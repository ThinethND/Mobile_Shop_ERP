<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref } from 'vue'
import * as THREE from 'three'
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js'
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js'

const DOTLOTTIE_SCRIPT_SRC =
  'https://unpkg.com/@lottiefiles/dotlottie-wc@0.9.3/dist/dotlottie-wc.js'

const DOTLOTTIE_ANIMATION_SRC =
  'https://lottie.host/7799d1df-13f4-4ae5-9498-c5bbc8362c42/ROZeoYHWYC.lottie'

const props = withDefaults(
  defineProps<{
    modelPath?: string
    height?: string
    autoRotate?: boolean
  }>(),
  {
    modelPath: '/models/iphone_17_pro_max.glb',
    height: '700px',
    autoRotate: true,
  },
)

const sectionRef = ref<HTMLElement | null>(null)
const stageRef = ref<HTMLElement | null>(null)
const loading = ref(true)
const error = ref('')
const modelReady = ref(false)
const showRotateHint = ref(false)

let scene: THREE.Scene | null = null
let camera: THREE.PerspectiveCamera | null = null
let renderer: THREE.WebGLRenderer | null = null
let controls: OrbitControls | null = null
let modelRoot: THREE.Group | null = null
let frameId = 0
let resizeObserver: ResizeObserver | null = null
let resumeRotateTimer: number | null = null
let meshTargets: THREE.Object3D[] = []
let currentAutoRotateSpeed = 0
let targetAutoRotateSpeed = 0
let dotLottieScriptEl: HTMLScriptElement | null = null
let hintObserver: IntersectionObserver | null = null
let sectionInView = false
let hasUserInteracted = false

const raycaster = new THREE.Raycaster()
const pointer = new THREE.Vector2()
const clock = new THREE.Clock()

const clearResumeTimer = () => {
  if (resumeRotateTimer !== null) {
    window.clearTimeout(resumeRotateTimer)
    resumeRotateTimer = null
  }
}

const hideRotateHint = () => {
  showRotateHint.value = false
}

const maybeShowRotateHint = () => {
  if (!modelReady.value || loading.value || !!error.value || !sectionInView || hasUserInteracted) {
    return
  }

  showRotateHint.value = true
}

const registerUserInteraction = () => {
  if (hasUserInteracted) return
  hasUserInteracted = true
  hideRotateHint()
}

const ensureDotLottieScript = () => {
  if (typeof window === 'undefined') return
  if (document.querySelector('script[data-dotlottie-wc="true"]')) return

  dotLottieScriptEl = document.createElement('script')
  dotLottieScriptEl.src = DOTLOTTIE_SCRIPT_SRC
  dotLottieScriptEl.type = 'module'
  dotLottieScriptEl.async = true
  dotLottieScriptEl.dataset.dotlottieWc = 'true'
  document.head.appendChild(dotLottieScriptEl)
}

const setCanvasIdleState = () => {
  if (!renderer?.domElement) return
  renderer.domElement.style.cursor = 'grab'
  renderer.domElement.style.touchAction = 'pan-y'
}

const setCanvasDragState = () => {
  if (!renderer?.domElement) return
  renderer.domElement.style.cursor = 'grabbing'
  renderer.domElement.style.touchAction = 'none'
}

const pauseAutoRotate = () => {
  clearResumeTimer()
  targetAutoRotateSpeed = 0
  setCanvasDragState()
}

const resumeAutoRotate = () => {
  clearResumeTimer()
  setCanvasIdleState()

  if (!props.autoRotate) {
    targetAutoRotateSpeed = 0
    return
  }

  resumeRotateTimer = window.setTimeout(() => {
    targetAutoRotateSpeed = window.innerWidth < 768 ? 0.55 : 0.78
    setCanvasIdleState()
  }, 500)
}

const getIntersections = (event: PointerEvent) => {
  if (!renderer || !camera || !meshTargets.length) {
    return []
  }

  const rect = renderer.domElement.getBoundingClientRect()

  pointer.x = ((event.clientX - rect.left) / rect.width) * 2 - 1
  pointer.y = -((event.clientY - rect.top) / rect.height) * 2 + 1

  raycaster.setFromCamera(pointer, camera)

  return raycaster.intersectObjects(meshTargets, true)
}

const handlePointerDown = (event: PointerEvent) => {
  if (!controls || loading.value || error.value) return

  const hitModel = getIntersections(event).length > 0

  if (hitModel) {
    registerUserInteraction()
    controls.enabled = true
    pauseAutoRotate()
  } else {
    controls.enabled = false
    setCanvasIdleState()
  }
}

const handlePointerEnd = () => {
  if (!controls) return
  controls.enabled = true
  resumeAutoRotate()
}

const fitCameraToObject = (object: THREE.Object3D) => {
  if (!camera || !controls) return

  const box = new THREE.Box3().setFromObject(object)
  const size = box.getSize(new THREE.Vector3())
  const center = box.getCenter(new THREE.Vector3())

  object.position.sub(center)

  const maxDim = Math.max(size.x, size.y, size.z)
  const fov = (camera.fov * Math.PI) / 180
  const mobileView = window.innerWidth < 768
  const distanceFactor = mobileView ? 1.2 : 1.48
  const cameraZ = Math.abs(maxDim / 2 / Math.tan(fov / 2)) * distanceFactor

  camera.position.set(cameraZ * 0.12, cameraZ * 0.015, cameraZ)
  camera.near = Math.max(0.01, maxDim / 100)
  camera.far = Math.max(1000, maxDim * 25)
  camera.updateProjectionMatrix()

  controls.target.set(0, 0, 0)
  controls.minDistance = cameraZ
  controls.maxDistance = cameraZ
  controls.update()
}

const createLights = () => {
  if (!scene) return

  const ambient = new THREE.AmbientLight(0xffffff, 1.6)
  scene.add(ambient)

  const hemi = new THREE.HemisphereLight(0xffffff, 0x020202, 1.2)
  hemi.position.set(0, 20, 0)
  scene.add(hemi)

  const key = new THREE.DirectionalLight(0xffffff, 2.5)
  key.position.set(6, 8, 10)
  scene.add(key)

  const fill = new THREE.DirectionalLight(0xbfd7ff, 1.05)
  fill.position.set(-7, 3, 6)
  scene.add(fill)

  const rim = new THREE.DirectionalLight(0xffffff, 1.45)
  rim.position.set(0, 7, -10)
  scene.add(rim)

  const low = new THREE.DirectionalLight(0xffffff, 0.55)
  low.position.set(0, -3, 5)
  scene.add(low)
}

const resize = () => {
  if (!stageRef.value || !camera || !renderer) return

  const width = stageRef.value.clientWidth
  const height = stageRef.value.clientHeight

  if (!width || !height) return

  camera.aspect = width / height
  camera.updateProjectionMatrix()
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.75))
  renderer.setSize(width, height, false)
}

const animate = () => {
  if (!renderer || !scene || !camera) return

  frameId = window.requestAnimationFrame(animate)

  const delta = Math.min(clock.getDelta(), 0.033)

  if (controls) {
    currentAutoRotateSpeed +=
      (targetAutoRotateSpeed - currentAutoRotateSpeed) * Math.min(1, delta * 7)
    controls.autoRotate = currentAutoRotateSpeed > 0.001
    controls.autoRotateSpeed = currentAutoRotateSpeed
    controls.update(delta)
  }

  renderer.render(scene, camera)
}

const disposeScene = () => {
  clearResumeTimer()
  hideRotateHint()

  if (frameId) {
    window.cancelAnimationFrame(frameId)
    frameId = 0
  }

  if (renderer?.domElement) {
    renderer.domElement.removeEventListener('pointerdown', handlePointerDown)
  }

  window.removeEventListener('pointerup', handlePointerEnd)
  window.removeEventListener('pointercancel', handlePointerEnd)
  window.removeEventListener('pointerleave', handlePointerEnd)

  controls?.dispose()
  controls = null

  if (modelRoot) {
    modelRoot.traverse((child) => {
      const mesh = child as THREE.Mesh

      if (mesh.isMesh) {
        mesh.geometry?.dispose()

        const materials = Array.isArray(mesh.material) ? mesh.material : [mesh.material]
        materials.forEach((material) => material?.dispose?.())
      }
    })
  }

  if (scene && modelRoot) {
    scene.remove(modelRoot)
  }

  modelRoot = null
  meshTargets = []

  renderer?.dispose()
  renderer = null
  camera = null
  scene = null

  resizeObserver?.disconnect()
  resizeObserver = null

  hintObserver?.disconnect()
  hintObserver = null

  if (stageRef.value) {
    stageRef.value.innerHTML = ''
  }
}

onMounted(() => {
  if (!stageRef.value) return

  ensureDotLottieScript()

  scene = new THREE.Scene()

  camera = new THREE.PerspectiveCamera(34, 1, 0.1, 1000)
  camera.position.set(0, 0.1, 4)

  renderer = new THREE.WebGLRenderer({
    antialias: true,
    alpha: false,
    powerPreference: 'high-performance',
  })

  renderer.outputColorSpace = THREE.SRGBColorSpace
  renderer.toneMapping = THREE.ACESFilmicToneMapping
  renderer.toneMappingExposure = 1.08
  renderer.setClearColor(0x000000, 1)
  renderer.domElement.style.display = 'block'
  renderer.domElement.style.width = '100%'
  renderer.domElement.style.height = '100%'
  renderer.domElement.setAttribute('aria-label', 'Interactive 3D phone model')

  stageRef.value.appendChild(renderer.domElement)

  controls = new OrbitControls(camera, renderer.domElement)
  controls.enableDamping = true
  controls.dampingFactor = window.innerWidth < 768 ? 0.11 : 0.085
  controls.rotateSpeed = window.innerWidth < 768 ? 0.58 : 0.72
  controls.enableZoom = false
  controls.enablePan = false
  controls.enableRotate = true
  controls.autoRotate = props.autoRotate
  controls.autoRotateSpeed = 0
  controls.minPolarAngle = Math.PI / 2 - 0.62
  controls.maxPolarAngle = Math.PI / 2 + 0.62
  controls.minAzimuthAngle = -Infinity
  controls.maxAzimuthAngle = Infinity
  controls.touches.ONE = THREE.TOUCH.ROTATE
  controls.touches.TWO = THREE.TOUCH.DOLLY_PAN

  currentAutoRotateSpeed = 0
  targetAutoRotateSpeed = props.autoRotate ? (window.innerWidth < 768 ? 0.55 : 0.78) : 0

  setCanvasIdleState()
  createLights()
  resize()

  renderer.domElement.addEventListener('pointerdown', handlePointerDown)
  window.addEventListener('pointerup', handlePointerEnd)
  window.addEventListener('pointercancel', handlePointerEnd)
  window.addEventListener('pointerleave', handlePointerEnd)

  resizeObserver = new ResizeObserver(() => {
    resize()
    if (modelRoot) {
      fitCameraToObject(modelRoot)
    }
  })
  resizeObserver.observe(stageRef.value)

  if (sectionRef.value) {
    hintObserver = new IntersectionObserver(
      (entries) => {
        const entry = entries[0]
        sectionInView = entry.isIntersecting && entry.intersectionRatio > 0.35

        if (!sectionInView) {
          hideRotateHint()
          return
        }

        maybeShowRotateHint()
      },
      {
        threshold: [0.2, 0.35, 0.6],
      },
    )

    hintObserver.observe(sectionRef.value)
  }

  const loader = new GLTFLoader()

  loader.load(
    props.modelPath,
    (gltf) => {
      if (!scene || !renderer || !camera) return

      modelRoot = gltf.scene
      modelRoot.rotation.y = -0.46
      modelRoot.rotation.x = 0.035

      meshTargets = []

      modelRoot.traverse((child) => {
        const mesh = child as THREE.Mesh

        if (mesh.isMesh) {
          mesh.castShadow = false
          mesh.receiveShadow = false
          mesh.frustumCulled = false
          meshTargets.push(mesh)
        }
      })

      scene.add(modelRoot)
      fitCameraToObject(modelRoot)
      renderer.compile(scene, camera)

      modelReady.value = true
      loading.value = false
      maybeShowRotateHint()
      clock.start()
      animate()
    },
    undefined,
    (loadError) => {
      console.error(loadError)
      error.value = 'Failed to load 3D model.'
      loading.value = false
      modelReady.value = false
      hideRotateHint()
    },
  )
})

onBeforeUnmount(() => {
  disposeScene()
})
</script>

<template>
  <section
    ref="sectionRef"
    class="phone-showcase relative w-full overflow-hidden bg-black text-white"
    :style="{ '--phone-stage-height': height }"
  >
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_left_top,rgba(255,255,255,0.06),transparent_28%)]" />
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_right_center,rgba(255,255,255,0.05),transparent_26%)]" />
      <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(255,255,255,0.02),transparent_18%,transparent_82%,rgba(255,255,255,0.02))]" />
    </div>

    <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-1">
      <div class="grid items-center gap-10 lg:grid-cols-[minmax(0,0.88fr)_minmax(0,1.12fr)] xl:gap-16">
        <div class="order-2 lg:order-1">
          <div
            class="inline-flex items-center rounded-full border border-white/10 bg-white/[0.05] px-4 py-2 text-[11px] font-semibold uppercase tracking-[0.28em] text-white/75 sm:text-xs"
          >
            Pre-Order Available
          </div>

          <h2
            class="mt-5 max-w-[11ch] text-[42px] font-semibold leading-[0.9] tracking-[-0.05em] text-white sm:text-[58px] lg:text-[68px] xl:text-[84px]"
          >
            Introducing iPhone 17 Pro Max
          </h2>

          <p class="mt-5 max-w-[60ch] text-[15px] leading-7 text-white/72 sm:text-base sm:leading-8">
            Discover a bold new flagship experience with the iPhone 17 Pro Max. Designed to look
            premium from every angle, it brings a refined silhouette, a striking pro finish, and a
            powerful first impression that belongs at the center of your next upgrade.
          </p>
        </div>

        <div class="order-1 lg:order-2">
          <div class="phone-showcase__stage-wrap relative ml-auto w-full">
            <div ref="stageRef" class="phone-showcase__stage" />

            <div
              v-if="loading"
              class="phone-showcase__loading pointer-events-none absolute inset-0 z-20"
              role="status"
              aria-live="polite"
            >
              <div class="phone-showcase__loading-card">
                <component
                  :is="'dotlottie-wc'"
                  :src="DOTLOTTIE_ANIMATION_SRC"
                  class="phone-showcase__loader-anim"
                  autoplay
                  loop
                />
                <p class="phone-showcase__loading-text">Loading 3D Model...</p>
              </div>
            </div>

            <div
              v-if="showRotateHint && !loading && !error"
              class="phone-showcase__rotate-hint"
              aria-hidden="true"
            >
              <div class="phone-showcase__rotate-hint-card">
                <div class="phone-showcase__rotate-track">
                  <span class="phone-showcase__rotate-trail" />
                  <span class="phone-showcase__rotate-glow" />

                  <span class="phone-showcase__rotate-hand">
                    <svg
                      viewBox="0 0 64 64"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                      class="phone-showcase__rotate-hand-icon"
                    >
                      <path
                        d="M24 29V14.5C24 12.567 25.567 11 27.5 11C29.433 11 31 12.567 31 14.5V26"
                        stroke="currentColor"
                        stroke-width="3.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <path
                        d="M31 26V12.5C31 10.567 32.567 9 34.5 9C36.433 9 38 10.567 38 12.5V26"
                        stroke="currentColor"
                        stroke-width="3.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <path
                        d="M38 26V15.5C38 13.567 39.567 12 41.5 12C43.433 12 45 13.567 45 15.5V29"
                        stroke="currentColor"
                        stroke-width="3.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <path
                        d="M24 24.5L20.4 22.3C18.7 21.26 16.49 21.78 15.44 23.47C14.42 25.13 14.89 27.3 16.5 28.4L24 33.5V43.8C24 50.54 29.46 56 36.2 56C41.76 56 46.64 52.23 48.05 46.85L50 39.4C50.64 36.96 50.03 34.35 48.36 32.45L45 28.6"
                        stroke="currentColor"
                        stroke-width="3.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                  </span>
                </div>

                <div class="phone-showcase__rotate-copy">
                  <p class="phone-showcase__rotate-title">Drag to rotate</p>
                  <p class="phone-showcase__rotate-subtitle">Touch and move the phone in 3D</p>
                </div>
              </div>
            </div>

            <div
              v-if="error"
              class="absolute inset-0 z-30 flex items-center justify-center px-6 text-center"
            >
              <div
                class="rounded-2xl border border-red-300/25 bg-red-500/15 px-5 py-4 text-sm text-white"
              >
                {{ error }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.phone-showcase__stage-wrap {
  width: 100%;
  max-width: 100%;
}

.phone-showcase__stage {
  position: relative;
  width: 100%;
  height: clamp(400px, 96vw, 640px);
  overflow: hidden;
  background:
    radial-gradient(circle at 50% 40%, rgba(255, 255, 255, 0.05), transparent 24%),
    radial-gradient(circle at 50% 84%, rgba(255, 255, 255, 0.03), transparent 26%),
    #000;
  border-radius: 28px;
}

.phone-showcase__stage :deep(canvas) {
  display: block;
  width: 100% !important;
  height: 100% !important;
  outline: none;
  touch-action: pan-y;
}

.phone-showcase__loading {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  backdrop-filter: blur(2px);
  background: rgba(0, 0, 0, 0.18);
}

.phone-showcase__loading-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  width: min(88%, 340px);
}

.phone-showcase__loader-anim {
  width: clamp(118px, 30vw, 300px);
  height: clamp(118px, 30vw, 300px);
}

.phone-showcase__loading-text {
  margin: 0;
  text-align: center;
  font-size: clamp(13px, 2.8vw, 16px);
  font-weight: 600;
  letter-spacing: 0.02em;
  color: rgba(255, 255, 255, 0.92);
}

.phone-showcase__rotate-hint {
  position: absolute;
  inset: 0;
  z-index: 25;
  pointer-events: none;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding: clamp(16px, 3.5vw, 28px);
  animation: phone-showcase-hint-fade 0.38s ease;
}

.phone-showcase__rotate-hint-card {
  display: inline-flex;
  align-items: center;
  gap: 0.95rem;
  min-width: min(92%, 360px);
  max-width: 92%;
  padding: 0.9rem 1rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 22px;
  background: rgba(10, 10, 10, 0.58);
  backdrop-filter: blur(14px);
  box-shadow:
    0 16px 40px rgba(0, 0, 0, 0.34),
    inset 0 1px 0 rgba(255, 255, 255, 0.06);
}

.phone-showcase__rotate-track {
  position: relative;
  flex: 0 0 clamp(108px, 18vw, 150px);
  height: 46px;
  overflow: hidden;
}

.phone-showcase__rotate-trail {
  position: absolute;
  left: 50%;
  top: 50%;
  width: clamp(78px, 13vw, 108px);
  height: 2px;
  border-radius: 999px;
  transform: translate(-50%, -50%);
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0.05),
    rgba(255, 255, 255, 0.55),
    rgba(255, 255, 255, 0.05)
  );
  animation: phone-showcase-trail-pulse 1.6s ease-in-out infinite;
}

.phone-showcase__rotate-glow {
  position: absolute;
  left: 50%;
  top: 50%;
  width: 84px;
  height: 84px;
  transform: translate(-50%, -50%);
  border-radius: 999px;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.14), transparent 64%);
  filter: blur(3px);
  opacity: 0.75;
}

.phone-showcase__rotate-hand {
  position: absolute;
  left: 50%;
  top: 50%;
  width: 46px;
  height: 46px;
  display: grid;
  place-items: center;
  border-radius: 999px;
  color: rgba(255, 255, 255, 0.96);
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.16);
  box-shadow:
    0 8px 24px rgba(0, 0, 0, 0.24),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  transform: translate(-50%, -50%);
  animation: phone-showcase-hand-swipe 1.6s ease-in-out infinite;
}

.phone-showcase__rotate-hand-icon {
  width: 26px;
  height: 26px;
}

.phone-showcase__rotate-copy {
  min-width: 0;
}

.phone-showcase__rotate-title {
  margin: 0;
  font-size: 14px;
  line-height: 1.2;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.98);
}

.phone-showcase__rotate-subtitle {
  margin: 0.24rem 0 0;
  font-size: 12px;
  line-height: 1.45;
  color: rgba(255, 255, 255, 0.66);
}

@keyframes phone-showcase-hand-swipe {
  0%,
  100% {
    transform: translate(calc(-50% - 24px), -50%) rotate(-12deg);
  }
  50% {
    transform: translate(calc(-50% + 24px), -50%) rotate(12deg);
  }
}

@keyframes phone-showcase-trail-pulse {
  0%,
  100% {
    opacity: 0.35;
    transform: translate(-50%, -50%) scaleX(0.72);
  }
  50% {
    opacity: 0.95;
    transform: translate(-50%, -50%) scaleX(1);
  }
}

@keyframes phone-showcase-hint-fade {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 640px) {
  .phone-showcase__stage {
    height: clamp(360px, 92vw, 500px);
    border-radius: 22px;
  }

  .phone-showcase__loading-card {
    gap: 0.25rem;
  }

  .phone-showcase__loader-anim {
    width: clamp(100px, 34vw, 150px);
    height: clamp(100px, 34vw, 150px);
  }

  .phone-showcase__rotate-hint {
    padding: 14px;
  }

  .phone-showcase__rotate-hint-card {
    gap: 0.75rem;
    padding: 0.8rem 0.85rem;
    border-radius: 18px;
  }

  .phone-showcase__rotate-track {
    flex-basis: 102px;
    height: 42px;
  }

  .phone-showcase__rotate-hand {
    width: 42px;
    height: 42px;
  }

  .phone-showcase__rotate-hand-icon {
    width: 24px;
    height: 24px;
  }

  .phone-showcase__rotate-title {
    font-size: 13px;
  }

  .phone-showcase__rotate-subtitle {
    font-size: 11px;
  }
}

@media (min-width: 1024px) {
  .phone-showcase__stage-wrap {
    max-width: 760px;
  }

  .phone-showcase__stage {
    height: var(--phone-stage-height);
  }
}
</style>
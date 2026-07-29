<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import StarRating from '@/Frontend/components/StarRating.vue'

const PAGE_SIZE = 4

type SortKey = 'recent' | 'highest' | 'lowest'

type ReviewItem = {
  id: number | string
  rating: number | null
  customer_name: string | null
  short_description: string | null
  long_description: string | null
  image_urls: string[]
  created_at: string | null
  created_at_iso: string | null
}

type Summary = {
  reviews_count: number
  avg_rating: number | null
}

type Cached = {
  summary: Summary
  reviews: ReviewItem[]
}

type PageData = {
  summary: Summary
  reviews: ReviewItem[]
}

type GalleryImageItem = {
  key: string
  src: string
  reviewId: string
  customerName: string | null
}

const cache = new Map<string, Cached>()
const inflight = new Map<string, Promise<PageData>>()

const props = withDefaults(
  defineProps<{
    fetchUrl: string
    active: boolean
    initialCount?: number
    initialAvg?: number | null
    productName?: string | null
    productImage?: string | null
  }>(),
  {
    initialCount: 0,
    initialAvg: null,
    productName: null,
    productImage: null,
  }
)

const loading = ref(false)
const loadingMore = ref(false)
const loaded = ref(false)
const hydratingGallery = ref(false)
const error = ref<string | null>(null)
const errorScope = ref<'initial' | 'more' | null>(null)

const sort = ref<SortKey>('recent')

const summary = ref<Summary>({
  reviews_count: props.initialCount ?? 0,
  avg_rating: props.initialAvg ?? null,
})

const reviews = ref<ReviewItem[]>([])

const imageModalOpen = ref(false)
const activeGalleryIndex = ref(0)

const addReviewOpen = ref(false)
const addReviewStep = ref<1 | 2>(1)
const draftRating = ref(0)
const hoveredDraftRating = ref(0)
const invoiceDigits = ref('')
const customerName = ref('')
const postAnonymously = ref(false)
const reviewContent = ref('')
const reviewImageFiles = ref<File[]>([])
const reviewImagePreviewUrls = ref<string[]>([])
const reviewImageInputKey = ref(0)
const submitError = ref<string | null>(null)
const submitSuccess = ref<string | null>(null)
const submitting = ref(false)

let stepTimer: number | null = null
let successTimer: number | null = null
const MAX_REVIEW_IMAGES = 8
const MAX_REVIEW_THUMBNAILS = 1
const MAX_REVIEW_IMAGE_MB = 8
const MAX_REVIEW_IMAGE_BYTES = MAX_REVIEW_IMAGE_MB * 1024 * 1024

const cacheKey = computed(() => {
  if (!props.fetchUrl) return ''
  return `${props.fetchUrl}::${sort.value}`
})

const sortedReviews = computed(() => {
  const list = [...reviews.value]

  if (sort.value === 'highest') {
    return list.sort((a, b) => (b.rating ?? -1) - (a.rating ?? -1))
  }

  if (sort.value === 'lowest') {
    return list.sort((a, b) => (a.rating ?? 999) - (b.rating ?? 999))
  }

  return list.sort((a, b) => {
    const aIso = a.created_at_iso || ''
    const bIso = b.created_at_iso || ''
    return bIso.localeCompare(aIso)
  })
})

const canLoadMore = computed(() => {
  return summary.value.reviews_count > reviews.value.length
})

const remainingReviews = computed(() => {
  return Math.max(0, summary.value.reviews_count - reviews.value.length)
})

const nextLoadCount = computed(() => {
  return Math.min(PAGE_SIZE, remainingReviews.value)
})

const galleryImages = computed<GalleryImageItem[]>(() => {
  return sortedReviews.value.flatMap((review) => {
    return review.image_urls.map((src, index) => ({
      key: `${String(review.id)}::${index}`,
      src,
      reviewId: String(review.id),
      customerName: review.customer_name,
    }))
  })
})

const activeGalleryItem = computed(() => {
  return galleryImages.value[activeGalleryIndex.value] ?? null
})

const hasAnyModalOpen = computed(() => {
  return imageModalOpen.value || addReviewOpen.value
})

const interactiveDraftRating = computed(() => {
  return hoveredDraftRating.value || draftRating.value
})

function normalizeSummary(raw: any): Summary {
  return {
    reviews_count: Number(raw?.reviews_count ?? 0),
    avg_rating: raw?.avg_rating === null || typeof raw?.avg_rating === 'undefined'
      ? null
      : Number(raw?.avg_rating),
  }
}

function normalizeReview(raw: any): ReviewItem {
  return {
    id: raw?.id ?? '',
    rating: raw?.rating === null || typeof raw?.rating === 'undefined' ? null : Number(raw?.rating),
    customer_name: raw?.customer_name ?? null,
    short_description: raw?.short_description ?? null,
    long_description: raw?.long_description ?? null,
    image_urls: Array.isArray(raw?.image_urls) ? raw.image_urls.map(String).filter(Boolean) : [],
    created_at: raw?.created_at ?? null,
    created_at_iso: raw?.created_at_iso ?? null,
  }
}

function buildPagedUrl(fetchUrl: string, sortKey: SortKey, offset: number) {
  const [path, queryString] = fetchUrl.split('?')
  const params = new URLSearchParams(queryString || '')

  params.set('sort', sortKey)
  params.set('limit', String(PAGE_SIZE))
  params.set('offset', String(offset))

  const query = params.toString()
  return query ? `${path}?${query}` : path
}

function applyCached(next: Cached) {
  summary.value = next.summary
  reviews.value = next.reviews
  loaded.value = true
}

function syncFromCache() {
  const key = cacheKey.value
  if (!key) return
  const cached = cache.get(key)
  if (!cached) return
  if (loaded.value && cached.reviews.length === reviews.value.length) return
  applyCached(cached)
}

function mergeReviews(existing: ReviewItem[], incoming: ReviewItem[]) {
  const seen = new Set(existing.map((item) => String(item.id)))
  const merged = [...existing]

  for (const item of incoming) {
    const id = String(item.id)
    if (seen.has(id)) continue
    seen.add(id)
    merged.push(item)
  }

  return merged
}

async function fetchPage(fetchUrl: string, sortKey: SortKey, offset: number) {
  const key = `${fetchUrl}::${sortKey}`
  const requestKey = `${key}::${offset}`

  if (inflight.has(requestKey)) {
    return await inflight.get(requestKey)!
  }

  const url = buildPagedUrl(fetchUrl, sortKey, offset)

  const promise = fetch(url, {
    method: 'GET',
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  }).then(async (res) => {
    if (!res.ok) throw new Error('Failed')
    const data = await res.json()
    return {
      summary: normalizeSummary(data?.summary),
      reviews: Array.isArray(data?.reviews) ? data.reviews.map(normalizeReview) : [],
    } satisfies PageData
  })

  inflight.set(requestKey, promise)

  try {
    return await promise
  } finally {
    inflight.delete(requestKey)
  }
}

async function loadInitial() {
  if (!props.fetchUrl) return
  if (loaded.value || loading.value) return

  const fetchUrl = props.fetchUrl
  const sortKey = sort.value
  const key = `${fetchUrl}::${sortKey}`

  if (cache.has(key)) {
    applyCached(cache.get(key)!)
    return
  }

  loading.value = true
  error.value = null
  errorScope.value = null

  try {
    const page = await fetchPage(fetchUrl, sortKey, 0)
    const next: Cached = { summary: page.summary, reviews: page.reviews }
    cache.set(key, next)

    if (cacheKey.value !== key) return
    applyCached(next)
  } catch (e) {
    console.error('Reviews load error:', e)
    error.value = 'Failed to load reviews. Please try again.'
    errorScope.value = 'initial'
  } finally {
    loading.value = false
  }
}

async function loadMore() {
  if (!props.fetchUrl) return
  if (!loaded.value) return
  if (!canLoadMore.value) return
  if (loading.value || loadingMore.value) return

  syncFromCache()
  if (!canLoadMore.value) return

  const fetchUrl = props.fetchUrl
  const sortKey = sort.value
  const key = `${fetchUrl}::${sortKey}`
  const offset = reviews.value.length

  loadingMore.value = true
  error.value = null
  errorScope.value = null

  try {
    const page = await fetchPage(fetchUrl, sortKey, offset)
    const merged = mergeReviews(reviews.value, page.reviews)
    const next: Cached = {
      summary: page.summary,
      reviews: merged,
    }

    cache.set(key, next)

    if (cacheKey.value !== key) return
    applyCached(next)
  } catch (e) {
    console.error('Reviews load more error:', e)
    error.value = 'Failed to load more reviews. Please try again.'
    errorScope.value = 'more'
  } finally {
    loadingMore.value = false
  }
}

async function hydrateAllReviewsForGallery() {
  if (!props.fetchUrl) return
  if (hydratingGallery.value) return

  if (!loaded.value) {
    await loadInitial()
  }

  if (!canLoadMore.value) return

  hydratingGallery.value = true

  try {
    const fetchUrl = props.fetchUrl
    const sortKey = sort.value
    const key = `${fetchUrl}::${sortKey}`

    let nextSummary = summary.value
    let nextReviews = [...reviews.value]
    let offset = nextReviews.length

    while (offset < nextSummary.reviews_count) {
      const page = await fetchPage(fetchUrl, sortKey, offset)
      nextSummary = page.summary
      nextReviews = mergeReviews(nextReviews, page.reviews)
      offset = nextReviews.length

      const next: Cached = {
        summary: nextSummary,
        reviews: nextReviews,
      }

      cache.set(key, next)

      if (cacheKey.value === key) {
        applyCached(next)
      }

      if (!page.reviews.length) {
        break
      }
    }
  } catch (e) {
    console.error('Gallery hydration error:', e)
  } finally {
    hydratingGallery.value = false
  }
}

function clearCacheForFetchUrl(fetchUrl: string) {
  for (const key of cache.keys()) {
    if (key.startsWith(`${fetchUrl}::`)) {
      cache.delete(key)
    }
  }

  for (const key of inflight.keys()) {
    if (key.startsWith(`${fetchUrl}::`)) {
      inflight.delete(key)
    }
  }
}

function retryInitial() {
  if (!props.fetchUrl) return

  loaded.value = false
  error.value = null
  errorScope.value = null
  reviews.value = []

  clearCacheForFetchUrl(props.fetchUrl)
  loadInitial()
}

function retryMore() {
  error.value = null
  errorScope.value = null
  loadMore()
}

function openImageModal(reviewId: number | string, imageIndex: number) {
  const key = `${String(reviewId)}::${imageIndex}`
  const index = galleryImages.value.findIndex((item) => item.key === key)
  if (index < 0) return

  activeGalleryIndex.value = index
  imageModalOpen.value = true
  void hydrateAllReviewsForGallery()
}

function closeImageModal() {
  imageModalOpen.value = false
}

function nextGalleryImage() {
  if (!galleryImages.value.length) return
  activeGalleryIndex.value = (activeGalleryIndex.value + 1) % galleryImages.value.length
}

function prevGalleryImage() {
  if (!galleryImages.value.length) return
  activeGalleryIndex.value = (activeGalleryIndex.value - 1 + galleryImages.value.length) % galleryImages.value.length
}

let galleryTouchStartX: number | null = null
let galleryTouchStartY: number | null = null
let galleryTouchLastX: number | null = null
let galleryTouchLastY: number | null = null

function onGalleryTouchStart(event: TouchEvent) {
  if (!galleryImages.value.length) return
  if (event.touches.length !== 1) return

  const touch = event.touches[0]
  galleryTouchStartX = touch.clientX
  galleryTouchStartY = touch.clientY
  galleryTouchLastX = touch.clientX
  galleryTouchLastY = touch.clientY
}

function onGalleryTouchMove(event: TouchEvent) {
  if (galleryTouchStartX === null || galleryTouchStartY === null) return
  if (event.touches.length !== 1) return

  const touch = event.touches[0]
  galleryTouchLastX = touch.clientX
  galleryTouchLastY = touch.clientY
}

function resetGalleryTouch() {
  galleryTouchStartX = null
  galleryTouchStartY = null
  galleryTouchLastX = null
  galleryTouchLastY = null
}

function onGalleryTouchEnd() {
  if (
    galleryTouchStartX === null
    || galleryTouchStartY === null
    || galleryTouchLastX === null
    || galleryTouchLastY === null
  ) {
    resetGalleryTouch()
    return
  }

  const deltaX = galleryTouchLastX - galleryTouchStartX
  const deltaY = galleryTouchLastY - galleryTouchStartY
  const absX = Math.abs(deltaX)
  const absY = Math.abs(deltaY)

  resetGalleryTouch()

  if (absX < 44 || absX < absY) return

  if (deltaX > 0) {
    nextGalleryImage()
    return
  }

  prevGalleryImage()
}

function openAddReview() {
  resetAddReviewDraft()
  addReviewOpen.value = true
}

function closeAddReview() {
  addReviewOpen.value = false
  resetAddReviewDraft()
}

function resetAddReviewDraft() {
  addReviewStep.value = 1
  draftRating.value = 0
  hoveredDraftRating.value = 0
  invoiceDigits.value = ''
  customerName.value = ''
  postAnonymously.value = false
  reviewContent.value = ''
  submitError.value = null
  submitting.value = false
  cleanupReviewPreviewUrls()
  reviewImageFiles.value = []
  reviewImagePreviewUrls.value = []
  reviewImageInputKey.value += 1

  if (stepTimer !== null) {
    window.clearTimeout(stepTimer)
    stepTimer = null
  }
}

function clearSuccessMessage() {
  submitSuccess.value = null

  if (successTimer !== null) {
    window.clearTimeout(successTimer)
    successTimer = null
  }
}

function showSuccessMessage(message: string) {
  clearSuccessMessage()
  submitSuccess.value = message
  successTimer = window.setTimeout(() => {
    submitSuccess.value = null
    successTimer = null
  }, 4500)
}

function selectDraftRating(rating: number) {
  draftRating.value = rating
  submitError.value = null
  clearSuccessMessage()

  if (stepTimer !== null) {
    window.clearTimeout(stepTimer)
  }

  stepTimer = window.setTimeout(() => {
    addReviewStep.value = 2
  }, 180)
}

function backToRatingStep() {
  submitError.value = null
  addReviewStep.value = 1
}

function cleanupReviewPreviewUrls() {
  reviewImagePreviewUrls.value.forEach((url) => {
    URL.revokeObjectURL(url)
  })
}

function removeReviewImage(index: number) {
  const url = reviewImagePreviewUrls.value[index]
  if (url) {
    URL.revokeObjectURL(url)
  }

  reviewImagePreviewUrls.value.splice(index, 1)
  reviewImageFiles.value.splice(index, 1)
}

function sanitizeInvoiceDigits(value: string) {
  return String(value ?? '')
    .replace(/\D+/g, '')
    .slice(0, 12)
}

function buildInvoiceNo(digits: string) {
  const sanitized = sanitizeInvoiceDigits(digits)
  if (!sanitized) return null

  const parsed = Number.parseInt(sanitized, 10)
  if (!Number.isFinite(parsed) || parsed <= 0) return null

  const normalized = String(parsed).padStart(3, '0')
  return `INV-${normalized}`
}

function getCsrfToken() {
  const token = document?.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  return token || ''
}

function onReviewImagesChange(event: Event) {
  const input = event.target as HTMLInputElement
  const selected = Array.from(input.files ?? [])
  if (!selected.length) return

  submitError.value = null
  clearSuccessMessage()

  const remaining = Math.max(0, MAX_REVIEW_IMAGES - reviewImageFiles.value.length)

  if (!remaining) {
    submitError.value = `You can upload up to ${MAX_REVIEW_IMAGES} images.`
    input.value = ''
    return
  }

  const validFiles = selected.filter((file) => file.size <= MAX_REVIEW_IMAGE_BYTES)
  const rejectedCount = selected.length - validFiles.length

  const nextFiles = validFiles.slice(0, remaining)

  if (rejectedCount) {
    submitError.value = `Some images were too large. Max ${MAX_REVIEW_IMAGE_MB}MB per image.`
  }

  if (!nextFiles.length) {
    input.value = ''
    return
  }

  reviewImageFiles.value = [...reviewImageFiles.value, ...nextFiles]
  reviewImagePreviewUrls.value = [
    ...reviewImagePreviewUrls.value,
    ...nextFiles.map((file) => URL.createObjectURL(file)),
  ]

  input.value = ''
}

watch(postAnonymously, (checked) => {
  if (checked) {
    customerName.value = ''
  }
})

async function submitAddReview() {
  submitError.value = null
  clearSuccessMessage()
  const invoiceNo = buildInvoiceNo(invoiceDigits.value)

  if (!invoiceNo) {
    submitError.value = 'Please enter your invoice ID number.'
    return
  }

  if (!draftRating.value) {
    submitError.value = 'Please select a rating first.'
    return
  }

  submitting.value = true

  try {
    const formData = new FormData()
    formData.append('invoice_no', invoiceNo)
    formData.append('anonymous', postAnonymously.value ? '1' : '0')
    formData.append('rating', String(draftRating.value))

    if (!postAnonymously.value && customerName.value.trim()) {
      formData.append('customer_name', customerName.value.trim())
    }

    if (reviewContent.value.trim()) {
      formData.append('long_description', reviewContent.value.trim())
    }

    reviewImageFiles.value.forEach((file) => {
      formData.append('images[]', file)
    })

    const res = await fetch(props.fetchUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
      body: formData,
    })

    const contentType = res.headers.get('content-type') || ''
    const payload = contentType.includes('application/json') ? await res.json().catch(() => null) : null

    if (!res.ok) {
      if (res.status === 413) {
        submitError.value =
          'Your images are too large to upload. Please choose smaller images or upload fewer images.'
        return
      }

      const errors = payload?.errors && typeof payload.errors === 'object' ? payload.errors : null
      const firstError = errors
        ? Object.values(errors).flatMap((value: any) => (Array.isArray(value) ? value : [value]))[0]
        : null

      submitError.value = String(firstError || payload?.message || 'Failed to submit review. Please try again.')
      return
    }

    showSuccessMessage(String(payload?.message || 'Review submitted successfully.'))

    const fetchUrl = props.fetchUrl
    closeAddReview()

    loaded.value = false
    loading.value = false
    loadingMore.value = false
    hydratingGallery.value = false
    error.value = null
    errorScope.value = null
    reviews.value = []
    imageModalOpen.value = false
    activeGalleryIndex.value = 0
    clearCacheForFetchUrl(fetchUrl)

    await loadInitial()
  } catch (error) {
    console.error('Review submit error:', error)
    submitError.value =
      'Unable to submit your review right now. If you attached images, try fewer/smaller images and try again.'
  } finally {
    submitting.value = false
  }
}

function updateBodyScrollLock(locked: boolean) {
  if (typeof document === 'undefined') return

  document.documentElement.style.overflow = locked ? 'hidden' : ''
  document.body.style.overflow = locked ? 'hidden' : ''
}

function onGlobalKeydown(event: KeyboardEvent) {
  if (event.key === 'Escape') {
    if (imageModalOpen.value) {
      closeImageModal()
      return
    }

    if (addReviewOpen.value) {
      closeAddReview()
    }

    return
  }

  if (!imageModalOpen.value) return

  if (event.key === 'ArrowRight') {
    event.preventDefault()
    nextGalleryImage()
  }

  if (event.key === 'ArrowLeft') {
    event.preventDefault()
    prevGalleryImage()
  }
}

onMounted(() => {
  window.addEventListener('keydown', onGlobalKeydown)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onGlobalKeydown)
  cleanupReviewPreviewUrls()
  updateBodyScrollLock(false)
  clearSuccessMessage()

  if (stepTimer !== null) {
    window.clearTimeout(stepTimer)
    stepTimer = null
  }
})

watch(
  hasAnyModalOpen,
  (open) => {
    updateBodyScrollLock(open)
  },
  { immediate: true }
)

watch(
  galleryImages,
  (items) => {
    if (!items.length) {
      activeGalleryIndex.value = 0
      imageModalOpen.value = false
      return
    }

    if (activeGalleryIndex.value > items.length - 1) {
      activeGalleryIndex.value = items.length - 1
    }
  },
  { deep: true }
)

watch(
  () => props.active,
  (active) => {
    if (active) {
      loadInitial()
    }
  },
  { immediate: true }
)

watch(
  () => props.fetchUrl,
  () => {
    loaded.value = false
    loading.value = false
    loadingMore.value = false
    hydratingGallery.value = false
    error.value = null
    errorScope.value = null
    reviews.value = []
    summary.value = {
      reviews_count: props.initialCount ?? 0,
      avg_rating: props.initialAvg ?? null,
    }

    imageModalOpen.value = false
    activeGalleryIndex.value = 0
    closeAddReview()
    clearSuccessMessage()

    if (props.active) {
      loadInitial()
    }
  }
)

watch(
  () => sort.value,
  () => {
    loaded.value = false
    loading.value = false
    loadingMore.value = false
    hydratingGallery.value = false
    error.value = null
    errorScope.value = null
    reviews.value = []
    imageModalOpen.value = false
    activeGalleryIndex.value = 0

    if (props.active) {
      loadInitial()
    }
  }
)
</script>

<template>
  <div class="product-reviews-panel">
    <div
      class="product-reviews-header"
    >
      <div>
        <div class="flex items-center gap-2">
          <StarRating :rating="summary.avg_rating" :count="summary.reviews_count" :showCount="false" size="md" />
          <span class="text-sm text-slate-500">
            {{ summary.reviews_count }} review{{ summary.reviews_count === 1 ? '' : 's' }}
          </span>
        </div>
        <div class="mt-1 text-sm text-slate-500">
          Customer reviews
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button
          type="button"
          class="review-add-button"
          @click="openAddReview"
        >
          Add a review
        </button>
      </div>
    </div>

    <div class="product-reviews-body">
      <div
        v-if="submitSuccess"
        class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
      >
        {{ submitSuccess }}
      </div>

      <div v-if="loading && !reviews.length" class="space-y-4">
        <div v-for="n in 3" :key="`review-skel-${n}`" class="space-y-3 rounded-2xl border border-slate-200 p-4">
          <div class="h-4 w-32 animate-pulse rounded bg-slate-100" />
          <div class="h-4 w-2/3 animate-pulse rounded bg-slate-100" />
          <div class="h-4 w-full animate-pulse rounded bg-slate-100" />
        </div>
      </div>

      <div v-else-if="error && !reviews.length" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        <div class="font-semibold">Failed to load reviews</div>
        <div class="mt-1 text-red-600">{{ error }}</div>
        <button
          type="button"
          class="mt-3 inline-flex items-center rounded-full border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100"
          @click="retryInitial"
        >
          Retry
        </button>
      </div>

      <div v-else-if="!sortedReviews.length" class="review-empty-state">
        No reviews yet.
      </div>

      <div v-else>
        <div class="review-list">
          <article v-for="review in sortedReviews" :key="review.id" class="review-item">
            <div class="review-item__meta">
              <StarRating :rating="review.rating" :showValue="false" size="md" />
              <div class="text-xs text-slate-400">
                {{ review.created_at || '' }}
              </div>
            </div>

            <div class="mt-3 flex items-center gap-3">
              <div class="review-avatar">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor" aria-hidden="true">
                  <path
                    d="M12 12c2.761 0 5-2.239 5-5S14.761 2 12 2 7 4.239 7 7s2.239 5 5 5zm0 2c-4.418 0-8 2.239-8 5v1c0 .552.448 1 1 1h14c.552 0 1-.448 1-1v-1c0-2.761-3.582-5-8-5z"
                  />
                </svg>
              </div>

              <div class="flex flex-wrap items-center gap-2">
                <div class="font-semibold text-slate-900">
                  {{ review.customer_name || 'Anonymous' }}
                </div>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
                  Customer
                </span>
              </div>
            </div>

            <div v-if="review.short_description" class="review-title">
              {{ review.short_description }}
            </div>

            <div v-if="review.long_description" class="review-copy">
              {{ review.long_description }}
            </div>

            <div v-if="review.image_urls?.length" class="mt-4 flex flex-wrap gap-3">
              <button
                v-for="(url, idx) in review.image_urls.slice(0, MAX_REVIEW_THUMBNAILS)"
                :key="`${review.id}-img-${idx}`"
                type="button"
                class="review-image-button group"
                @click="openImageModal(review.id, idx)"
              >
                <img
                  :src="url"
                  alt="Review image"
                  class="h-24 w-24 object-cover transition duration-300 group-hover:scale-[1.04] sm:h-28 sm:w-28"
                  loading="lazy"
                />
                <div
                  v-if="idx === MAX_REVIEW_THUMBNAILS - 1 && review.image_urls.length > MAX_REVIEW_THUMBNAILS"
                  class="absolute inset-0 flex items-center justify-center bg-slate-950/60 text-sm font-semibold text-white"
                >
                  +{{ review.image_urls.length - MAX_REVIEW_THUMBNAILS }}
                </div>
              </button>
            </div>
          </article>
        </div>

        <div v-if="canLoadMore" class="pt-6">
          <button
            type="button"
            class="review-load-more"
            :disabled="loadingMore"
            @click="loadMore"
          >
            <span v-if="loadingMore">Loading...</span>
            <span v-else>Load {{ nextLoadCount }} more review{{ nextLoadCount === 1 ? '' : 's' }}</span>
          </button>
          <div class="mt-2 text-center text-xs text-slate-400">
            Showing {{ reviews.length }} of {{ summary.reviews_count }}
          </div>
        </div>

        <div
          v-if="error && reviews.length && errorScope === 'more'"
          class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
        >
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>{{ error }}</div>
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-full border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100"
              @click="retryMore"
            >
              Try again
            </button>
          </div>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <Transition name="modal-fade">
        <div
          v-if="imageModalOpen && activeGalleryItem"
          class="fixed inset-0 z-[140] bg-slate-950/20 backdrop-blur-md"
          @click="closeImageModal"
        >
          <button
            type="button"
            class="absolute right-4 top-4 z-20 inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/40 bg-white/70 text-slate-900 shadow-lg backdrop-blur transition hover:bg-white sm:right-6 sm:top-6"
            @click.stop="closeImageModal"
          >
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 6l12 12M18 6L6 18" />
            </svg>
          </button>

          <div
            class="pointer-events-none absolute left-1/2 top-4 z-20 -translate-x-1/2 rounded-full border border-white/30 bg-white/65 px-4 py-2 text-center text-xs font-medium text-slate-800 shadow-lg backdrop-blur sm:top-6"
          >
            {{ activeGalleryIndex + 1 }} / {{ galleryImages.length }}
            <span v-if="activeGalleryItem.customerName">- {{ activeGalleryItem.customerName }}</span>
          </div>

          <button
            v-if="galleryImages.length > 1"
            type="button"
            class="absolute left-3 top-1/2 z-20 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/35 bg-white/70 text-slate-900 shadow-lg backdrop-blur transition hover:bg-white sm:left-6 sm:h-12 sm:w-12"
            @click.stop="prevGalleryImage"
          >
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M15 18l-6-6 6-6" />
            </svg>
          </button>

          <button
            v-if="galleryImages.length > 1"
            type="button"
            class="absolute right-3 top-1/2 z-20 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/35 bg-white/70 text-slate-900 shadow-lg backdrop-blur transition hover:bg-white sm:right-6 sm:h-12 sm:w-12"
            @click.stop="nextGalleryImage"
          >
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M9 18l6-6-6-6" />
            </svg>
          </button>

          <div
            class="flex h-full w-full items-center justify-center px-4 py-16 sm:px-8 sm:py-20"
            @touchstart.passive="onGalleryTouchStart"
            @touchmove.passive="onGalleryTouchMove"
            @touchend="onGalleryTouchEnd"
            @touchcancel="onGalleryTouchEnd"
            @click.stop
          >
            <Transition name="gallery-image" mode="out-in">
              <img
                :key="activeGalleryItem.key"
                :src="activeGalleryItem.src"
                alt="Review image preview"
                class="max-h-[88vh] max-w-[96vw] object-contain drop-shadow-[0_30px_70px_rgba(15,23,42,0.25)]"
              />
            </Transition>
          </div>

          <div
            v-if="galleryImages.length > 1"
            class="absolute bottom-4 left-1/2 z-20 flex max-w-[92vw] -translate-x-1/2 gap-2 overflow-x-auto rounded-2xl border border-white/25 bg-white/55 px-3 py-3 shadow-xl backdrop-blur-md sm:bottom-6"
            @click.stop
          >
            <button
              v-for="(image, index) in galleryImages"
              :key="image.key"
              type="button"
              class="shrink-0 overflow-hidden rounded-xl border transition"
              :class="index === activeGalleryIndex
                ? 'border-slate-900 shadow-md'
                : 'border-white/30 opacity-75 hover:opacity-100'"
              @click="activeGalleryIndex = index"
            >
              <img
                :src="image.src"
                alt="Review image thumbnail"
                class="h-14 w-14 object-cover sm:h-16 sm:w-16"
              />
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>

    <Teleport to="body">
      <Transition name="modal-fade">
          <div
            v-if="addReviewOpen"
            class="review-modal-overlay"
            @click="closeAddReview"
          >
            <div
              class="review-modal-dialog"
              @click.stop
            >
              <div class="review-modal-header">
                <div>
                  <div class="review-modal-title">
                    Add a review
                </div>
                <div class="review-modal-subtitle">
                  Share your experience with this product.
                </div>
              </div>

              <button
                type="button"
                class="review-modal-close"
                @click="closeAddReview"
              >
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M6 6l12 12M18 6L6 18" />
                </svg>
                </button>
              </div>

              <div class="review-modal-body">
                <Transition name="review-step" mode="out-in">
                  <div v-if="addReviewStep === 1" key="review-step-1" class="review-rating-step">
                    <h3 class="text-xl font-semibold tracking-[-0.02em] text-slate-950">
                      How would you rate this product?
                    </h3>
                  <p class="mt-2 text-sm leading-6 text-slate-500">
                    We would love it if you would share a bit about your experience.
                  </p>

                  <div class="mt-7 flex flex-col items-center">
                    <img
                      v-if="props.productImage"
                      :src="props.productImage"
                      :alt="props.productName || 'Product image'"
                      class="h-32 w-auto max-w-[220px] object-contain sm:h-36 sm:max-w-[260px]"
                    >
                    <div v-else class="text-xs text-slate-400">
                      No image
                    </div>

                    <div class="mt-4 text-base font-semibold text-slate-950">
                      {{ props.productName || 'This product' }}
                    </div>
                  </div>

                  <div
                    class="mt-6 flex items-center justify-center gap-2 sm:gap-3"
                    @mouseleave="hoveredDraftRating = 0"
                  >
                    <button
                      v-for="star in 5"
                      :key="`draft-star-${star}`"
                      type="button"
                      class="rounded-full p-1 transition hover:scale-105"
                      :aria-label="`Rate ${star} star${star === 1 ? '' : 's'}`"
                      @mouseenter="hoveredDraftRating = star"
                      @focus="hoveredDraftRating = star"
                      @blur="hoveredDraftRating = 0"
                      @click="selectDraftRating(star)"
                    >
                      <svg
                        viewBox="0 0 24 24"
                        class="h-10 w-10 transition sm:h-11 sm:w-11"
                        :class="star <= interactiveDraftRating ? 'text-[#f2a536]' : 'text-slate-300'"
                        fill="currentColor"
                      >
                        <path
                          d="M12 2.25l2.917 5.91 6.523.948-4.72 4.6 1.114 6.497L12 17.118 6.166 20.205l1.114-6.497-4.72-4.6 6.523-.948L12 2.25z"
                        />
                      </svg>
                    </button>
                  </div>

                  <div class="mt-4 text-sm font-medium text-slate-500">
                    {{ draftRating ? `${draftRating} out of 5 selected` : 'Tap a star rating to continue' }}
                  </div>
                </div>

                <form
                  v-else
                  id="add-review-form"
                  key="review-step-2"
                  class="review-form"
                  @submit.prevent="submitAddReview"
                >
                  <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                      <img
                        v-if="props.productImage"
                        :src="props.productImage"
                        :alt="props.productName || 'Product image'"
                        class="h-14 w-auto max-w-[72px] shrink-0 object-contain"
                      >

                      <div>
                        <div class="text-sm font-semibold text-slate-950">
                          {{ props.productName || 'This product' }}
                        </div>
                        <div class="mt-1 flex items-center gap-1">
                          <svg
                            v-for="star in 5"
                            :key="`selected-star-${star}`"
                            viewBox="0 0 24 24"
                            class="h-4 w-4"
                            :class="star <= draftRating ? 'text-[#f2a536]' : 'text-slate-300'"
                            fill="currentColor"
                          >
                            <path
                              d="M12 2.25l2.917 5.91 6.523.948-4.72 4.6 1.114 6.497L12 17.118 6.166 20.205l1.114-6.497-4.72-4.6 6.523-.948L12 2.25z"
                            />
                          </svg>
                        </div>
                      </div>
                    </div>

                    <button
                      type="button"
                      class="text-sm font-semibold text-slate-500 transition hover:text-slate-900"
                      @click="backToRatingStep"
                    >
                      Change
                    </button>
                  </div>

                  <div>
                    <label for="review-invoice-id" class="mb-2 block text-sm font-semibold text-slate-900">
                      Please enter your Order INV ID
                    </label>
                    <div
                      class="flex overflow-hidden rounded-2xl border border-slate-200 bg-white transition focus-within:border-slate-400 focus-within:ring-4 focus-within:ring-slate-900/5"
                    >
                      <span class="flex items-center bg-slate-50 px-4 text-sm font-semibold text-slate-500">
                        INV-
                      </span>
                      <input
                        id="review-invoice-id"
                        v-model="invoiceDigits"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        placeholder="e.g. 010"
                        class="w-full flex-1 bg-white px-4 py-3 text-sm text-slate-900 outline-none placeholder:text-slate-400"
                        @input="invoiceDigits = sanitizeInvoiceDigits(invoiceDigits)"
                      >
                    </div>
                  </div>

                  <div>
                    <label for="review-customer-name" class="mb-2 block text-sm font-semibold text-slate-900">
                      Your name (optional)
                    </label>
                    <input
                      id="review-customer-name"
                      v-model="customerName"
                      type="text"
                      placeholder="Enter your name"
                      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-4 focus:ring-slate-900/5 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400"
                      :disabled="postAnonymously"
                    >

                    <label class="mt-3 flex items-center gap-2 text-sm text-slate-600">
                      <input
                        id="review-anonymous"
                        v-model="postAnonymously"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900/20"
                      >
                      <span>Post as anonymous</span>
                    </label>
                  </div>

                  <div>
                    <label for="review-content" class="mb-2 block text-sm font-semibold text-slate-900">
                      Review content (optional)
                    </label>
                    <textarea
                      id="review-content"
                      v-model="reviewContent"
                      rows="5"
                      placeholder="Tell us about your experience with this product"
                      class="w-full resize-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-4 focus:ring-slate-900/5"
                    ></textarea>
                  </div>

                  <div>
                    <label for="review-images" class="mb-2 block text-sm font-semibold text-slate-900">
                      Add images
                    </label>

                    <input
                      :key="reviewImageInputKey"
                      id="review-images"
                      type="file"
                      accept="image/*"
                      multiple
                      class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800"
                      @change="onReviewImagesChange"
                    >

                    <p class="mt-2 text-xs text-slate-500">
                      Up to {{ MAX_REVIEW_IMAGES }} images, {{ MAX_REVIEW_IMAGE_MB }}MB each (JPG, PNG, WebP).
                    </p>

                    <div v-if="reviewImagePreviewUrls.length" class="mt-3 flex flex-wrap gap-3">
                      <div
                        v-for="(url, index) in reviewImagePreviewUrls"
                        :key="`preview-image-${index}`"
                        class="relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50"
                      >
                        <img
                          :src="url"
                          alt="Selected review image"
                          class="h-20 w-20 object-cover sm:h-24 sm:w-24"
                        >
                        <button
                          type="button"
                          class="absolute right-1 top-1 inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-white/90 text-slate-600 shadow-sm transition hover:bg-white hover:text-slate-900"
                          aria-label="Remove image"
                          @click="removeReviewImage(index)"
                        >
                          <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 6l12 12M18 6L6 18" />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>

                </form>
              </Transition>
            </div>

            <div
              v-if="addReviewStep === 2"
              class="review-modal-footer"
            >
              <div
                v-if="submitError"
                class="mb-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
              >
                {{ submitError }}
              </div>

              <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                <button
                  type="button"
                  class="review-modal-secondary"
                  @click="closeAddReview"
                >
                  Cancel
                </button>

                <button
                  type="submit"
                  form="add-review-form"
                  class="review-modal-primary"
                  :disabled="submitting"
                >
                  <span v-if="submitting">Submitting...</span>
                  <span v-else>Add review</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.product-reviews-panel {
  border-top: 1px solid #dbe3ef;
  border-bottom: 1px solid #dbe3ef;
  background: transparent;
}

.product-reviews-header {
  display: flex;
  flex-direction: column;
  gap: 14px;
  border-bottom: 1px solid #dbe3ef;
  padding: 18px 0;
}

.product-reviews-body {
  padding: 20px 0 22px;
}

.review-add-button,
.review-load-more,
.review-modal-primary,
.review-modal-secondary {
  display: inline-flex;
  min-height: 44px;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  padding: 0 18px;
  font-size: 14px;
  font-weight: 700;
  transition:
    background-color 0.18s ease,
    border-color 0.18s ease,
    color 0.18s ease,
    transform 0.18s ease;
}

.review-add-button,
.review-modal-primary {
  border: 1px solid #071f4f;
  background: #071f4f;
  color: #ffffff;
}

.review-add-button:hover,
.review-modal-primary:hover:not(:disabled) {
  background: #0b2b62;
  transform: translateY(-1px);
}

.review-load-more,
.review-modal-secondary {
  width: 100%;
  border: 1px solid #cbd5e1;
  background: transparent;
  color: #071f4f;
}

.review-load-more:hover:not(:disabled),
.review-modal-secondary:hover {
  border-color: #071f4f;
  background: rgba(7, 31, 79, 0.04);
}

.review-load-more:disabled,
.review-modal-primary:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.review-empty-state {
  padding: 34px 0;
  color: #64748b;
  font-size: 14px;
  text-align: center;
}

.review-list {
  display: grid;
  gap: 0;
}

.review-item {
  border-top: 1px solid #dbe3ef;
  padding: 22px 0;
}

.review-item:first-child {
  border-top: 0;
  padding-top: 0;
}

.review-item:last-child {
  padding-bottom: 0;
}

.review-item__meta {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.review-avatar {
  display: flex;
  width: 40px;
  height: 40px;
  flex: 0 0 40px;
  align-items: center;
  justify-content: center;
  border: 1px solid #dbe3ef;
  border-radius: 999px;
  background: transparent;
  color: #64748b;
}

.review-title {
  margin-top: 14px;
  color: #071f4f;
  font-size: 15px;
  font-weight: 800;
  line-height: 1.35;
}

.review-copy {
  margin-top: 6px;
  color: #43506e;
  font-size: 14px;
  line-height: 1.75;
  white-space: pre-line;
}

.review-image-button {
  position: relative;
  overflow: hidden;
  border: 1px solid #dbe3ef;
  border-radius: 8px;
  background: transparent;
  transition:
    border-color 0.18s ease,
    transform 0.18s ease;
}

.review-image-button:hover {
  border-color: #071f4f;
  transform: translateY(-1px);
}

.review-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 150;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(15, 23, 42, 0.48);
  padding: 24px;
}

.review-modal-dialog {
  display: flex;
  width: min(720px, 100%);
  max-height: calc(100vh - 48px);
  flex-direction: column;
  overflow: hidden;
  border: 1px solid #dbe3ef;
  border-radius: 18px;
  background: #f8fafc;
}

.review-modal-header,
.review-modal-footer {
  flex: 0 0 auto;
  background: #f8fafc;
}

.review-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  border-bottom: 1px solid #dbe3ef;
  padding: 18px 22px;
}

.review-modal-title {
  color: #071f4f;
  font-size: 18px;
  font-weight: 800;
  line-height: 1.25;
}

.review-modal-subtitle {
  margin-top: 4px;
  color: #64748b;
  font-size: 14px;
  line-height: 1.45;
}

.review-modal-close {
  display: inline-flex;
  width: 40px;
  height: 40px;
  flex: 0 0 40px;
  align-items: center;
  justify-content: center;
  border: 1px solid #cbd5e1;
  border-radius: 999px;
  background: transparent;
  color: #475569;
  transition:
    border-color 0.18s ease,
    color 0.18s ease,
    background-color 0.18s ease;
}

.review-modal-close:hover {
  border-color: #071f4f;
  background: rgba(7, 31, 79, 0.04);
  color: #071f4f;
}

.review-modal-body {
  min-height: 0;
  flex: 1 1 auto;
  overflow-y: auto;
  padding: 22px;
}

.review-rating-step {
  text-align: center;
}

.review-form {
  display: grid;
  gap: 18px;
}

.review-modal-footer {
  border-top: 1px solid #dbe3ef;
  padding: 16px 22px;
}

.review-modal-footer > div:last-child {
  display: flex;
  flex-direction: column-reverse;
  gap: 12px;
}

.review-modal-primary,
.review-modal-secondary {
  width: 100%;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition:
    opacity 0.24s ease,
    transform 0.24s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.review-step-enter-active,
.review-step-leave-active {
  transition:
    opacity 0.28s ease,
    transform 0.28s ease,
    filter 0.28s ease;
}

.review-step-enter-from,
.review-step-leave-to {
  opacity: 0;
  transform: translateY(14px);
  filter: blur(2px);
}

.gallery-image-enter-active,
.gallery-image-leave-active {
  transition: opacity 0.18s ease;
}

.gallery-image-enter-from,
.gallery-image-leave-to {
  opacity: 0;
}

@media (min-width: 640px) {
  .product-reviews-header {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
  }

  .review-add-button,
  .review-modal-primary,
  .review-modal-secondary {
    width: auto;
  }

  .review-modal-footer > div:last-child {
    flex-direction: row;
    align-items: center;
    justify-content: flex-end;
  }
}

@media (max-width: 640px) {
  .review-item__meta {
    flex-direction: column;
    gap: 8px;
  }

  .review-modal-overlay {
    align-items: flex-end;
    padding: 10px;
  }

  .review-modal-dialog {
    width: 100%;
    max-height: calc(100vh - 20px);
    border-radius: 16px;
  }

  .review-modal-header,
  .review-modal-body,
  .review-modal-footer {
    padding-inline: 16px;
  }
}

@media (prefers-reduced-motion: reduce) {
  * {
    scroll-behavior: auto !important;
    transition: none !important;
    animation: none !important;
  }
}
</style>

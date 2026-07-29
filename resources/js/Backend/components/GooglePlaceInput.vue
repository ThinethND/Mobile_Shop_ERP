<template>
  <div class="relative">
    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-200 mb-1">
      Where's your business located? <sup class="text-rose-500">*</sup>
    </label>

    <div class="relative">
      <input
        ref="inputRef"
        type="text"
        v-model="query"
        @focus="showSuggestions = suggestions.length > 0"
        @input="onInput"
        placeholder="Search your business location..."
        autocomplete="off"
        class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-[#38bdf8] focus:ring focus:ring-[#38bdf8]/30 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
      />

      <!-- Custom Dropdown -->
      <ul
        v-if="showSuggestions && suggestions.length"
        class="absolute left-0 right-0 mt-1 max-h-60 overflow-auto rounded-md border border-zinc-200 bg-white text-sm shadow-lg z-50 dark:border-zinc-700 dark:bg-zinc-900"
      >
        <li
          v-for="(item, i) in suggestions"
          :key="item.place_id"
          class="cursor-pointer px-3 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-800"
          @mousedown.prevent="selectSuggestion(item)"
        >
          <div class="font-medium text-zinc-800 dark:text-zinc-100">
            {{ item.structured_formatting.main_text }}
          </div>
          <div class="text-xs text-zinc-500">
            {{ item.structured_formatting.secondary_text }}
          </div>
        </li>
      </ul>
    </div>

    <!-- Optional Map Preview -->
    <div v-if="latitude && longitude" class="mt-2 h-40 w-full rounded-lg overflow-hidden">
      <iframe
        :src="`https://www.google.com/maps?q=${latitude},${longitude}&hl=es;z=14&output=embed`"
        width="100%"
        height="100%"
        style="border:0;"
        loading="lazy"
      ></iframe>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'

const props = defineProps({
  modelValue: String,
  latitude: Number,
  longitude: Number,
})

const emit = defineEmits(['update:modelValue', 'update:latitude', 'update:longitude'])

const query = ref(props.modelValue || '')
const suggestions = ref([])
const showSuggestions = ref(false)
let autocompleteService = null
let placesService = null
let debounceTimer = null

// Sync external changes
watch(
  () => props.modelValue,
  (v) => {
    if (v !== query.value) query.value = v || ''
  }
)

onMounted(() => {
  loadGoogle(() => {
    autocompleteService = new google.maps.places.AutocompleteService()
    const dummy = document.createElement('div')
    placesService = new google.maps.places.PlacesService(dummy)
  })
  window.addEventListener('click', handleOutsideClick)
})

onBeforeUnmount(() => {
  window.removeEventListener('click', handleOutsideClick)
})

function loadGoogle(cb) {
  if (window.google && window.google.maps && window.google.maps.places) return cb()

  const existing = document.querySelector('script[data-gmaps="places"]')
  if (existing) {
    existing.addEventListener('load', cb)
    return
  }

  const script = document.createElement('script')
  script.src = `https://maps.googleapis.com/maps/api/js?key=${import.meta.env.VITE_GOOGLE_MAPS_API_KEY}&libraries=places`
  script.dataset.gmaps = 'places'
  script.async = true
  script.defer = true
  script.onload = cb
  document.head.appendChild(script)
}

function handleOutsideClick(e) {
  if (!e.target.closest('.relative')) showSuggestions.value = false
}

function onInput() {
  emit('update:modelValue', query.value)
  if (!query.value.trim()) {
    suggestions.value = []
    showSuggestions.value = false
    return
  }

  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    if (!autocompleteService) {
      // Retry until service ready
      loadGoogle(() => {
        autocompleteService = new google.maps.places.AutocompleteService()
        fetchPredictions()
      })
    } else {
      fetchPredictions()
    }
  }, 300)
}

function fetchPredictions() {
  autocompleteService.getPlacePredictions(
    {
      input: query.value,
      types: ['geocode', 'establishment'],
    },
    (preds, status) => {
      if (status !== google.maps.places.PlacesServiceStatus.OK || !preds) {
        suggestions.value = []
        showSuggestions.value = false
        return
      }
      suggestions.value = preds
      showSuggestions.value = true
    }
  )
}

function selectSuggestion(s) {
  query.value = s.description
  emit('update:modelValue', s.description)
  showSuggestions.value = false

  if (!placesService) return
  placesService.getDetails(
    { placeId: s.place_id, fields: ['geometry', 'formatted_address'] },
    (place, status) => {
      if (status !== google.maps.places.PlacesServiceStatus.OK || !place) return
      const lat = place.geometry?.location?.lat()
      const lng = place.geometry?.location?.lng()
      if (place.formatted_address) emit('update:modelValue', place.formatted_address)
      if (lat && lng) {
        emit('update:latitude', lat)
        emit('update:longitude', lng)
      }
    }
  )
}
</script>

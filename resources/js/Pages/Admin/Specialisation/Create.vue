<template>
  <div v-if="show" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="$emit('close')"></div>

    <div class="relative w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-center justify-between">
          <div class="text-white">
            <div class="text-xl font-semibold">Add Specialisation</div>
            <div class="text-sm opacity-90">Create a new specialisation</div>
          </div>
          <button @click="$emit('close')" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
        </div>
      </div>

      <div class="bg-white p-6">
        <form @submit.prevent="submitCreate" class="space-y-5">
          <div v-if="generalError" class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="text-sm text-red-800">{{ generalError }}</div>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
            <input
              v-model="name"
              class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]"
              placeholder="e.g. Anxiety Disorders"
            />
            <p v-if="fieldErrors.name" class="mt-1 text-sm text-red-600">{{ fieldErrors.name }}</p>
          </div>

          <div class="flex items-center gap-3 pt-2">
            <button
              :disabled="creating"
              class="px-5 py-2.5 bg-[rgb(141,61,79)] text-white rounded-lg shadow hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed font-medium"
            >
              {{ creating ? 'Creating...' : 'Create' }}
            </button>
            <button type="button" @click="$emit('close')" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({ show: Boolean })
const emit = defineEmits(['close', 'created'])

const name = ref('')
const creating = ref(false)
const generalError = ref(null)
const fieldErrors = ref({ name: '' })

watch(
  () => props.show,
  (isShowing) => {
    if (!isShowing) return
    name.value = ''
    creating.value = false
    generalError.value = null
    fieldErrors.value = { name: '' }
  }
)

async function ensureCsrfToken() {
  const tokenEl = document.querySelector('meta[name="csrf-token"]')
  const metaToken = tokenEl?.getAttribute('content') || ''
  if (metaToken) return { token: metaToken, type: 'meta' }

  const m1 = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  if (m1) return { token: decodeURIComponent(m1[1]), type: 'cookie' }

  try {
    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'same-origin' })
  } catch {}

  const m2 = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return m2 ? { token: decodeURIComponent(m2[1]), type: 'cookie' } : { token: '', type: 'none' }
}

async function submitCreate() {
  generalError.value = null
  fieldErrors.value = { name: '' }
  creating.value = true

  try {
    const csrf = await ensureCsrfToken()

    const res = await fetch('/specialisations', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(csrf.token ? { 'X-CSRF-TOKEN': csrf.type === 'meta' ? csrf.token : undefined, 'X-XSRF-TOKEN': csrf.type === 'cookie' ? csrf.token : undefined } : {}),
      },
      body: JSON.stringify({ name: name.value }),
    })

    if (res.status === 201) {
      const json = await res.json()
      emit('created', json.specialisation)
      emit('close')
      return
    }

    if (res.status === 422) {
      const json = await res.json()
      const errors = json?.errors || {}
      fieldErrors.value = { name: errors?.name?.[0] || '' }
      return
    }

    const text = await res.text()
    generalError.value = text || 'Failed to create specialisation.'
  } catch (e) {
    generalError.value = e?.message || 'Failed to create specialisation.'
  } finally {
    creating.value = false
  }
}
</script>

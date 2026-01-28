<template>
  <div class="p-6">
    <header class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">App Fee</h1>
        <p class="text-sm text-gray-600">Set the platform fee percentage taken from psychologist sessions.</p>
      </div>
    </header>

    <div v-if="flash" class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3">
      <div class="flex items-center justify-between">
        <div class="text-sm text-green-800">{{ flash }}</div>
        <button @click="flash = ''" class="text-green-700/70 hover:text-green-800">Dismiss</button>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-xl">
      <label class="block text-sm font-medium text-gray-700 mb-2">Percentage (%)</label>
      <div class="flex gap-3 items-center">
        <input v-model="percentage" type="number" step="0.01" min="0" max="100" class="w-full rounded-lg border-gray-300 px-3 py-2" />
        <button
          @click="save"
          :disabled="saving"
          class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg shadow btn-save disabled:opacity-50 min-w-[120px] justify-center"
          style="background-color:#AF5166;"
        >
          <svg v-if="!saving" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
            <path d="M5 13v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6" />
            <path d="M9 15V9a3 3 0 0 1 3-3h0a3 3 0 0 1 3 3v6" />
            <path d="M12 3v4" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4 animate-spin stroke-white">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25"/>
            <path d="M4 12a8 8 0 0 0 8 8" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
          </svg>
          <span>{{ saving ? 'Saving...' : 'Save7' }}</span>
        </button>
      </div>
      <p class="mt-3 text-sm text-gray-500">Enter a percentage. You can update this value later.</p>
    </div>
  </div>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

defineOptions({ layout: AdminLayout })

const props = defineProps({ appFee: Object })
const page = usePage()

const appFeeLocal = ref(props.appFee ? { ...props.appFee } : null)
const percentage = ref(appFeeLocal.value?.percentage ?? '')
const saving = ref(false)
const flash = ref(page.props.value?.flash?.success ?? '')

async function ensureCsrfToken() {
  const tokenEl = document.querySelector('meta[name="csrf-token"]')
  const metaToken = tokenEl?.getAttribute('content') || ''
  if (metaToken) return { token: metaToken, type: 'meta' }
  const m1 = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  if (m1) return { token: decodeURIComponent(m1[1]), type: 'cookie' }
  try { await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'same-origin' }) } catch {}
  const m2 = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return m2 ? { token: decodeURIComponent(m2[1]), type: 'cookie' } : { token: '', type: 'none' }
}

async function save() {
  // If updating existing fee, show confirmation
  if (appFeeLocal.value?.id) {
    const current = String(appFeeLocal.value.percentage ?? '')
    const next = String(percentage.value ?? '')
    const confirm = await Swal.fire({
      title: 'Confirm change',
      html: `Change app fee from <strong>${current}%</strong> to <strong>${next}%</strong>?`,
      icon: 'warning',
      iconColor: 'rgb(89 151 172 / var(--tw-bg-opacity, 1))',
      showCancelButton: true,
      confirmButtonText: 'Yes, change',
      confirmButtonColor: 'rgb(175 81 102 / var(--tw-bg-opacity, 1))',
      cancelButtonText: 'Cancel',
      cancelButtonColor: 'rgb(107 114 128 / var(--tw-bg-opacity, 1))',
      reverseButtons: true,
    })

    if (!confirm.isConfirmed) return
  }

  saving.value = true
  const csrf = await ensureCsrfToken()
  const payload = { percentage: percentage.value }
  const method = appFeeLocal.value?.id ? 'PATCH' : 'POST'
  const url = appFeeLocal.value?.id ? `/app-fees/${appFeeLocal.value.id}` : '/app-fees'

  try {
    const res = await fetch(url, {
      method,
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json, text/html, */*',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(csrf.token ? { 'X-CSRF-TOKEN': csrf.type === 'meta' ? csrf.token : undefined, 'X-XSRF-TOKEN': csrf.type === 'cookie' ? csrf.token : undefined } : {}),
      },
      body: JSON.stringify(payload),
    })

    if (!res.ok) {
      const txt = await res.text()
      throw new Error(txt || 'Save failed')
    }

    // If server returned JSON, update local state and show toast
    const ct = res.headers.get('content-type') || ''
    if (ct.includes('application/json')) {
      const json = await res.json()
      if (json.appFee) {
        appFeeLocal.value = { ...json.appFee }
        percentage.value = json.appFee.percentage
      }
    }

    await Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: 'App fee saved',
      showConfirmButton: false,
      timer: 1800,
      timerProgressBar: true,
    })
  } catch (e) {
    Swal.fire({ icon: 'error', title: 'Save failed', text: e?.message || '' })
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.animate-spin { animation: spin 1s linear infinite }
@keyframes spin { to { transform: rotate(360deg) } }
</style>

/* Save button custom color (use solid hex for broader compatibility) */
.btn-save {
  background-color: #AF5166 !important;
  transition: background-color 150ms ease;
}
.btn-save:hover {
  background-color: #963f53 !important;
}


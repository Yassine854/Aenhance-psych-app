<script setup>
import { ref, watch, computed } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  show: { type: Boolean },
  profile: { type: Object, default: null },
  authUser: { type: Object, default: null },
  reportedRole: { type: String, default: null },
})

const emit = defineEmits(['close', 'sent'])

const reason = ref('')
const reasonSelect = ref('')
const otherReason = ref('')
const proof = ref(null)
const proofInput = ref(null)
const proofPreview = ref('')
const errors = ref({})
const sending = ref(false)

const psychologistReasons = [
  { value: 'harassment', label: 'Harassment or inappropriate behaviour' },
  { value: 'privacy', label: 'Privacy violation / sharing personal data' },
  { value: 'fraud', label: 'Fraudulent activity / scamming' },
  { value: 'unprofessional', label: 'Unprofessional conduct' },
  { value: 'no_show', label: 'Missed appointment without notice' },
  { value: 'other', label: 'Other' },
]

const patientReasons = [
  { value: 'harassment', label: 'Harassment or inappropriate behaviour' },
  { value: 'no_show', label: 'Missed appointment without notice' },
  { value: 'abusive', label: 'Abusive or threatening behaviour' },
  { value: 'other', label: 'Other' },
]

const isReportingPatient = computed(() => {
  if (props.reportedRole) return String(props.reportedRole).toLowerCase() === 'patient'
  const userRole = String(props.profile?.user?.role || '').toUpperCase()
  if (userRole === 'PSYCHOLOGIST') return false
  if (userRole === 'PATIENT') return true
  if (props.profile?.price_per_session !== undefined) return false
  if (Array.isArray(props.profile?.specialisations) && props.profile.specialisations.length) return false
  if (props.profile?.is_approved !== undefined) return false
  return true
})

const reasonsList = computed(() => (isReportingPatient.value ? patientReasons : psychologistReasons))

const modalTitle = computed(() => (isReportingPatient.value ? 'Report patient' : 'Report psychologist'))
const modalSubtitle = computed(() => (isReportingPatient.value ? 'Tell us why you are reporting this patient' : 'Tell us why you are reporting this psychologist'))

const displayName = computed(() => {
  const prof = props.profile || {}
  const userName = (prof.user && (prof.user.name || prof.user.full_name || prof.user.first_name && `${prof.user.first_name} ${prof.user.last_name}`)) || ''
  if (userName) return userName
  const first = prof.first_name || ''
  const last = prof.last_name || ''
  const combined = `${first} ${last}`.trim()
  if (combined) return combined
  return prof?.user?.email || prof?.name || '—'
})

watch(() => props.show, (v) => {
  if (!v) {
    reason.value = ''
    reasonSelect.value = ''
    otherReason.value = ''
    proof.value = null
    errors.value = {}
  }
})

function closeModal() {
  try { document.activeElement && document.activeElement.blur() } catch (e) { /* ignore */ }
  emit('close')
}

function onFileChange(e) {
  proof.value = e?.target?.files?.[0] || null
}

function onDrop(e) {
  const file = e?.dataTransfer?.files?.[0] || null
  if (!file) return
  if (!file.type.startsWith('image/')) return
  proof.value = file
}

// keep a preview URL for the selected image and revoke previous
let _prevObjectUrl = null
watch(proof, (f) => {
  if (_prevObjectUrl) {
    try { URL.revokeObjectURL(_prevObjectUrl) } catch (e) {}
    _prevObjectUrl = null
  }
  if (f) {
    try {
      _prevObjectUrl = URL.createObjectURL(f)
      proofPreview.value = _prevObjectUrl
    } catch (e) {
      proofPreview.value = ''
    }
  } else {
    proofPreview.value = ''
  }
})

async function submit() {
  errors.value = {}
  // validate select + other
  if (!reasonSelect.value) {
    errors.value.reason = 'Please select a reason.'
    return
  }
  if (reasonSelect.value === 'other') {
    if (!otherReason.value || !otherReason.value.trim()) {
      errors.value.reason = 'Please specify the other reason.'
      return
    }
  }

  sending.value = true
  try {
    const fd = new FormData()
    fd.append('reporter_id', props.authUser?.id || '')
    const reporterType = props.authUser && String(props.authUser.role || '').toUpperCase() === 'PATIENT' ? 'patient' : 'psychologist'
    fd.append('reporter_type', reporterType)
    fd.append('reported_id', props.profile?.id || '')
    // determine reported_type: prefer explicit prop, otherwise infer
    const reportedType = props.reportedRole ? String(props.reportedRole).toLowerCase() : (isReportingPatient.value ? 'patient' : 'psychologist')
    fd.append('reported_type', reportedType)
    // decide final reason text
    const finalReason = reasonSelect.value === 'other' ? otherReason.value.trim() : (reasonsList.value.find(r => r.value === reasonSelect.value)?.label || reasonSelect.value)
    fd.append('reason', finalReason)
    if (proof.value) fd.append('proof_image', proof.value)

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    const res = await fetch(route('reports.store'), {
      method: 'POST',
      body: fd,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      credentials: 'same-origin',
    })

    const json = await res.json().catch(() => ({}))

    if (!res.ok) {
      const errs = json?.errors || {}
      errors.value = errs
      Swal.fire({ icon: 'error', title: 'Report failed', text: json?.message || 'Unable to send report' })
      return
    }

    Swal.fire({ position: 'top-end', icon: 'success', title: 'Report sent', toast: true, timer: 2500, showConfirmButton: false })
    emit('sent', json?.data)
    closeModal()
  } catch (e) {
    Swal.fire({ icon: 'error', title: 'Report failed', text: 'Unable to send report' })
  } finally {
    sending.value = false
  }
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-[1200] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>

    <div class="relative w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden z-[1201]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-4">
        <div class="flex items-center justify-between">
          <div class="text-white">
            <div class="text-lg font-semibold">{{ modalTitle }}</div>
            <div class="text-sm opacity-90">{{ modalSubtitle }}</div>
          </div>
          <button @click="closeModal" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
        </div>
      </div>

      <div class="bg-white p-6 max-h-[70vh] overflow-y-auto styled-scrollbar">
        <div class="space-y-4">
          <div>
            <label class="text-sm font-medium text-gray-700">{{ isReportingPatient ? 'Patient' : 'Psychologist' }}</label>
            <div class="mt-1 text-sm text-gray-800 font-semibold">{{ displayName }}</div>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">Reason <span class="text-red-500">*</span></label>
            <select v-model="reasonSelect" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
              <option value="" disabled>Select a reason</option>
              <option v-for="r in reasonsList" :key="r.value" :value="r.value">{{ r.label }}</option>
            </select>
            <p v-if="errors.reason" class="mt-1 text-sm text-red-600">{{ Array.isArray(errors.reason) ? errors.reason[0] : errors.reason }}</p>

            <div v-if="reasonSelect === 'other'" class="mt-3">
              <label class="text-sm font-medium text-gray-700">Please specify</label>
              <input v-model="otherReason" type="text" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
              <p v-if="errors.reason" class="mt-1 text-sm text-red-600">{{ Array.isArray(errors.reason) ? errors.reason[0] : errors.reason }}</p>
            </div>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">Proof image (optional)</label>
            <div
              @click="proofInput?.click()"
              @drop.prevent="onDrop"
              @dragover.prevent
              class="mt-1 border-2 border-dashed border-gray-300 rounded-lg p-3 flex items-center justify-center hover:border-[rgb(89,151,172)] hover:bg-gray-50 transition cursor-pointer"
            >
              <img v-if="proofPreview" :src="proofPreview" class="h-16 w-16 rounded-full object-cover" />
              <span v-else class="text-sm text-gray-600">Drag & drop or click</span>
            </div>
            <input ref="proofInput" type="file" accept="image/*" @change="onFileChange" class="hidden" />
            <p v-if="errors.proof_image" class="mt-1 text-sm text-red-600">{{ Array.isArray(errors.proof_image) ? errors.proof_image[0] : errors.proof_image }}</p>
          </div>

          <div class="flex items-center gap-3 pt-4">
            <button :disabled="sending" @click.prevent="submit" class="px-5 py-2.5 bg-[rgb(141,61,79)] text-white rounded-lg shadow hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed font-medium">
              {{ sending ? 'Sending...' : 'Send report' }}
            </button>
            <button type="button" @click="closeModal" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

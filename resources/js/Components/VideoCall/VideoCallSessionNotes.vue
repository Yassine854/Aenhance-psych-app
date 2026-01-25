<template>
  <div v-if="show" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="close"></div>
    <div class="relative w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-center justify-between">
          <div class="text-white">
            <div class="text-xl font-semibold">Session Notes</div>
            <div class="text-sm opacity-90">Add clinical notes for this session. These are private and saved to the patient's file.</div>
          </div>
          <button @click="close" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
        </div>
      </div>

      <div class="bg-white p-6 max-h-[70vh] overflow-y-auto styled-scrollbar">
        <form @submit.prevent="onSubmit" class="space-y-4">
          <div class="grid grid-cols-1 gap-3">
            <div class="grid grid-cols-2 gap-3">
              <label class="block">
                <span class="text-sm text-gray-600">Session mode <span class="text-red-500">*</span></span>
                <select required aria-required="true" v-model="form.session_mode" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                  <option value="video_audio">Video + Audio</option>
                  <option value="audio">Audio</option>
                  <option value="video">Video</option>
                </select>
              </label>

              <label class="block">
                <span class="text-sm text-gray-600">Risk level <span class="text-red-500">*</span></span>
                <select required aria-required="true" v-model="form.risk_level" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                  <option value="none">None</option>
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="very_high">Very high</option>
                </select>
              </label>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <label class="block">
                <span class="text-sm text-gray-600">Session date</span>
                <input type="datetime-local" v-model="form.session_date" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50/60 cursor-not-allowed text-gray-600" />
              </label>

              <label class="block">
                <span class="text-sm text-gray-600">Duration (minutes)</span>
                <input type="number" v-model.number="form.session_duration" min="0" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50/60 cursor-not-allowed text-gray-600" />
              </label>
            </div>

            <label class="block">
              <span class="text-sm text-gray-600">Subjective</span>
              <textarea v-model="form.subjective" rows="3" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
            </label>

            <label class="block">
              <span class="text-sm text-gray-600">Objective</span>
              <textarea v-model="form.objective" rows="3" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
            </label>

            <label class="block">
              <span class="text-sm text-gray-600">Assessment</span>
              <textarea v-model="form.assessment" rows="3" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
            </label>

            <label class="block">
              <span class="text-sm text-gray-600">Intervention</span>
              <textarea v-model="form.intervention" rows="3" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
            </label>

            <label class="block">
              <span class="text-sm text-gray-600">Plan</span>
              <textarea v-model="form.plan" rows="3" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
            </label>
          </div>

          <div class="pt-4 flex items-center gap-3">
            <button type="button" @click="onCancel" class="px-4 py-2 text-sm bg-white border border-gray-200 rounded-lg">Cancel</button>
            <button type="submit" :disabled="submitting" class="px-5 py-2.5 bg-[rgb(141,61,79)] text-white rounded-lg shadow hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed font-medium">
              {{ submitting ? 'Saving...' : 'Save notes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const show = ref(false)
const submitting = ref(false)
let resolvePromise = null

function pad(n) { return n < 10 ? '0' + n : String(n) }
function formatDatetimeLocal(input) {
  if (!input) return ''
  const d = input instanceof Date ? input : new Date(String(input))
  if (Number.isNaN(d.getTime())) return ''
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

const form = ref({
  appointment_session_id: null,
  session_date: formatDatetimeLocal(new Date()),
  session_duration: 0,
  session_mode: 'video_audio',
  risk_level: 'none',
  subjective: '',
  objective: '',
  assessment: '',
  intervention: '',
  plan: '',
})

function open(payload = {}) {
  form.value = Object.assign({}, form.value, payload || {})
  // Normalize incoming session_date to datetime-local format so input shows value
  if (form.value.session_date) {
    const fmt = formatDatetimeLocal(form.value.session_date)
    form.value.session_date = fmt || form.value.session_date
  }
  // Ensure duration is numeric
  if (form.value.session_duration != null) {
    form.value.session_duration = Number(form.value.session_duration) || 0
  }

  show.value = true
  return new Promise((resolve) => { resolvePromise = resolve })
}

function close() {
  show.value = false
  if (resolvePromise) { resolvePromise(null); resolvePromise = null }
}

function onCancel() {
  close()
}

function onSubmit() {
  if (submitting.value) return
  submitting.value = true
  // return payload and let parent call API
  const payload = { ...form.value }
  submitting.value = false
  show.value = false
  if (resolvePromise) { resolvePromise(payload); resolvePromise = null }
}

// expose methods to parent
defineExpose({ open })
</script>

<style scoped>
/* minimal visual polish; main styling is Tailwind classes */
</style>

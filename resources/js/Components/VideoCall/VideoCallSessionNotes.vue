<template>
  <div v-if="show" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="close"></div>
    <div class="relative w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-center justify-between">
          <div class="text-white">
            <div class="text-xl font-semibold">{{ t('videoCallNotes.title') }}</div>
            <div class="text-sm opacity-90">{{ t('videoCallNotes.subtitle') }}</div>
          </div>
          <button @click="close" class="text-white/90 hover:text-white text-2xl leading-none" :aria-label="t('videoCallNotes.close')">✕</button>
        </div>
      </div>

      <div class="bg-white p-6 max-h-[70vh] overflow-y-auto styled-scrollbar">
        <form @submit.prevent="onSubmit" class="space-y-4">
          <div class="grid grid-cols-1 gap-3">
            <div class="grid grid-cols-2 gap-3">
              <label class="block">
                <span class="text-sm text-gray-600">{{ t('videoCallNotes.sessionMode') }} <span class="text-red-500">*</span></span>
                <select required aria-required="true" v-model="form.session_mode" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                  <option value="video_audio">{{ t('videoCallNotes.modes.videoAudio') }}</option>
                  <option value="audio">{{ t('videoCallNotes.modes.audio') }}</option>
                  <option value="video">{{ t('videoCallNotes.modes.video') }}</option>
                </select>
              </label>

              <label class="block">
                <span class="text-sm text-gray-600">{{ t('videoCallNotes.riskLevel') }} <span class="text-red-500">*</span></span>
                <select required aria-required="true" v-model="form.risk_level" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                  <option value="none">{{ t('videoCallNotes.risks.none') }}</option>
                  <option value="low">{{ t('videoCallNotes.risks.low') }}</option>
                  <option value="medium">{{ t('videoCallNotes.risks.medium') }}</option>
                  <option value="high">{{ t('videoCallNotes.risks.high') }}</option>
                  <option value="very_high">{{ t('videoCallNotes.risks.veryHigh') }}</option>
                </select>
              </label>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <label class="block">
                <span class="text-sm text-gray-600">{{ t('videoCallNotes.sessionDate') }}</span>
                <input type="datetime-local" v-model="form.session_date" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50/60 cursor-not-allowed text-gray-600" />
              </label>

              <label class="block">
                <span class="text-sm text-gray-600">{{ t('videoCallNotes.durationMinutes') }}</span>
                <input type="number" v-model.number="form.session_duration" min="0" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50/60 cursor-not-allowed text-gray-600" />
              </label>
            </div>

            <label class="block">
              <span class="text-sm text-gray-600">{{ t('videoCallNotes.subjective') }}</span>
              <textarea v-model="form.subjective" rows="3" :placeholder="t('videoCallNotes.subjectivePlaceholder')" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
            </label>

            <label class="block">
              <span class="text-sm text-gray-600">{{ t('videoCallNotes.objective') }}</span>
              <textarea v-model="form.objective" rows="3" :placeholder="t('videoCallNotes.objectivePlaceholder')" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
            </label>

            <label class="block">
              <span class="text-sm text-gray-600">{{ t('videoCallNotes.assessment') }}</span>
              <textarea v-model="form.assessment" rows="3" :placeholder="t('videoCallNotes.assessmentPlaceholder')" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
            </label>

            <label class="block">
              <span class="text-sm text-gray-600">{{ t('videoCallNotes.intervention') }}</span>
              <textarea v-model="form.intervention" rows="3" :placeholder="t('videoCallNotes.interventionPlaceholder')" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
            </label>

            <label class="block">
              <span class="text-sm text-gray-600">{{ t('videoCallNotes.plan') }}</span>
              <textarea v-model="form.plan" rows="3" :placeholder="t('videoCallNotes.planPlaceholder')" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
            </label>
          </div>

          <div class="pt-4 flex items-center gap-3">
            <button type="button" @click="onCancel" class="px-4 py-2 text-sm bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">{{ t('videoCallNotes.cancel') }}</button>
            <button type="submit" :disabled="submitting" class="px-5 py-2.5 bg-[rgb(141,61,79)] text-white rounded-lg shadow hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed font-medium transition-opacity">
              {{ submitting ? t('videoCallNotes.saving') : t('videoCallNotes.saveNotes') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

function setLang(lang) {
  locale.value = lang
  localStorage.setItem('locale', lang)
  if (lang === 'ar') {
    document.documentElement.setAttribute('dir', 'rtl')
    document.documentElement.setAttribute('lang', 'ar')
    return
  }
  document.documentElement.setAttribute('dir', 'ltr')
  document.documentElement.setAttribute('lang', lang)
}

onMounted(() => {
  const savedLang = localStorage.getItem('locale') || locale.value
  setLang(savedLang)
})

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
[dir="rtl"] {
  text-align: right;
}

.styled-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgb(89 151 172 / var(--tw-bg-opacity, 1)) rgba(229, 231, 235, 1);
}
.styled-scrollbar::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}
.styled-scrollbar::-webkit-scrollbar-track {
  background: rgba(241, 245, 249, 1);
  border-radius: 9999px;
}
.styled-scrollbar::-webkit-scrollbar-thumb {
  background: rgb(89 151 172 / var(--tw-bg-opacity, 1));
  border-radius: 9999px;
  border: 2px solid #ffffff;
}
.styled-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgb(89 151 172 / 0.85);
}
</style>
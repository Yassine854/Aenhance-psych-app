<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="$emit('close')"></div>
    <div class="relative w-full max-w-7xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-white text-lg font-semibold">{{ t('payoutDetails.title') }} #{{ payout?.id ?? '—' }}</div>
            <div class="text-sm text-white/90">{{ patient?.name || '' }}</div>
          </div>
          <div>
            <button @click="$emit('close')" class="text-white/90 hover:text-white text-2xl leading-none" :aria-label="t('payoutDetails.close')">✕</button>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 max-h-[70vh] overflow-y-auto styled-scrollbar">
        <div class="notebook grid grid-cols-1 gap-6 md:grid-cols-2">
          <div class="book-page p-8 bg-[rgba(89,151,172,0.04)] rounded border border-[rgba(89,151,172,0.06)]">
            <div class="book-meta mb-4">
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-[rgb(24,58,63)] font-semibold text-lg">{{ t('payoutDetails.payout') }} #{{ payout?.id ?? '—' }}</div>
                  <div class="text-xs text-gray-500 mt-1">{{ t('payoutDetails.status') }}:
                    <span :class="['ml-1 inline-flex items-center px-2 py-1 rounded text-xs font-medium', payoutBadge(payout?.status)]">
                      {{ payoutStatusLabel(payout?.status) }}
                    </span>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <div class="text-sm text-gray-500">{{ formatDate(payout?.created_at || payout?.createdAt) }}</div>
                </div>
              </div>
            </div>

            <div class="space-y-5 text-sm text-[rgb(24,58,63)]">
              <div class="book-section">
                <div class="book-heading">{{ t('payoutDetails.appointment') }}</div>
                <div class="mt-1">{{ t('payoutDetails.date') }}: <span class="font-medium">{{ formatDate(payout?.appointment?.scheduled_start) }}</span></div>
              </div>
              <div class="book-section">
                <div class="book-heading">{{ t('payoutDetails.payment') }}</div>
                <div class="mt-1">{{ t('payoutDetails.gross') }}: <span class="font-medium">{{ payout?.gross_amount || '—' }} {{ payout?.currency || '' }}</span></div>
                <div class="mt-1">{{ t('payoutDetails.platformFee') }}: <span class="font-medium">{{ payout?.platform_fee || '—' }} {{ payout?.currency || '' }}</span></div>
              </div>

              <div class="book-section">
                <div class="book-heading">{{ t('payoutDetails.net') }}</div>
                <div class="mt-1 text-lg font-semibold">{{ payout?.net_amount || '—' }} {{ payout?.currency || '' }}</div>
              </div>
            </div>
          </div>

          <div class="book-page p-8 bg-white rounded border border-[rgba(15,23,42,0.04)]">
            <div class="space-y-5 text-sm text-[rgb(24,58,63)]">
              <div class="book-section">
                <div class="book-heading">{{ t('payoutDetails.estimatedAvailability') }}</div>
                <div class="mt-1">{{ formatDateDateOnly(payout?.estimated_availability) }}</div>
              </div>

              <div class="book-section">
                <div class="book-heading">{{ t('payoutDetails.paidAt') }}</div>
                <div class="mt-1">{{ formatDate(payout?.paid_at) }}</div>
              </div>
              <div class="book-section">
                <div class="book-heading">{{ t('payoutDetails.refundedAt') }}</div>
                <div class="mt-1">{{ formatDate(payout?.refund_at) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
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

const props = defineProps({ payout: Object })
const emit = defineEmits(['close'])

// use computed refs so changes to props.payout update the UI
const payout = computed(() => props.payout || {})

const visible = computed(() => Boolean(payout.value && Object.keys(payout.value).length > 0))

const patient = computed(() => payout.value?.appointment?.patient ?? null)

function payoutBadge(status) {
  const s = String(status || '').trim().toLowerCase()
  if (s === 'pending') return 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200'
  if (s === 'paid') return 'bg-green-50 text-green-700 ring-1 ring-green-200'
  if (s === 'on_hold') return 'bg-red-50 text-red-700 ring-1 ring-red-200'
  if (s === 'refund') return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}

function payoutStatusLabel(status) {
  const s = String(status || '').trim().toLowerCase()
  if (!s) return '—'
  if (s === 'pending') return t('payoutDetails.statusPending')
  if (s === 'paid') return t('payoutDetails.statusPaid')
  if (s === 'on_hold') return t('payoutDetails.statusOnHold')
  if (s === 'refund') return t('payoutDetails.statusRefund')
  return s.charAt(0).toUpperCase() + s.slice(1)
}

function formatDate(d) {
  if (!d) return '—'
  try {
    const localeMap = { ar: 'ar', fr: 'fr', en: 'en' }
    const currentLocale = localeMap[locale.value] || 'en'
    return new Intl.DateTimeFormat(currentLocale, { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' }).format(new Date(d))
  } catch { return String(d) }
}

function formatDateDateOnly(d) {
  if (!d) return '—'
  try {
    const localeMap = { ar: 'ar', fr: 'fr', en: 'en' }
    const currentLocale = localeMap[locale.value] || 'en'
    return new Intl.DateTimeFormat(currentLocale, { year: 'numeric', month: 'short', day: '2-digit' }).format(new Date(d))
  } catch { return String(d) }
}
</script>

<style scoped>
/* reuse styling from NotesBook */
.styled-scrollbar { scrollbar-width: thin; scrollbar-color: rgb(89 151 172 / var(--tw-bg-opacity, 1)) rgba(229, 231, 235, 1); }
.styled-scrollbar::-webkit-scrollbar { width: 10px; height: 10px; }
.styled-scrollbar::-webkit-scrollbar-track { background: rgba(241, 245, 249, 1); border-radius: 9999px; }
.styled-scrollbar::-webkit-scrollbar-thumb { background: rgb(89 151 172 / var(--tw-bg-opacity, 1)); border-radius: 9999px; border: 2px solid #ffffff; }
.book-page { min-height: 300px; }
.book-heading { font-weight: 600; color: rgb(89,151,172); margin-bottom: 6px; }
.book-section { background: transparent; padding: 6px 0; border-bottom: 1px dashed rgba(15,23,42,0.04); }

[dir="rtl"] {
  text-align: right;
}
</style>
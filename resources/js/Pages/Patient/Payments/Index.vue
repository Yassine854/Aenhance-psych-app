<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed, ref, watch, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import Swal from 'sweetalert2'
import Navbar from '@/Components/Navbar.vue'
import Footer from '@/Components/Footer.vue'

const props = defineProps({
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
  authUser: { type: Object },
  status: { type: String, default: '' },
  payments: { type: Array, default: () => [] },
})

const page = usePage()
const { t } = useI18n()
const flashStatus = computed(() => props.status || page.props?.flash?.status || '')

const pageSize = ref(5)
const currentPage = ref(1)
const paymentsTop = ref(null)

const paymentsList = computed(() => props.payments || [])
const totalPages = computed(() => Math.max(1, Math.ceil(paymentsList.value.length / pageSize.value)))

const paginatedPayments = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return paymentsList.value.slice(start, start + pageSize.value)
})

watch(() => props.payments, () => {
  currentPage.value = 1
})

watch(currentPage, async () => {
  await nextTick()
  try {
    const el = paymentsTop.value
    if (el && typeof el.scrollIntoView === 'function') {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' })
    } else {
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  } catch {
    // ignore
  }
})

const pagesToShow = computed(() => {
  const total = totalPages.value
  const current = currentPage.value
  const out = []
  if (total <= 7) {
    for (let i = 1; i <= total; i++) out.push(i)
    return out
  }
  out.push(1)
  const left = Math.max(2, current - 1)
  const right = Math.min(total - 1, current + 1)
  if (left > 2) out.push('left-ellipsis')
  for (let i = left; i <= right; i++) out.push(i)
  if (right < total - 1) out.push('right-ellipsis')
  out.push(total)
  return out
})

const toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 2200,
  timerProgressBar: true,
})

const statusToastShown = ref(false)
watch(
  flashStatus,
  (status) => {
    if (statusToastShown.value || !status) return
    statusToastShown.value = true
    toast.fire({ icon: 'success', title: String(status) })
  },
  { immediate: true }
)

function formatDateTime(value) {
  if (!value) return ''
  try {
    const dt = new Date(value)
    return new Intl.DateTimeFormat(undefined, {
      weekday: 'short',
      year: 'numeric',
      month: 'short',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    }).format(dt)
  } catch {
    return String(value)
  }
}

function formatMoney(amount, currency) {
  if (amount == null) return '—'
  const normalizedCurrency = String(currency || 'TND')
  const numericAmount = Number(amount)
  if (!Number.isFinite(numericAmount)) return `${amount} ${normalizedCurrency}`
  return `${numericAmount.toFixed(2)} ${normalizedCurrency}`
}

function paymentStatusBadgeClass(status) {
  const s = String(status || '').toLowerCase()
  if (s === 'paid') return 'bg-green-50 text-green-700 ring-1 ring-green-200'
  if (s === 'pending') return 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200'
  if (s === 'failed') return 'bg-red-50 text-red-700 ring-1 ring-red-200'
  if (s === 'refunded') return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}

function paymentStatusLabel(status) {
  const s = String(status || '').toLowerCase()
  if (!s) return '—'
  if (s === 'paid') return t('payments.paid')
  if (s === 'pending') return t('payments.pending')
  if (s === 'failed') return t('payments.failed')
  if (s === 'refunded') return t('payments.refunded')
  return s.charAt(0).toUpperCase() + s.slice(1)
}
</script>

<template>
  <Head :title="`${t('payments.title')} - AEnhance`" />

  <Navbar :canLogin="canLogin" :canRegister="canRegister" :authUser="authUser" />

  <div class="bg-gray-50 py-10 md:py-14">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 md:p-7 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ t('payments.title') }}</h1>
            <p class="mt-2 text-gray-700 max-w-3xl">{{ t('payments.subtitle') }}</p>
          </div>

          <Link
            :href="route('patient.appointments')"
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300"
          >
            {{ t('payments.goToAppointments') }}
          </Link>
        </div>

        <div class="mt-5 grid grid-cols-1 sm:grid-cols-4 gap-3">
          <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
            <div class="text-xs font-semibold text-gray-500">{{ t('payments.totalPayments') }}</div>
            <div class="mt-1 text-lg font-bold text-gray-900">{{ paymentsList.length }}</div>
          </div>
          <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3">
            <div class="text-xs font-semibold text-green-700">{{ t('payments.paid') }}</div>
            <div class="mt-1 text-lg font-bold text-green-900">
              {{ paymentsList.filter((p) => String(p?.status || '').toLowerCase() === 'paid').length }}
            </div>
          </div>
          <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3">
            <div class="text-xs font-semibold text-yellow-700">{{ t('payments.pending') }}</div>
            <div class="mt-1 text-lg font-bold text-yellow-900">
              {{ paymentsList.filter((p) => String(p?.status || '').toLowerCase() === 'pending').length }}
            </div>
          </div>
          <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3">
            <div class="text-xs font-semibold text-red-700">{{ t('payments.failed') }}</div>
            <div class="mt-1 text-lg font-bold text-red-900">
              {{ paymentsList.filter((p) => String(p?.status || '').toLowerCase() === 'failed').length }}
            </div>
          </div>
        </div>
      </div>

      <div v-if="!paymentsList.length" class="bg-white border border-gray-200 rounded-2xl shadow-sm p-8 text-gray-700">
      <div class="text-lg font-semibold text-gray-900">{{ t('payments.noPaymentsTitle') }}</div>
      <div class="mt-1 text-sm text-gray-600">{{ t('payments.noPaymentsDesc') }}</div>
        <div class="mt-6">
            <Link
            :href="route('services.consultation')"
            class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-[#5997ac] text-white text-sm font-semibold hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#5997ac]/30"
          >
            {{ t('payments.bookSession') }}
          </Link>
        </div>
      </div>

      <div v-else class="space-y-4" ref="paymentsTop">
        <TransitionGroup name="list" tag="div" class="space-y-4">
          <div
            v-for="p in paginatedPayments"
            :key="p.id"
            class="group relative bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden transition hover:shadow-md"
          >
            <div class="p-5 md:p-6">
              <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div class="min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <div class="text-lg font-semibold text-gray-900 truncate">
                      {{ p.appointment?.psychologist_name || t('payments.psychologist') }}
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold" :class="paymentStatusBadgeClass(p.status)">
                      {{ paymentStatusLabel(p.status) }}
                    </span>
                  </div>
                </div>

                <div class="text-right">
                  <div class="text-xs font-semibold text-gray-500">{{ t('payments.amount') }}</div>
                  <div class="mt-1 text-lg font-bold text-gray-900">{{ formatMoney(p.amount, p.currency) }}</div>
                </div>
              </div>

              <div class="mt-3 rounded-2xl border border-gray-200 bg-gray-50 -ml-1 -mr-3 md:-ml-2 md:-mr-4 px-4 md:px-5 py-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                  <div>
                    <div class="text-xs font-semibold text-gray-500">{{ t('payments.appointmentDate') }}</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">{{ formatDateTime(p.appointment?.scheduled_start) || '—' }}</div>
                  </div>

                  <div>
                    <div class="text-xs font-semibold text-gray-500">{{ t('payments.paidAt') }}</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900">{{ formatDateTime(p.paid_at) || '—' }}</div>
                  </div>

                  <div>
                    <div class="text-xs font-semibold text-gray-500">{{ t('payments.provider') }}</div>
                    <div class="mt-1 text-sm font-semibold text-gray-900 uppercase">{{ p.provider || '—' }}</div>
                  </div>

                  <div>
                    <div class="text-xs font-semibold text-gray-500">{{ t('payments.transaction') }}</div>
                    <div class="mt-1">
                      <span v-if="p.transaction_id" class="inline-flex items-center rounded-md bg-white px-2 py-0.5 font-mono text-[11px] text-gray-700 ring-1 ring-gray-200">{{ p.transaction_id }}</span>
                      <span v-else class="text-sm font-semibold text-gray-900">—</span>
                    </div>
                  </div>
                </div>

                <div v-if="p.failure_reason || p.refund_reason" class="mt-4 border-t border-gray-200 pt-3 text-xs text-gray-600">
                  <div v-if="p.failure_reason">{{ t('payments.failureReason') }}: {{ p.failure_reason }}</div>
                  <div v-if="p.refund_reason" class="mt-1">{{ t('payments.refundReason') }}: {{ p.refund_reason }}</div>
                </div>
              </div>
            </div>
          </div>
        </TransitionGroup>

          <div v-if="paymentsList.length > pageSize" class="mt-6 flex items-center justify-between">
          <div class="text-sm text-gray-600">{{ t('payments.showing', { start: (currentPage - 1) * pageSize + 1, end: Math.min(currentPage * pageSize, paymentsList.length), total: paymentsList.length }) }}</div>
          <div class="inline-flex items-center gap-2">
              <button
              @click="currentPage = Math.max(1, currentPage - 1)"
              :disabled="currentPage === 1"
              class="px-3 py-1 rounded-full bg-white border shadow-sm text-sm disabled:opacity-50"
              :aria-label="t('payments.previousPage')"
            >
              ‹
            </button>

            <div class="inline-flex items-center gap-2">
              <template v-for="item in pagesToShow" :key="String(item)">
                <button
                  v-if="typeof item === 'number'"
                  @click="currentPage = item"
                  :aria-current="currentPage === item ? 'page' : null"
                  :class="[
                    'px-3 py-1 rounded-full text-sm shadow-sm border transition-all',
                    currentPage === item ? 'text-white' : 'text-gray-700 bg-white'
                  ]"
                  :style="currentPage === item ? { backgroundColor: 'rgb(89 151 172 / var(--tw-bg-opacity, 1))', boxShadow: '0 6px 18px rgba(89,151,172,0.18)', transform: 'translateY(-1px)' } : {}"
                >
                  {{ item }}
                </button>

                <span v-else class="text-gray-400 px-2">…</span>
              </template>
            </div>

            <button
              @click="currentPage = Math.min(totalPages, currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 rounded-full bg-white border shadow-sm text-sm disabled:opacity-50"
              :aria-label="t('payments.nextPage')"
            >
              ›
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <Footer />
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: opacity 180ms ease, transform 180ms ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>

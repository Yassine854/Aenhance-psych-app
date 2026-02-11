<template>
  <div v-if="show && payment" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="close"></div>

    <div class="relative w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-start justify-between gap-4">
          <div class="text-white">
            <div class="text-xl font-semibold leading-tight">Payment #{{ payment.id }}</div>
            <div class="text-sm opacity-90">{{ formatDate(payment.created_at) }}</div>
          </div>
          <button @click="close" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
        </div>

        <div class="mt-4 flex items-center gap-2 flex-wrap">
          <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium" :class="paymentBadge(payment.status)">
            {{ paymentLabel(payment.status) }}
          </span>
        </div>
      </div>

      <div class="bg-white p-6 max-h-[80vh] overflow-y-auto styled-scrollbar">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 p-4">
              <div class="text-sm font-semibold text-gray-900">Participants</div>
              <dl class="mt-3 space-y-3">
                <div class="flex items-start justify-between gap-4">
                  <dt class="text-xs font-medium text-gray-500">Patient</dt>
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ payment.patient?.name || payment.appointment?.patient?.name || '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                  <dt class="text-xs font-medium text-gray-500">Psychologist</dt>
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ payment.psychologist?.name || payment.appointment?.psychologist?.name || '—' }}</dd>
                </div>
              </dl>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
              <div class="text-sm font-semibold text-gray-900">Details</div>
              <dl class="mt-3 space-y-3">
                <div class="flex items-start justify-between gap-4">
                  <dt class="text-xs font-medium text-gray-500">Amount</dt>
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ Number(payment.amount).toFixed(2) }} {{ payment.currency || 'TND' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                  <dt class="text-xs font-medium text-gray-500">Provider</dt>
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ payment.provider || '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                  <dt class="text-xs font-medium text-gray-500">Transaction</dt>
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ payment.transaction_id || '—' }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-gray-200 p-4">
              <div class="flex items-center justify-between gap-3 flex-wrap">
                <div>
                  <div class="text-sm font-semibold text-gray-900">Payment Info</div>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium" :class="paymentBadge(payment.status)">
                  {{ paymentLabel(payment.status) }}
                </span>
              </div>

              <div class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                  <div class="text-xs text-gray-500">Provider</div>
                  <div class="text-sm text-gray-900">{{ payment.provider || '—' }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500">Amount</div>
                  <div class="text-sm text-gray-900">{{ Number(payment.amount).toFixed(2) }} {{ payment.currency || 'TND' }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500">Paid at</div>
                  <div class="text-sm text-gray-900">{{ formatDateTime(payment.paid_at) }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500">Payment ID</div>
                  <div class="text-sm text-gray-900">{{ payment.id || '—' }}</div>
                </div>
              </div>
            </div>
            <div v-if="String(payment.status || '').toLowerCase() === 'refunded'" class="rounded-xl border border-gray-200 p-4">
              <div class="text-sm font-semibold text-gray-900">Refund</div>
              <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <div class="text-xs text-gray-500">Reason</div>
                  <div class="text-sm text-gray-900">{{ payment.refund_reason || '—' }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500">Refunded at</div>
                  <div class="text-sm text-gray-900">{{ formatDateTime(payment.updated_at) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ show: Boolean, payment: Object })
const emit = defineEmits(['close'])

const payment = computed(() => props.payment || null)

function close(){ emit('close') }

function formatDate(value) {
  if (!value) return '—'
  try { return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: '2-digit' }).format(new Date(value)) } catch { return String(value) }
}

function formatDateTime(value) {
  if (!value) return '—'
  try { return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' }).format(new Date(value)) } catch { return String(value) }
}

function paymentLabel(status) {
  const s = String(status || '').toLowerCase()
  if (!s) return '—'
  if (s === 'paid') return 'Paid'
  if (s === 'pending') return 'Pending'
  if (s === 'failed') return 'Failed'
  if (s === 'refunded') return 'Refunded'
  return s
}

function paymentBadge(status) {
  const s = String(status || '').toLowerCase()
  if (!s) return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
  if (s === 'paid') return 'bg-green-50 text-green-700 ring-1 ring-green-200'
  if (s === 'pending') return 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200'
  if (s === 'failed') return 'bg-red-50 text-red-700 ring-1 ring-red-200'
  if (s === 'refunded') return 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}

</script>

<style scoped></style>

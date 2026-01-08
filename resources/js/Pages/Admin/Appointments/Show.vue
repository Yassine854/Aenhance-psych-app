<template>
  <div v-if="show && appointment" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="$emit('close')"></div>

    <div class="relative w-full max-w-6xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-start justify-between gap-4">
          <div class="text-white">
            <div class="text-xl font-semibold leading-tight">Appointment #{{ appointment.id }}</div>
            <div class="text-sm opacity-90">
              {{ formatDate(appointment.scheduled_start) }} · {{ formatTime(appointment.scheduled_start) }} – {{ formatTime(appointment.scheduled_end) }}
            </div>
          </div>
          <button @click="$emit('close')" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
        </div>

        <div class="mt-4 flex items-center gap-2 flex-wrap">
          <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium" :class="appointmentBadge(appointment.status)">
            {{ appointmentLabel(appointment.status) }}
          </span>

          <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium" :class="paymentBadge(appointment.payment?.status)">
            {{ paymentLabel(appointment.payment?.status) }}
          </span>

          <span
            v-if="showPrice"
            class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-white/15 text-white ring-1 ring-white/25"
          >
            {{ Number(appointment.price).toFixed(2) }} {{ appointment.currency || 'TND' }}
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
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ appointment.patient?.name || '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                  <dt class="text-xs font-medium text-gray-500">Psychologist</dt>
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ appointment.psychologist?.name || '—' }}</dd>
                </div>
              </dl>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
              <div class="text-sm font-semibold text-gray-900">Schedule</div>
              <dl class="mt-3 space-y-3">
                <div class="flex items-start justify-between gap-4">
                  <dt class="text-xs font-medium text-gray-500">Start</dt>
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ formatDateTime(appointment.scheduled_start) }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                  <dt class="text-xs font-medium text-gray-500">End</dt>
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ formatDateTime(appointment.scheduled_end) }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-gray-200 p-4">
              <div class="flex items-center justify-between gap-3 flex-wrap">
                <div>
                  <div class="text-sm font-semibold text-gray-900">Payment (latest)</div>
                  <div class="text-xs text-gray-500">Only the latest payment is shown.</div>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium" :class="paymentBadge(appointment.payment?.status)">
                  {{ paymentLabel(appointment.payment?.status) }}
                </span>
              </div>

              <div class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                  <div class="text-xs text-gray-500">Provider</div>
                  <div class="text-sm text-gray-900">{{ appointment.payment?.provider || '—' }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500">Amount</div>
                  <div class="text-sm text-gray-900">
                    <span v-if="appointment.payment?.amount != null">{{ Number(appointment.payment.amount).toFixed(2) }} {{ appointment.payment.currency || appointment.currency || 'TND' }}</span>
                    <span v-else>—</span>
                  </div>
                </div>
                <div>
                  <div class="text-xs text-gray-500">Paid at</div>
                  <div class="text-sm text-gray-900">{{ formatDateTime(appointment.payment?.paid_at) }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500">Payment ID</div>
                  <div class="text-sm text-gray-900">{{ appointment.payment?.id || '—' }}</div>
                </div>
              </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
              <div class="text-sm font-semibold text-gray-900">Cancellation</div>
              <div class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                  <div class="text-xs text-gray-500">Cancelled by</div>
                  <div class="text-sm text-gray-900">{{ appointment.canceled_by || '—' }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500">Cancelled by user</div>
                  <div class="text-sm text-gray-900">{{ appointment.canceled_by_user_id || '—' }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500">Cancelled at</div>
                  <div class="text-sm text-gray-900">{{ formatDateTime(appointment.canceled_at) }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500">Reason</div>
                  <div class="text-sm text-gray-900">{{ appointment.cancellation_reason || '—' }}</div>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3">
              <button type="button" @click="$emit('close')" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Close</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  show: Boolean,
  appointment: Object,
})

defineEmits(['close'])

const appointment = computed(() => props.appointment || null)

function normalizeStatus(value) {
  return String(value || '').trim().toLowerCase()
}

const showPrice = computed(() => {
  const s = normalizeStatus(appointment.value?.status)
  return appointment.value?.price != null && !['cancelled', 'no_show'].includes(s)
})

function appointmentLabel(status) {
  const s = normalizeStatus(status)
  if (!s) return '—'
  if (s === 'no_show') return 'missed'
  return s.charAt(0).toUpperCase() + s.slice(1)
}

function appointmentBadge(status) {
  const s = normalizeStatus(status)
  if (s === 'pending') return 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200'
  if (s === 'confirmed') return 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'
  if (s === 'completed') return 'bg-green-50 text-green-700 ring-1 ring-green-200'
  if (s === 'cancelled') return 'bg-red-50 text-red-700 ring-1 ring-red-200'
  if (s === 'no_show') return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}

function paymentLabel(status) {
  const s = normalizeStatus(status)
  if (!s) return 'No payment'
  if (s === 'paid') return 'Paid'
  if (s === 'pending') return 'Pending'
  if (s === 'failed') return 'Failed'
  if (s === 'refunded') return 'Refunded'
  return s
}

function paymentBadge(status) {
  const s = normalizeStatus(status)
  if (!s) return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
  if (s === 'paid') return 'bg-green-50 text-green-700 ring-1 ring-green-200'
  if (s === 'pending') return 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200'
  if (s === 'failed') return 'bg-red-50 text-red-700 ring-1 ring-red-200'
  if (s === 'refunded') return 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}

function formatDate(value) {
  if (!value) return '—'
  try {
    return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: '2-digit' }).format(new Date(value))
  } catch {
    return String(value)
  }
}

function formatTime(value) {
  if (!value) return '—'
  try {
    return new Intl.DateTimeFormat(undefined, { hour: '2-digit', minute: '2-digit' }).format(new Date(value))
  } catch {
    return '—'
  }
}

function formatDateTime(value) {
  if (!value) return '—'
  try {
    return new Intl.DateTimeFormat(undefined, {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    }).format(new Date(value))
  } catch {
    return String(value)
  }
}
</script>


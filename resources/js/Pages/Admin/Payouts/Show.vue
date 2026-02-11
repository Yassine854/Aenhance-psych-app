<template>
  <div v-if="show && payout" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="close"></div>
    <div class="relative w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-start justify-between gap-4">
          <div class="text-white">
            <div class="text-xl font-semibold">Payout #{{ payout.id }}</div>
            <div class="text-sm opacity-90">
              <template v-if="payout && String(payout.status || '').toLowerCase() === 'paid'">
                Paid at {{ formatDate(payout.paid_at) }}
              </template>
              <template v-else>
                Updated {{ formatDate(payout.updated_at) }}
              </template>
            </div>
          </div>
          <button @click="close" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
        </div>
      </div>

      <div class="bg-white p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <div class="text-sm font-semibold text-gray-900">Psychologist</div>
            <div class="mt-2 text-sm text-gray-700">{{ payout.psychologist?.name || payout.psychologist?.email || '—' }}</div>
          </div>
          <div>
            <div class="text-sm font-semibold text-gray-900">Patient</div>
            <div class="mt-2 text-sm text-gray-700">{{ payout.patient?.name || '—' }}</div>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <div class="text-xs text-gray-500">Gross</div>
            <div class="text-sm text-gray-900">{{ Number(payout.gross_amount).toFixed(2) }} {{ payout.currency }}</div>
          </div>
          <div>
            <div class="text-xs text-gray-500">Platform Fee</div>
            <div class="text-sm text-gray-900">{{ Number(payout.platform_fee).toFixed(2) }} {{ payout.currency }}</div>
          </div>
          <div>
            <div class="text-xs text-gray-500">Net</div>
            <div class="text-sm text-gray-900">{{ Number(payout.net_amount).toFixed(2) }} {{ payout.currency }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({ show: Boolean, payout: Object })
const emit = defineEmits(['close'])

const payout = computed(() => props.payout || null)
function close(){ emit('close') }

function formatDate(value) {
  if (!value) return '—'
  try { return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: '2-digit' }).format(new Date(value)) } catch { return String(value) }
}
</script>

<style scoped></style>

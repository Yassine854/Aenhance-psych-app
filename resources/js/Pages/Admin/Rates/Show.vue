<template>
  <div v-if="show && rating" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="close"></div>

    <div class="relative w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-start justify-between gap-4">
          <div class="text-white">
            <div class="text-xl font-semibold leading-tight">Rating #{{ rating.id }}</div>
            <div class="text-sm opacity-90">{{ formatDate(rating.created_at) }}</div>
          </div>
          <div class="flex items-center gap-3">
            <button @click="close" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 max-h-[80vh] overflow-y-auto styled-scrollbar">
        <div class="grid grid-cols-1 gap-6">
          <div class="rounded-xl border border-gray-200 p-4">
            <div class="text-sm font-semibold text-gray-900">Participants</div>
            <dl class="mt-3 space-y-3">
              <div class="flex items-start justify-between gap-4">
                <dt class="text-xs font-medium text-gray-500">Patient</dt>
                <dd class="text-sm font-medium text-gray-900 text-right">{{ rating.patient?.name || '—' }}</dd>
              </div>
              <div class="flex items-start justify-between gap-4">
                <dt class="text-xs font-medium text-gray-500">Psychologist</dt>
                <dd class="text-sm font-medium text-gray-900 text-right">{{ rating.psychologist?.name || '—' }}</dd>
              </div>
            </dl>
          </div>

          <div class="rounded-xl border border-gray-200 p-4">
            <div class="text-sm font-semibold text-gray-900">Details</div>
            <dl class="mt-3 space-y-3">
              <div class="flex items-start justify-between gap-4">
                <dt class="text-xs font-medium text-gray-500">Rating</dt>
                <dd class="text-sm font-medium text-gray-900 text-right">{{ rating.rating }}</dd>
              </div>
              <div class="flex items-start justify-between gap-4">
                <dt class="text-xs font-medium text-gray-500">Comment</dt>
                <dd class="text-sm font-medium text-gray-900 text-right">{{ rating.comment || '—' }}</dd>
              </div>
              <div v-if="rating.session" class="flex items-start justify-between gap-4">
                <dt class="text-xs font-medium text-gray-500">Session</dt>
                <dd class="text-sm font-medium text-gray-900 text-right">#{{ rating.session.id }}</dd>
              </div>
              <div v-if="rating.appointment" class="flex items-start justify-between gap-4">
                <dt class="text-xs font-medium text-gray-500">Appointment</dt>
                <dd class="text-sm font-medium text-gray-900 text-right">#{{ rating.appointment.id }} — {{ rating.appointment.status || '—' }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ show: Boolean, rating: Object })
const emit = defineEmits(['close'])

const rating = computed(() => props.rating || null)

function close(){ emit('close') }

function formatDate(value) {
  if (!value) return '—'
  try { return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: '2-digit' }).format(new Date(value)) } catch { return String(value) }
}
</script>

<style scoped></style>

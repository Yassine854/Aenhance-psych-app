<template>
  <div v-if="show && log" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="$emit('close')"></div>
    <div class="relative w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-start justify-between gap-4">
          <div class="text-white">
            <div class="text-xl font-semibold leading-tight">Log #{{ log.id }}</div>
            <div class="text-sm opacity-90">{{ formatDate(log.created_at) }}</div>
          </div>
          <button @click="$emit('close')" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
        </div>
      </div>

      <div class="bg-white p-6 max-h-[80vh] overflow-y-auto styled-scrollbar">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 p-4">
              <div class="text-sm font-semibold text-gray-900">Details</div>
              <dl class="mt-3 space-y-3">
                <div class="flex items-start justify-between gap-4">
                  <dt class="text-xs font-medium text-gray-500">Target</dt>
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ log.target_type }} #{{ log.target_id }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                  <dt class="text-xs font-medium text-gray-500">Action</dt>
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ log.action }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                  <dt class="text-xs font-medium text-gray-500">Actor Role</dt>
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ log.actor_role || 'SYSTEM' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                  <dt class="text-xs font-medium text-gray-500">Target</dt>
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ log.patient?.user?.name || (log.target_type ? (log.target_type + ' #' + log.target_id) : '—') }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-gray-200 p-4">
              <div class="text-sm font-semibold text-gray-900">Description</div>
              <div class="mt-3 text-sm text-gray-700">{{ log.description || '—' }}</div>
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
const props = defineProps({ show: Boolean, log: Object })

function formatDate(value) {
  if (!value) return '—'
  try { const d = new Date(value); if (Number.isNaN(d.getTime())) return String(value); return d.toLocaleString() } catch { return String(value) }
}
</script>

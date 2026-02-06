<template>
  <div v-if="show && log" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="$emit('close')"></div>
    <div class="relative w-full max-w-6xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-start justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="text-white">
              <div class="text-xl font-semibold leading-tight">Log #{{ log.id }}</div>
              <div class="text-sm opacity-90">{{ formatDate(log.created_at) }}</div>
            </div>
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
                      <dt class="text-xs font-medium text-gray-500">Actor ID</dt>
                      <dd class="text-sm font-medium text-gray-900 text-right">{{ log.actor_id || 'SYSTEM' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                      <dt class="text-xs font-medium text-gray-500">Patient</dt>
                      <dd class="text-sm font-medium text-gray-900 text-right">
                        <span v-if="loadingRelated && !appointment" class="text-gray-500">Loading…</span>
                        <span v-else>{{ appointment?.patient || '-' }}</span>
                      </dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                      <dt class="text-xs font-medium text-gray-500">Psychologist</dt>
                      <dd class="text-sm font-medium text-gray-900 text-right">
                        <span v-if="loadingRelated && !appointment" class="text-gray-500">Loading…</span>
                        <span v-else>{{ appointment?.psychologist || '-' }}</span>
                      </dd>
                    </div>
                    <div class="flex items-start justify-between gap-4">
                      <dt class="text-xs font-medium text-gray-500">Schedule</dt>
                      <dd class="text-sm font-medium text-gray-900 text-right">
                        <span v-if="loadingRelated && !appointment" class="text-gray-500">Loading…</span>
                        <span v-else>{{ formatDate(appointment?.scheduled_start) }}</span>
                      </dd>
                    </div>
                    
                  </dl>
                </div>
              </div>

              <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl border border-gray-200 p-4">
                  <div class="text-sm font-semibold text-gray-900">Description</div>
                  <div class="mt-3 text-sm text-gray-700">{{ log.description || '—' }}</div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                  <div class="text-sm font-semibold text-gray-900">Earlier Logs for Appointment</div>
                    <div class="mt-3 text-sm text-gray-700">
                    <div v-if="loadingRelated" class="text-gray-500">Loading…</div>
                    <div v-else>
                      <div v-if="related.length === 0" class="text-gray-500">No earlier logs for this appointment.</div>
                      <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-auto">
                        <thead class="bg-gray-50">
                          <tr>
                            <th class="px-3 py-2 text-left text-xs text-gray-500">When</th>
                            <th class="px-3 py-2 text-left text-xs text-gray-500">Action</th>
                            <th class="px-3 py-2 text-left text-xs text-gray-500">Actor Role</th>
                            <th class="px-3 py-2 text-left text-xs text-gray-500">Status</th>
                            <th class="px-3 py-2 text-left text-xs text-gray-500">Description</th>
                          </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                          <tr v-for="r in related" :key="r.id">
                            <td class="px-3 py-2 text-sm text-gray-700">{{ formatDate(r.created_at) }}</td>
                            <td class="px-3 py-2 text-sm text-gray-700">{{ r.action }}</td>
                            <td class="px-3 py-2 text-sm text-gray-700">{{ r.actor_role || 'SYSTEM' }}</td>
                            <td class="px-3 py-2 text-sm text-gray-700">
                              <span v-if="r.status" :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium', statusBadgeClass(r.status)]">
                                {{ r.status }}
                              </span>
                              <span v-else class="text-gray-500">-</span>
                            </td>
                            <td class="px-3 py-2 text-sm text-gray-700">{{ r.description || '-' }}</td>
                          </tr>
                        </tbody>
                        </table>
                      </div>
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
import { ref, watch, toRefs } from 'vue'

const props = defineProps({ show: Boolean, log: Object })
const { show, log } = toRefs(props)

const related = ref([])
const loadingRelated = ref(false)
const appointment = ref(null)

watch([show, log], async ([showVal, logVal]) => {
  if (!showVal || !logVal) return
  // reset appointment/related to avoid showing embedded/full appointment data
  appointment.value = null

  // fetch earlier related logs
  loadingRelated.value = true
  related.value = []
  try {
    const res = await fetch(`/admin/logs/appointments/${logVal.id}/related`, { headers: { 'Accept': 'application/json' } })
      if (res.ok) {
      const payload = await res.json()
      // if controller returned appointment, prefer it
      if (payload.appointment) appointment.value = payload.appointment
      // filter out transient payment start logs
      const all = payload.logs || []
      related.value = all.filter(r => !/\bstarted[_\-\s]?payment\b/i.test(String(r.action || '')))
    } else {
      related.value = []
    }
  } catch (e) {
    related.value = []
  } finally {
    loadingRelated.value = false
  }
})

function formatDate(value) {
  if (!value) return '—'
  try {
    const d = new Date(value)
    if (Number.isNaN(d.getTime())) return String(value)
    return d.toLocaleString()
  } catch {
    return String(value)
  }
}

function statusBadgeClass(status) {
  if (!status) return 'bg-gray-50 text-gray-700'
  const s = String(status).toLowerCase()
  switch (s) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'confirmed':
      return 'bg-blue-100 text-blue-800'
    case 'completed':
      return 'bg-green-100 text-green-800'
    case 'cancelled':
    case 'canceled':
      return 'bg-red-100 text-red-800'
    case 'no_show':
    case 'no-show':
    case 'no show':
      return 'bg-gray-100 text-gray-800'
    default:
      return 'bg-gray-50 text-gray-700'
  }
}
</script>

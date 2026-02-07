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
                      <dd class="text-sm font-medium text-gray-900 text-right">{{ getPersonName(session?.patient) }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-4">
                      <dt class="text-xs font-medium text-gray-500">Psychologist</dt>
                      <dd class="text-sm font-medium text-gray-900 text-right">{{ getPersonName(session?.psychologist) }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-4">
                      <dt class="text-xs font-medium text-gray-500">Appointment</dt>
                      <dd class="text-sm font-medium text-gray-900 text-right">{{ session?.appointment_id ? ('#' + session.appointment_id) : (sessionLoading ? 'Loading…' : '-') }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-4">
                      <dt class="text-xs font-medium text-gray-500">Room</dt>
                      <dd class="text-sm font-medium text-gray-900 text-right">{{ session?.room_id ?? (sessionLoading ? 'Loading…' : '-') }}</dd>
                    </div>

                    <div class="flex items-start justify-between gap-4">
                      <dt class="text-xs font-medium text-gray-500">Status</dt>
                      <dd class="text-sm font-medium text-gray-900 text-right">
                        <span v-if="session?.status" :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium', statusBadgeClass(session.status)]">{{ session.status }}</span>
                        <span v-else class="text-gray-500">-</span>
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
                  <div class="text-sm font-semibold text-gray-900">Session Details</div>
                    <div class="mt-3 text-sm text-gray-700">
                      <div v-if="sessionLoading" class="text-gray-500">Loading…</div>
                      <div v-else>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                          <div>
                            <div class="text-xs text-gray-500">Started At</div>
                            <div class="text-sm text-gray-800">{{ formatDate(session?.started_at) }}</div>
                          </div>
                          <div>
                            <div class="text-xs text-gray-500">Ended At</div>
                            <div class="text-sm text-gray-800">{{ formatDate(session?.ended_at) }}</div>
                          </div>
                          <div>
                            <div class="text-xs text-gray-500">Patient Joined</div>
                            <div class="text-sm text-gray-800">{{ formatDate(session?.patient_joined_at) }}</div>
                          </div>
                          <div>
                            <div class="text-xs text-gray-500">Psychologist Joined</div>
                            <div class="text-sm text-gray-800">{{ formatDate(session?.psychologist_joined_at) }}</div>
                          </div>
                          <div>
                            <div class="text-xs text-gray-500">Patient Left</div>
                            <div class="text-sm text-gray-800">{{ formatDate(session?.patient_left_at) }}</div>
                          </div>
                          <div>
                            <div class="text-xs text-gray-500">Psychologist Left</div>
                            <div class="text-sm text-gray-800">{{ formatDate(session?.psychologist_left_at) }}</div>
                          </div>
                          <div>
                            <div class="text-xs text-gray-500">Duration (min)</div>
                            <div class="text-sm text-gray-800">{{ session?.duration_minutes ?? '-' }}</div>
                          </div>
                          <div>
                            <div class="text-xs text-gray-500">Patient In Room</div>
                            <div class="text-sm text-gray-800">{{ typeof session?.patient_in_room === 'boolean' ? (session.patient_in_room ? 'Yes' : 'No') : '-' }}</div>
                          </div>
                          <div>
                            <div class="text-xs text-gray-500">Psychologist In Room</div>
                            <div class="text-sm text-gray-800">{{ typeof session?.psychologist_in_room === 'boolean' ? (session.psychologist_in_room ? 'Yes' : 'No') : '-' }}</div>
                          </div>
                        </div>
                      </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4">
                  <div class="text-sm font-semibold text-gray-900">Earlier Logs for Session</div>
                    <div class="mt-3 text-sm text-gray-700">
                    <div v-if="loadingRelated" class="text-gray-500">Loading…</div>
                    <div v-else>
                      <div v-if="related.length === 0" class="text-gray-500">No earlier logs for this session.</div>
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
                              <span v-if="logStatus(r)" :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium', statusBadgeClass(logStatus(r))]">
                                {{ logStatus(r) }}
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

const props = defineProps({ show: Boolean, log: Object, session: Object })
const { show, log, session: initialSession } = toRefs(props)

const related = ref([])
const loadingRelated = ref(false)
const session = ref(null)
const sessionLoading = ref(false)

watch([show, log], async ([showVal, logVal]) => {
  if (!showVal || !logVal) return
  // try to use initial session prop first
  session.value = null
  if (initialSession && initialSession.value) {
    session.value = initialSession.value
    sessionLoading.value = false
  }

  // if we still lack patient/psychologist info, try to derive from the log's appointment payload
  try {
    const ap = logVal?.appointment || null
    if ((!session.value || !session.value.patient || !session.value.psychologist) && ap) {
      const p = ap.patient || null
      const ph = ap.psychologist || null
      session.value = Object.assign({}, session.value || {}, {
        appointment_id: session.value?.appointment_id ?? (ap.id ?? null),
        room_id: session.value?.room_id ?? null,
        started_at: session.value?.started_at ?? null,
        ended_at: session.value?.ended_at ?? null,
        patient_joined_at: session.value?.patient_joined_at ?? null,
        psychologist_joined_at: session.value?.psychologist_joined_at ?? null,
        patient_left_at: session.value?.patient_left_at ?? null,
        psychologist_left_at: session.value?.psychologist_left_at ?? null,
        duration_minutes: session.value?.duration_minutes ?? null,
        status: session.value?.status ?? null,
        patient: session.value?.patient ?? (p ? {
          id: p.id ?? null,
          name: p.name ?? null,
          email: p.email ?? null,
          profile: p.patient_profile ?? p.patientProfile ?? null,
        } : null),
        psychologist: session.value?.psychologist ?? (ph ? {
          id: ph.id ?? null,
          name: ph.name ?? null,
          email: ph.email ?? null,
          profile: ph.psychologist_profile ?? ph.psychologistProfile ?? null,
        } : null),
      })
      sessionLoading.value = false
    }
  } catch (e) {
    // ignore
  }

  loadingRelated.value = true
  related.value = []
  try {
    const res = await fetch(`/admin/logs/sessions/${logVal.id}/related`, { headers: { 'Accept': 'application/json' } })
    if (res.ok) {
      const payload = await res.json()
      if (payload.session) {
        // merge payload.session with any existing session info (preserve patient/psychologist names)
        const prev = session.value || {}
        const s = payload.session || {}
        const merged = Object.assign({}, prev, s)
        merged.patient = prev.patient || s.patient || null
        merged.psychologist = prev.psychologist || s.psychologist || null
        session.value = merged
      }
      related.value = payload.logs || []
    } else {
      related.value = []
    }
  } catch (e) {
    related.value = []
  } finally {
    loadingRelated.value = false
    sessionLoading.value = false
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

function getPersonName(person) {
  if (!person) return '-'
  if (person.name) return person.name
  const profile = person.profile || null
  if (profile && (profile.first_name || profile.last_name)) {
    return ((profile.first_name || '') + (profile.last_name ? ` ${profile.last_name}` : '')).trim()
  }
  return person.email || '-'
}

function logStatus(log) {
  if (!log) return null
  const action = (log.action || '').toString().toLowerCase()
  if (action === 'created_session_status') return 'active'
  if (action === 'updated_session_status') return 'completed'
  const s = (log.status || '').toString().toLowerCase()
  if (s === 'active' || s === 'completed') return s
  // try to infer from description
  const desc = (log.description || '').toString().toLowerCase()
  if (desc.includes('active') || /in[- ]?room/.test(desc)) return 'active'
  if (desc.includes('completed') || desc.includes('complete')) return 'completed'
  return null
}

function statusBadgeClass(status) {
  if (!status) return 'bg-gray-50 text-gray-700'
  const s = String(status).toLowerCase()
  switch (s) {
    case 'active':
      return 'bg-blue-100 text-blue-800'
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

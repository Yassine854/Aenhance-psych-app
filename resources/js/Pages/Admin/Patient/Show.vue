<template>
  <div v-if="show && patient" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="$emit('close')"></div>
    <div class="relative w-full max-w-6xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-start justify-between gap-4">
          <div class="flex items-center gap-4">
            <img v-if="patient.profile_image_url" :src="patient.profile_image_url" class="h-14 w-14 rounded-full ring-2 ring-white/70 object-cover" />
            <div v-else class="h-14 w-14 rounded-full bg-white/20 flex items-center justify-center text-white text-sm">No</div>
            <div class="text-white">
              <div class="text-xl font-semibold leading-tight">{{ patient.first_name }} {{ patient.last_name }}</div>
              <div class="text-sm opacity-90">Patient #{{ patient.id }}</div>
            </div>
          </div>
          <button @click="$emit('close')" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
        </div>
      </div>

      <div class="bg-white p-6 max-h-[80vh] overflow-y-auto styled-scrollbar">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 p-4">
              <div class="text-sm font-semibold text-gray-900">Account</div>
              <dl class="mt-3 space-y-3">
                <div class="flex items-start justify-between gap-4">
                  <dt class="text-xs font-medium text-gray-500">Email</dt>
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ patient.user?.email || '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                  <dt class="text-xs font-medium text-gray-500">Role</dt>
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ patient.user?.role || '—' }}</dd>
                </div>
              </dl>
            </div>
          </div>

          <div class="lg:col-span-2 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="rounded-xl border border-gray-200 p-4">
                <div class="text-sm font-semibold text-gray-900">Profile details</div>
                <dl class="mt-3 space-y-3">
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">First name</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ patient.first_name || '—' }}</dd>
                  </div>
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">Last name</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ patient.last_name || '—' }}</dd>
                  </div>
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">Gender</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ patient.gender || '—' }}</dd>
                  </div>
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">Date of birth</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ formatDate(patient.date_of_birth) }}</dd>
                  </div>
                </dl>
              </div>

              <div class="rounded-xl border border-gray-200 p-4">
                <div class="text-sm font-semibold text-gray-900">Contact & location</div>
                <dl class="mt-3 space-y-3">
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">Phone</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ formatPhone(patient) }}</dd>
                  </div>
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">Country</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ patient.country || '—' }}</dd>
                  </div>
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">City</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ patient.city || '—' }}</dd>
                  </div>
                </dl>
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
const props = defineProps({
  show: Boolean,
  patient: Object,
})

function formatDate(value) {
  if (!value) return '—'
  try {
    const d = new Date(value)
    if (Number.isNaN(d.getTime())) return String(value)
    return d.toLocaleDateString()
  } catch {
    return String(value)
  }
}

function formatPhone(p) {
  const cc = p?.country_code ? String(p.country_code) : ''
  const ph = p?.phone ? String(p.phone) : ''
  if (!cc && !ph) return '—'
  if (!cc) return ph
  if (!ph) return cc
  return `${cc} ${ph}`
}
</script>

<template>
  <div v-if="show && psychologist" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="$emit('close')"></div>
    <div class="relative w-full max-w-6xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <!-- Gradient header -->
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-start justify-between gap-4">
          <div class="flex items-center gap-4">
            <img v-if="psychologist.profile_image_url" :src="psychologist.profile_image_url" class="h-14 w-14 rounded-full ring-2 ring-white/70 object-cover" />
            <div v-else class="h-14 w-14 rounded-full bg-white/20 flex items-center justify-center text-white text-sm">No</div>
            <div class="text-white">
              <div class="text-xl font-semibold leading-tight">{{ psychologist.first_name }} {{ psychologist.last_name }}</div>
              <div class="text-sm opacity-90">Psychologist #{{ psychologist.id }} • {{ psychologist.specialization || '—' }}</div>
              <div class="mt-2 inline-flex items-center gap-2">
                <span :class="psychologist.is_approved ? 'bg-white text-[rgb(89,151,172)]' : 'bg-white/20 text-white'" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ psychologist.is_approved ? 'Approved' : 'Pending' }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white/20 text-white">
                  {{ formatCurrency(psychologist.price_per_session) }} / session
                </span>
              </div>
            </div>
          </div>
          <button @click="$emit('close')" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
        </div>
      </div>

      <!-- Content -->
      <div class="bg-white p-6 max-h-[80vh] overflow-y-auto styled-scrollbar">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left column -->
          <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 p-4">
              <div class="text-sm font-semibold text-gray-900">Documents</div>
              <div class="mt-3 space-y-2">
                <a v-if="psychologist.diploma" :href="psychologist.diploma" target="_blank" rel="noopener" class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-3 py-2 hover:bg-gray-50">
                  <div class="min-w-0">
                    <div class="text-sm font-medium text-gray-900">Diploma (PDF)</div>
                    <div class="text-xs text-gray-500 truncate">Open document</div>
                  </div>
                  <span class="text-xs font-semibold text-[rgb(141,61,79)]">View</span>
                </a>
                <div v-else class="rounded-lg border border-dashed border-gray-200 px-3 py-2 text-sm text-gray-500">Diploma not available</div>

                <a v-if="psychologist.cin" :href="psychologist.cin" target="_blank" rel="noopener" class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 px-3 py-2 hover:bg-gray-50">
                  <div class="min-w-0">
                    <div class="text-sm font-medium text-gray-900">CIN (PDF)</div>
                    <div class="text-xs text-gray-500 truncate">Open document</div>
                  </div>
                  <span class="text-xs font-semibold text-[rgb(141,61,79)]">View</span>
                </a>
                <div v-else class="rounded-lg border border-dashed border-gray-200 px-3 py-2 text-sm text-gray-500">CIN not available</div>
              </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
              <div class="text-sm font-semibold text-gray-900">Bio</div>
              <p class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ psychologist.bio || '—' }}</p>
            </div>
          </div>

          <!-- Right column -->
          <div class="lg:col-span-2 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="rounded-xl border border-gray-200 p-4">
                <div class="text-sm font-semibold text-gray-900">Profile details</div>
                <dl class="mt-3 space-y-3">
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">First name</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ psychologist.first_name || '—' }}</dd>
                  </div>
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">Last name</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ psychologist.last_name || '—' }}</dd>
                  </div>
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">Specialization</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ psychologist.specialization || '—' }}</dd>
                  </div>
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">Gender</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ psychologist.gender || '—' }}</dd>
                  </div>
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">Date of birth</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ formatDate(psychologist.date_of_birth) }}</dd>
                  </div>
                </dl>
              </div>

              <div class="rounded-xl border border-gray-200 p-4">
                <div class="text-sm font-semibold text-gray-900">Contact & location</div>
                <dl class="mt-3 space-y-3">
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">Phone</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ psychologist.phone || '—' }}</dd>
                  </div>
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">Country</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ psychologist.country || '—' }}</dd>
                  </div>
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">City</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ psychologist.city || '—' }}</dd>
                  </div>
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">Address</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ psychologist.address || '—' }}</dd>
                  </div>
                  <div class="flex items-start justify-between gap-4">
                    <dt class="text-xs font-medium text-gray-500">Price per session</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ formatCurrency(psychologist.price_per_session) }}</dd>
                  </div>
                </dl>
              </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
              <div class="flex items-center justify-between gap-4">
                <div class="text-sm font-semibold text-gray-900">Account</div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" :class="psychologist.user?.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'">
                  {{ psychologist.user?.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
              <dl class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start justify-between gap-4">
                  <dt class="text-xs font-medium text-gray-500">Account name</dt>
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ psychologist.user?.name || '—' }}</dd>
                </div>
                <div class="flex items-start justify-between gap-4">
                  <dt class="text-xs font-medium text-gray-500">Email</dt>
                  <dd class="text-sm font-medium text-gray-900 text-right">{{ psychologist.user?.email || '—' }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>

        <div class="pt-5 flex items-center justify-end">
          <button @click="$emit('close')" class="px-4 py-2 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  show: Boolean,
  psychologist: Object,
})

defineEmits(['close'])

function formatCurrency(value) {
  if (value == null) return '-'
  const n = Number(value)
  return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'TND', minimumFractionDigits: 2 }).format(n)
}

function formatDate(value) {
  if (!value) return '-'
  try {
    return new Date(value).toLocaleDateString()
  } catch {
    return '-'
  }
}
</script>

<style scoped>
.styled-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgb(89 151 172 / var(--tw-bg-opacity, 1)) rgba(229, 231, 235, 1);
}
.styled-scrollbar::-webkit-scrollbar {
  width: 10px;
  height: 10px;
}
.styled-scrollbar::-webkit-scrollbar-track {
  background: rgba(241, 245, 249, 1);
  border-radius: 9999px;
}
.styled-scrollbar::-webkit-scrollbar-thumb {
  background: rgb(89 151 172 / var(--tw-bg-opacity, 1));
  border-radius: 9999px;
  border: 2px solid #ffffff;
}
.styled-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgb(89 151 172 / 0.85);
}
</style>

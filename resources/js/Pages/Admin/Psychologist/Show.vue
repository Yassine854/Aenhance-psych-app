<template>
  <div class="p-6 space-y-6">
      <header class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Psychologist Details</h1>
          <p class="text-sm text-gray-600">Profile information and attachments.</p>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="route('psychologist-profiles.edit', profile.id)" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">Edit</Link>
          <Link :href="route('psychologist-profiles.index')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg shadow hover:bg-gray-200">Back</Link>
        </div>
      </header>

      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex flex-col md:flex-row gap-6">
          <div class="md:w-1/3 flex flex-col items-center">
            <img v-if="profile.profile_image_url" :src="profile.profile_image_url" class="h-40 w-40 rounded-full object-cover" />
            <div v-else class="h-40 w-40 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">No photo</div>
            <div class="mt-4 text-center">
              <div class="text-lg font-semibold text-gray-900">{{ profile.first_name }} {{ profile.last_name }}</div>
              <div class="text-sm text-gray-600">{{ profile.specialization || '-' }}</div>
            </div>
          </div>

          <div class="md:w-2/3 grid grid-cols-1 md:grid-cols-2 gap-4">
            <InfoRow label="Email" :value="profile.user?.email" />
            <InfoRow label="Price per session" :value="formatCurrency(profile.price_per_session)" />
            <InfoRow label="Gender" :value="profile.gender" />
            <InfoRow label="Country" :value="profile.country" />
            <InfoRow label="City" :value="profile.city" />
            <InfoRow label="Address" :value="profile.address" />
            <InfoRow label="Approved" :value="profile.is_approved ? 'Yes' : 'No'" />
            <InfoRow label="Date of birth" :value="formatDate(profile.date_of_birth)" />
          </div>
        </div>

        <div class="mt-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-3">Bio</h2>
          <p class="text-gray-700 whitespace-pre-line">{{ profile.bio || '—' }}</p>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
          <AttachmentCard v-if="profile.diploma" title="Diploma (PDF)" :href="profile.diploma" />
          <AttachmentCard v-if="profile.cin" title="CIN (PDF)" :href="profile.cin" />
        </div>
      </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ profile: Object })

const InfoRow = {
  props: {
    label: { type: String, required: true },
    value: { default: null },
  },
  template: `
    <div>
      <div class="text-xs text-gray-500">{{ label }}</div>
      <div class="text-sm text-gray-900">{{ value ?? '-' }}</div>
    </div>
  `,
}

const AttachmentCard = {
  props: {
    title: { type: String, required: true },
    href: { type: String, required: true },
  },
  template: `
    <a
      :href="href"
      target="_blank"
      class="block p-4 rounded-lg border border-gray-200 hover:border-indigo-300 hover:shadow transition"
    >
      <div class="font-medium text-gray-900 mb-1">{{ title }}</div>
      <div class="text-sm text-indigo-600">Open</div>
    </a>
  `,
}

function formatCurrency(value) {
  if (value == null) return '-'
  const n = Number(value)
  return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'TND', minimumFractionDigits: 2 }).format(n)
}

function formatDate(value) {
  if (!value) return '-' 
  try { return new Date(value).toLocaleDateString() } catch { return '-' }
}
</script>

<script>
export default {
  layout: AdminLayout,
  name: 'Admin/Psychologist/Show'
}
</script>

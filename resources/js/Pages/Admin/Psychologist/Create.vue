<template>
  <div class="p-6 space-y-6">
      <header class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-900">Add Psychologist</h1>
        <p class="text-sm text-gray-600">Create a new psychologist profile.</p>
      </header>

      <form @submit.prevent="submit" class="bg-white p-6 rounded-lg shadow space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium text-gray-700">First name</label>
            <input v-model="form.first_name" class="mt-1 block w-full rounded-md border-gray-300" />
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.first_name">{{ form.errors.first_name }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Last name</label>
            <input v-model="form.last_name" class="mt-1 block w-full rounded-md border-gray-300" />
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.last_name">{{ form.errors.last_name }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Specialization</label>
            <input v-model="form.specialization" class="mt-1 block w-full rounded-md border-gray-300" />
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.specialization">{{ form.errors.specialization }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Price per session (DT)</label>
            <input type="number" step="0.01" v-model="form.price_per_session" class="mt-1 block w-full rounded-md border-gray-300" />
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.price_per_session">{{ form.errors.price_per_session }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium text-gray-700">User (existing)</label>
            <select v-model="form.user_id" class="mt-1 block w-full rounded-md border-gray-300">
              <option value="">Select user</option>
              <option v-for="u in psychologistUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
            </select>
            <p class="mt-2 text-sm text-gray-500">Optional: link to an existing user.</p>
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.user_id">{{ form.errors.user_id }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Approved</label>
            <select v-model="form.is_approved" class="mt-1 block w-full rounded-md border-gray-300">
              <option :value="true">Yes</option>
              <option :value="false">No</option>
            </select>
          </div>
        </div>

        <div>
          <label class="text-sm font-medium text-gray-700">Bio</label>
          <textarea v-model="form.bio" class="mt-1 block w-full rounded-md border-gray-300 h-28"></textarea>
        </div>

        <div class="flex items-center gap-3">
          <button :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">Create</button>
          <Link :href="route('psychologist-profiles.index')" class="text-sm text-gray-600">Cancel</Link>
        </div>
      </form>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const form = useForm({
  user_id: '',
  first_name: '',
  last_name: '',
  specialization: '',
  price_per_session: 0,
  is_approved: false,
  bio: '',
})

const psychologistUsers = ref([])

onMounted(async () => {
  try {
    const res = await fetch('/users')
    const all = await res.json()
    psychologistUsers.value = (all || []).filter(u => u.role === 'PSYCHOLOGIST')
  } catch (e) {
    // ignore
  }
})

function submit() {
  form.post(route('psychologist-profiles.store'))
}
</script>

<script>
export default {
  layout: AdminLayout,
  name: 'Admin/Psychologist/Create'
}
</script>

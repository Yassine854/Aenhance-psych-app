<template>
  <div class="p-6 space-y-6">
      <header class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Psychologist</h1>
        <p class="text-sm text-gray-600">Update basic profile information.</p>
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
          <button :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">Save</button>
          <Link :href="route('psychologist-profiles.show', profile.id)" class="text-sm text-gray-600">Cancel</Link>
        </div>
      </form>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ profile: Object })

const form = useForm({
  _method: 'PUT',
  first_name: props.profile?.first_name || '',
  last_name: props.profile?.last_name || '',
  specialization: props.profile?.specialization || '',
  price_per_session: props.profile?.price_per_session || 0,
  is_approved: !!props.profile?.is_approved,
  bio: props.profile?.bio || '',
})

function submit() {
  form.post(route('psychologist-profiles.update', props.profile.id))
}
</script>

<script>
export default {
  layout: AdminLayout,
  name: 'Admin/Psychologist/Edit'
}
</script>

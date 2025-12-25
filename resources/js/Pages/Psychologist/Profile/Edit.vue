<template>
  <div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Edit Psychologist Profile</h1>

    <form @submit.prevent="submit" enctype="multipart/form-data">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block">First name</label>
          <input v-model="form.first_name" type="text" class="input" />
          <div v-if="form.errors.first_name" class="text-red-500">{{ form.errors.first_name }}</div>
        </div>
        <div>
          <label class="block">Last name</label>
          <input v-model="form.last_name" type="text" class="input" />
          <div v-if="form.errors.last_name" class="text-red-500">{{ form.errors.last_name }}</div>
        </div>
      </div>

      <div class="mt-4">
        <label class="block">Specialization</label>
        <input v-model="form.specialization" type="text" class="input" />
        <div v-if="form.errors.specialization" class="text-red-500">{{ form.errors.specialization }}</div>
      </div>

      <div class="mt-4">
        <label class="block">Bio</label>
        <textarea v-model="form.bio" class="input h-32"></textarea>
      </div>

      <div class="mt-4 grid grid-cols-2 gap-4">
        <div>
          <label class="block">Price per session</label>
          <input v-model="form.price_per_session" type="number" step="0.01" class="input" />
        </div>
        <div>
          <label class="block">Date of birth</label>
          <input v-model="form.date_of_birth" type="date" class="input" />
        </div>
      </div>

      <div class="mt-4">
        <label class="block">Profile image</label>
        <input ref="profileImage" @change="onFileChange('profile_image', $event)" type="file" accept="image/*" />
        <div v-if="profilePreview" class="mt-2">
          <img :src="profilePreview" class="h-24 w-24 object-cover rounded-full" />
        </div>
      </div>

      <div class="mt-4">
        <label class="block">Diploma (PDF)</label>
        <input @change="onFileChange('diploma_file', $event)" type="file" accept="application/pdf" />
        <div v-if="form.diploma && !files.diploma_file" class="mt-2 text-sm text-gray-600">Current: <a :href="form.diploma" target="_blank">View</a></div>
      </div>

      <div class="mt-4">
        <label class="block">CIN (PDF)</label>
        <input @change="onFileChange('cin_file', $event)" type="file" accept="application/pdf" />
        <div v-if="form.cin && !files.cin_file" class="mt-2 text-sm text-gray-600">Current: <a :href="form.cin" target="_blank">View</a></div>
      </div>

      <div class="mt-6 flex gap-3">
        <button type="submit" class="btn btn-primary" :disabled="form.processing">Save</button>
        <Link :href="route('dashboard')" class="btn">Cancel</Link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({ profile: Object })

const form = useForm({
  first_name: props.profile?.first_name || '',
  last_name: props.profile?.last_name || '',
  specialization: props.profile?.specialization || '',
  bio: props.profile?.bio || '',
  price_per_session: props.profile?.price_per_session || 0,
  date_of_birth: props.profile?.date_of_birth || null,
  profile_image_url: props.profile?.profile_image_url || null,
  diploma: props.profile?.diploma || null,
  cin: props.profile?.cin || null,
  profile_image: null,
  diploma_file: null,
  cin_file: null,
})

const files = ref({ profile_image: null, diploma_file: null, cin_file: null })
const profilePreview = ref(form.profile_image_url)

function onFileChange(field, event) {
  const file = event.target.files[0]
  files.value[field] = file
  form[field] = file
  if (field === 'profile_image' && file) {
    profilePreview.value = URL.createObjectURL(file)
  }
}

function submit() {
  form.put(route('psychologist.profile.update'), { forceFormData: true })
}
</script>

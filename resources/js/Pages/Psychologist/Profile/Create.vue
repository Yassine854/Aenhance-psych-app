<template>
  <div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Create Psychologist Profile</h1>

    <form @submit.prevent="submit" enctype="multipart/form-data">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block">First name</label>
          <input v-model="form.first_name" type="text" class="input" />
        </div>
        <div>
          <label class="block">Last name</label>
          <input v-model="form.last_name" type="text" class="input" />
        </div>
      </div>

      <div class="mt-4">
        <label class="block">Specialization</label>
        <input v-model="form.specialization" type="text" class="input" />
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
        <input @change="onFileChange('profile_image', $event)" type="file" accept="image/*" />
      </div>

      <div class="mt-4">
        <label class="block">Diploma (PDF)</label>
        <input @change="onFileChange('diploma_file', $event)" type="file" accept="application/pdf" />
      </div>

      <div class="mt-4">
        <label class="block">CIN (PDF)</label>
        <input @change="onFileChange('cin_file', $event)" type="file" accept="application/pdf" />
      </div>

      <div class="mt-6 flex gap-3">
        <button type="submit" class="btn btn-primary" :disabled="form.processing">Create</button>
        <Link :href="route('dashboard')" class="btn">Cancel</Link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'

const form = useForm({
  first_name: '',
  last_name: '',
  specialization: '',
  bio: '',
  price_per_session: 0,
  date_of_birth: null,
  profile_image: null,
  diploma_file: null,
  cin_file: null,
})

const files = ref({ profile_image: null, diploma_file: null, cin_file: null })

function onFileChange(field, event) {
  const file = event.target.files[0]
  files.value[field] = file
  form[field] = file
}

function submit() {
  form.post(route('psychologist.profile.store'), { forceFormData: true })
}
</script>
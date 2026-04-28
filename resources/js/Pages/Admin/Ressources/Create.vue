<template>
  <div class="space-y-6 p-6">
    <Head title="Create Ressource" />

    <RessourceEditorForm
      :form="form"
      page-title="Create Ressource"
      page-description="Upload a PDF and add the key details needed to manage this ressource from the admin panel."
      submit-label="Create Ressource"
      submitting-label="Creating..."
      :cancel-href="route('admin.ressources.index')"
      @submit="submit"
    />
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RessourceEditorForm from './RessourceEditorForm.vue'

defineOptions({ layout: AdminLayout })

const form = useForm({
  title: '',
  description: '',
  pdf: null,
  published_at: '',
  remove_pdf: false,
})

function submit() {
  form.post(route('admin.ressources.store'), {
    forceFormData: true,
  })
}
</script>
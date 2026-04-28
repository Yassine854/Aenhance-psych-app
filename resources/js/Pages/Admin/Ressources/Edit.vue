<template>
  <div class="space-y-6 p-6">
    <Head :title="`Edit ${ressource.title}`" />

    <RessourceEditorForm
      :form="form"
      page-title="Edit Ressource"
      page-description="Update the description, replace the PDF, or adjust the publish date for this ressource."
      submit-label="Save Changes"
      submitting-label="Saving..."
      :cancel-href="route('admin.ressources.index')"
      :existing-pdf-url="ressource.pdf || ''"
      @submit="submit"
    />
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RessourceEditorForm from './RessourceEditorForm.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  ressource: { type: Object, required: true },
})

const form = useForm({
  title: props.ressource.title || '',
  description: props.ressource.description || '',
  pdf: null,
  published_at: props.ressource.published_at || '',
  remove_pdf: false,
  _method: 'put',
})

function submit() {
  form.post(route('admin.ressources.update', props.ressource.id), {
    forceFormData: true,
  })
}
</script>
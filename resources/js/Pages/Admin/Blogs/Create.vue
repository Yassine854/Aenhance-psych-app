<template>
  <div class="space-y-6 p-6">
    <Head title="Create Blog Post" />

    <BlogEditorForm
      :form="form"
      mode="create"
      page-title="Create Blog Post"
      submit-label="Create Blog"
      submitting-label="Creating..."
      :cancel-href="route('admin.blogs.index')"
      @submit="submit"
    />
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import BlogEditorForm from './BlogEditorForm.vue'

defineOptions({ layout: AdminLayout })

const form = useForm({
  title: '',
  excerpt: '',
  content: '',
  featured_image: null,
  published_at: '',
  category: '',
  remove_featured_image: false,
})

function submit() {
  form.post(route('admin.blogs.store'), {
    forceFormData: true,
  })
}
</script>
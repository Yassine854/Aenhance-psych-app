<template>
  <div class="space-y-6 p-6">
    <Head :title="`Edit ${blog.title}`" />

    <BlogEditorForm
      :form="form"
      mode="edit"
      page-title="Edit Blog Post"
      submit-label="Save Changes"
      submitting-label="Saving..."
      :cancel-href="route('admin.blogs.index')"
      :existing-image-url="blog.featured_image || ''"
      @submit="submit"
    />
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import BlogEditorForm from './BlogEditorForm.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  blog: { type: Object, required: true },
})

const form = useForm({
  title: props.blog.title || '',
  excerpt: props.blog.excerpt || '',
  content: props.blog.content || '',
  featured_image: null,
  published_at: props.blog.published_at || '',
  category: props.blog.category || '',
  remove_featured_image: false,
  _method: 'put',
})

function submit() {
  form.post(route('admin.blogs.update', props.blog.id), {
    forceFormData: true,
  })
}
</script>
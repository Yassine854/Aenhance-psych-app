<template>
  <AdminLayout>
    <div class="p-6">
      <h1 class="text-2xl font-semibold">Admin Dashboard</h1>
      <p class="mt-4">I am the admin</p>
    </div>
  </AdminLayout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout.vue'
export default {
  layout: AdminLayout,
  name: 'Admin/Dashboard'
}
</script>
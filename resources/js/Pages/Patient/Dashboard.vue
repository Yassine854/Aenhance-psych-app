<template>
  <UserLayout>
    <div class="p-6">
      <h1 class="text-2xl font-semibold">Patient Dashboard</h1>
      <p class="mt-4">I am the Patient</p>
    </div>
  </UserLayout>
</template>

<script>
import UserLayout from '@/Layouts/UserLayout.vue'
export default {
  layout: UserLayout,
  name: 'Patient/Dashboard'
}
</script>
<script setup>
import Navbar from '@/Components/Navbar.vue'
import { Head, usePage } from '@inertiajs/vue3'
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue'
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue'
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue'

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  authUser: Object,
  mustVerifyEmail: Boolean,
  status: String,
})

const page = usePage()
</script>

<template>
  <Head title="Account" />

  <Navbar
    :canLogin="canLogin"
    :canRegister="canRegister"
    :authUser="authUser || page.props?.auth?.user"
  />

  <div class="min-h-[calc(100vh-112px)] bg-gray-50">
    <div class="bg-gradient-to-r from-[#af5166] to-[#5997ac]">
      <div class="mx-auto max-w-6xl px-4 py-8">
        <h1 class="text-2xl sm:text-3xl font-semibold text-white">Account info</h1>
        <p class="mt-1 text-sm text-white/90">Manage your profile, password, and security settings.</p>
      </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-8">
      <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 sm:p-8">
          <UpdateProfileInformationForm
            :must-verify-email="mustVerifyEmail"
            :status="status"
            class="max-w-xl"
          />
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 sm:p-8">
          <UpdatePasswordForm class="max-w-xl" />
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 sm:p-8">
          <DeleteUserForm class="max-w-xl" />
        </div>
      </div>
    </div>
  </div>
</template>
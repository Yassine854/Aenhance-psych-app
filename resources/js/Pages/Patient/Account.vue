<script setup>
import Navbar from '@/Components/Navbar.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head, useForm, usePage, Link } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import Swal from 'sweetalert2'

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  authUser: Object,
  mustVerifyEmail: Boolean,
  status: String,
})

const page = usePage()
const user = computed(() => page.props?.auth?.user)
const isAdmin = computed(() => user.value?.role === 'ADMIN')

const profileForm = useForm({
  name: user.value?.name || '',
  email: user.value?.email || '',
})

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const passwordInput = ref(null)
const currentPasswordInput = ref(null)

function submitProfile() {
  profileForm.patch(route('profile.update'), {
    preserveScroll: true,
    onSuccess: () => {
      // Update the shared props
      if (page.props?.auth) {
        page.props.auth.user.name = profileForm.name
        page.props.auth.user.email = profileForm.email
      }
      Swal.fire({
        position: 'top-end',
        icon: 'success',
        title: 'Profile updated successfully!',
        showConfirmButton: false,
        timer: 3000,
        toast: true,
        timerProgressBar: true,
        showCloseButton: true,
      })
    },
    onError: (errors) => {
      Swal.fire({
        position: 'top-end',
        icon: 'error',
        title: 'Update failed',
        text: 'Please check the form for errors.',
        showConfirmButton: false,
        timer: 3000,
        toast: true,
        timerProgressBar: true,
        showCloseButton: true,
      })
    }
  })
}

function updatePassword() {
  passwordForm.put(route('password.update'), {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset()
      Swal.fire({
        position: 'top-end',
        icon: 'success',
        title: 'Password updated successfully!',
        showConfirmButton: false,
        timer: 3000,
        toast: true,
        timerProgressBar: true,
        showCloseButton: true,
      })
    },
    onError: () => {
      if (passwordForm.errors.password) {
        passwordForm.reset('password', 'password_confirmation')
        passwordInput.value?.focus()
      }
      if (passwordForm.errors.current_password) {
        passwordForm.reset('current_password')
        currentPasswordInput.value?.focus()
      }
      Swal.fire({
        position: 'top-end',
        icon: 'error',
        title: 'Password update failed',
        text: 'Please check the form for errors.',
        showConfirmButton: false,
        timer: 3000,
        toast: true,
        timerProgressBar: true,
        showCloseButton: true,
      })
    },
  })
}

// Watch for form errors to show error SweetAlert
watch(() => Object.keys(profileForm.errors).length, (errorCount, oldErrorCount) => {
  if (errorCount > oldErrorCount && errorCount > 0) {
    const firstError = Object.values(profileForm.errors)[0]
    Swal.fire({
      position: 'top-end',
      icon: 'error',
      title: 'Validation Error',
      text: Array.isArray(firstError) ? firstError[0] : firstError,
      showConfirmButton: false,
      timer: 4000,
      toast: true,
      timerProgressBar: true,
      showCloseButton: true,
    })
  }
})

watch(() => Object.keys(passwordForm.errors).length, (errorCount, oldErrorCount) => {
  if (errorCount > oldErrorCount && errorCount > 0) {
    const firstError = Object.values(passwordForm.errors)[0]
    Swal.fire({
      position: 'top-end',
      icon: 'error',
      title: 'Validation Error',
      text: Array.isArray(firstError) ? firstError[0] : firstError,
      showConfirmButton: false,
      timer: 4000,
      toast: true,
      timerProgressBar: true,
      showCloseButton: true,
    })
  }
})
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
      <div class="space-y-8">
        <!-- Profile Information Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
          <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
              </div>
              <div>
                <h2 class="text-lg font-semibold text-white">Profile Information</h2>
                <p class="text-sm text-white/80">Update your account's profile information</p>
              </div>
            </div>
          </div>
          <div class="p-6 sm:p-8">
            <form @submit.prevent="submitProfile" class="space-y-6">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <InputLabel class="text-sm font-medium text-gray-700">
                    Name <span class="text-red-500">*</span>
                  </InputLabel>
                  <div class="relative">
                    <TextInput
                      v-model="profileForm.name"
                      type="text"
                      class="mt-1 block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac] sm:text-sm"
                      placeholder="Enter your full name"
                    />
                    <div class="absolute top-3 left-3 pointer-events-none">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                    </div>
                  </div>
                  <InputError class="mt-2" :message="profileForm.errors.name" />
                </div>
                <div class="space-y-2">
                  <InputLabel class="text-sm font-medium text-gray-700">
                    Email <span class="text-red-500">*</span>
                  </InputLabel>
                  <div class="relative">
                    <TextInput
                      v-model="profileForm.email"
                      type="email"
                      class="mt-1 block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac] sm:text-sm"
                      placeholder="Enter your email address"
                    />
                    <div class="absolute top-3 left-3 pointer-events-none">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                      </svg>
                    </div>
                  </div>
                  <InputError class="mt-2" :message="profileForm.errors.email" />
                </div>
              </div>

              <div v-if="mustVerifyEmail && user.email_verified_at === null" class="mt-4">
                <p class="text-sm text-gray-800">
                  Your email address is unverified.
                  <Link
                    :href="route('verification.send')"
                    method="post"
                    as="button"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  >
                    Click here to re-send the verification email.
                  </Link>
                </p>

                <div v-if="status === 'verification-link-sent'" class="mt-2 font-medium text-sm text-green-600">
                  A new verification link has been sent to your email address.
                </div>
              </div>

              <div class="flex items-center gap-4">
                <PrimaryButton
                  :disabled="profileForm.processing"
                  class="inline-flex items-center gap-2 px-6 py-2 bg-gradient-to-r from-[#af5166] to-[#c66b85] hover:from-[#8b4156] hover:to-[#a54f6a] text-white font-medium rounded-lg shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#af5166] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="profileForm.processing" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  {{ profileForm.processing ? 'Saving Changes...' : 'Save Changes' }}
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>

        <!-- Password Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
          <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
              </div>
              <div>
                <h2 class="text-lg font-semibold text-white">Update Password</h2>
                <p class="text-sm text-white/80">Ensure your account is using a long, random password to stay secure</p>
              </div>
            </div>
          </div>
          <div class="p-6 sm:p-8">
            <form @submit.prevent="updatePassword" class="space-y-6">
              <div class="space-y-2">
                <InputLabel class="text-sm font-medium text-gray-700">
                  Current Password <span class="text-red-500">*</span>
                </InputLabel>
                <div class="relative">
                  <TextInput
                    ref="currentPasswordInput"
                    v-model="passwordForm.current_password"
                    type="password"
                    class="mt-1 block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac] sm:text-sm"
                    placeholder="Enter your current password"
                  />
                  <div class="absolute top-3 left-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                  </div>
                </div>
                <InputError class="mt-2" :message="passwordForm.errors.current_password" />
              </div>

              <div class="space-y-2">
                <InputLabel class="text-sm font-medium text-gray-700">
                  New Password <span class="text-red-500">*</span>
                </InputLabel>
                <div class="relative">
                  <TextInput
                    ref="passwordInput"
                    v-model="passwordForm.password"
                    type="password"
                    class="mt-1 block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac] sm:text-sm"
                    placeholder="Enter your new password"
                  />
                  <div class="absolute top-3 left-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                  </div>
                </div>
                <InputError class="mt-2" :message="passwordForm.errors.password" />
              </div>

              <div class="space-y-2">
                <InputLabel class="text-sm font-medium text-gray-700">
                  Confirm New Password <span class="text-red-500">*</span>
                </InputLabel>
                <div class="relative">
                  <TextInput
                    v-model="passwordForm.password_confirmation"
                    type="password"
                    class="mt-1 block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac] sm:text-sm"
                    placeholder="Confirm your new password"
                  />
                  <div class="absolute top-3 left-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                  </div>
                </div>
                <InputError class="mt-2" :message="passwordForm.errors.password_confirmation" />
              </div>

              <div class="flex items-center gap-4">
                <PrimaryButton
                  :disabled="passwordForm.processing"
                  class="inline-flex items-center gap-2 px-6 py-2 bg-gradient-to-r from-[#af5166] to-[#c66b85] hover:from-[#8b4156] hover:to-[#a54f6a] text-white font-medium rounded-lg shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#af5166] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="passwordForm.processing" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  {{ passwordForm.processing ? 'Updating Password...' : 'Update Password' }}
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

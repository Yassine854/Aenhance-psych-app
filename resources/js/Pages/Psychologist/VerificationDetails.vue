<script setup>
import Navbar from '@/Components/Navbar.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head, useForm, usePage, Link } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  authUser: Object,
  verification_details: Object,
  status: String,
})

const page = usePage()

const form = useForm({
  rib: props.verification_details?.rib || '',
  bank_name: props.verification_details?.bank_name || '',
  bank_account_number: props.verification_details?.bank_account_number || '',
  bank_account_name: props.verification_details?.bank_account_name || '',
  rib_file: null,
  identity_type: props.verification_details?.identity_type || '',
  identity_number: props.verification_details?.identity_number || '',
  identity_file: null,
  diploma_files: [],
})

const ribFileInput = ref(null)
const identityInput = ref(null)
const diplomaInput = ref(null)

const isSubmitted = computed(() => !!props.verification_details)

function onRibFileChange(e) {
  const file = e?.target?.files?.[0] || null
  form.rib_file = file
}

function onIdentityChange(e) {
  const file = e?.target?.files?.[0] || null
  form.identity_file = file
}

function onDiplomaChange(e) {
  const files = e.target.files
  if (files && files.length > 0) {
    form.diploma_files = [...form.diploma_files, ...Array.from(files)]
    e.target.value = '' // reset to allow selecting same files again
  }
}

function removeIdentity() {
  form.identity_file = null
  if (identityInput.value) identityInput.value = ''
}

function removeDiploma(index) {
  form.diploma_files.splice(index, 1)
}

function submit() {
  // Check if bank information is complete (requires RIB, bank name, and RIB file)
  const bankComplete = form.rib && form.bank_name && form.rib_file;

  // Show confirmation dialog
  Swal.fire({
    title: 'Confirm Submission',
    html: `
      <div class="text-center space-y-4">
        <div class="w-16 h-16 bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] rounded-full flex items-center justify-center mx-auto">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>

        <div class="space-y-3">
          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span class="font-medium text-gray-700">Bank Information</span>
            <span class="px-2 py-1 rounded text-sm font-medium ${bankComplete ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
              ${bankComplete ? '✓ Complete' : '✗ Missing'}
            </span>
          </div>

          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span class="font-medium text-gray-700">Identity Document</span>
            <span class="px-2 py-1 rounded text-sm font-medium ${form.identity_file ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
              ${form.identity_file ? '✓ Uploaded' : '✗ Missing'}
            </span>
          </div>

          <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <span class="font-medium text-gray-700">Diploma Copies</span>
            <span class="px-2 py-1 rounded text-sm font-medium ${form.diploma_files.length > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
              ${form.diploma_files.length > 0 ? `✓ ${form.diploma_files.length} file(s)` : '✗ Missing'}
            </span>
          </div>
        </div>

        <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
          <p class="text-sm text-blue-800 font-medium">
            By confirming, you certify that all documents are authentic and accurate.
          </p>
        </div>
      </div>
    `,
    showCancelButton: true,
    confirmButtonText: 'Submit Verification',
    cancelButtonText: 'Review',
    customClass: {
      popup: 'rounded-2xl shadow-xl',
      confirmButton: 'px-6 py-3 rounded-lg font-semibold text-white bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] hover:from-[#4a7a95] hover:to-[#5a8ba0] transition-all duration-200',
      cancelButton: 'px-6 py-3 rounded-lg font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 transition-all duration-200 mr-4'
    },
    buttonsStyling: false,
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      // Proceed with form submission
      form.post(route('psychologist.verification.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: (page) => {
          // Clear files after successful submission
          form.diploma_files.splice(0)
          form.rib_file = null
          form.identity_file = null
          if (ribFileInput.value) ribFileInput.value.value = ''
          if (identityInput.value) identityInput.value = ''
          if (diplomaInput.value) diplomaInput.value.value = ''
          // Show success message
          Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Verification details submitted successfully!',
            text: 'Your documents will be reviewed shortly.',
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
            title: 'Submission failed',
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
  })
}

// Watch for form errors to show error SweetAlert
watch(() => Object.keys(form.errors).length, (errorCount, oldErrorCount) => {
  if (errorCount > oldErrorCount && errorCount > 0) {
    const firstError = Object.values(form.errors)[0]
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
  <Head title="Verification Details" />

  <Navbar
    :canLogin="canLogin"
    :canRegister="canRegister"
    :authUser="authUser || page.props?.auth?.user"
  />

  <div class="min-h-[calc(100vh-112px)] bg-gray-50">
    <div class="bg-gradient-to-r from-[#af5166] to-[#5997ac]">
      <div class="mx-auto max-w-6xl px-4 py-8">
        <div class="flex items-center gap-4">
          <div class="h-14 w-14 rounded-full overflow-hidden ring-2 ring-white/70 bg-white/15 flex items-center justify-center">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-semibold text-white">Complete Your Verification</h1>
            <p class="mt-1 text-sm text-white/90">Provide your banking and identity information to complete the verification process.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Back to Profile Button -->
    <div class="mx-auto max-w-6xl px-4 py-4">
      <Link
        :href="route('psychologist.profile.self')"
        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
      >
        <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Back to Profile
      </Link>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-8">
      <form @submit.prevent="submit" class="space-y-8">
        <!-- Bank Information Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
          <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
              </div>
              <div>
                <h2 class="text-lg font-semibold text-white">Bank Information</h2>
                <p class="text-sm text-white/80">Your banking details for payments</p>
              </div>
            </div>
          </div>
          <div class="p-6 sm:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <InputLabel for="rib" class="text-sm font-medium text-gray-700">
                  RIB (Relevé d'Identité Bancaire) <span class="text-red-500">*</span>
                </InputLabel>
                <div class="relative">
                  <TextInput
                    id="rib"
                    v-model="form.rib"
                    type="text"
                    class="mt-1 block w-full pl-10"
                    placeholder="Enter your RIB number"
                    :readonly="isSubmitted"
                  />
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                  </div>
                </div>
                <InputError :message="form.errors.rib" class="mt-2" />
              </div>

              <div class="space-y-2">
                <InputLabel for="bank_name" class="text-sm font-medium text-gray-700">
                  Bank Name <span class="text-red-500">*</span>
                </InputLabel>
                <div class="relative">
                  <TextInput
                    id="bank_name"
                    v-model="form.bank_name"
                    type="text"
                    class="mt-1 block w-full pl-10"
                    placeholder="Enter your bank name"
                    :readonly="isSubmitted"
                  />
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                  </div>
                </div>
                <InputError :message="form.errors.bank_name" class="mt-2" />
              </div>

              <div class="space-y-2">
                <InputLabel for="bank_account_number" class="text-sm font-medium text-gray-700">
                  Account Number <span class="text-red-500">*</span>
                </InputLabel>
                <div class="relative">
                  <TextInput
                    id="bank_account_number"
                    v-model="form.bank_account_number"
                    type="text"
                    class="mt-1 block w-full pl-10"
                    placeholder="Enter your account number"
                    :readonly="isSubmitted"
                  />
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                  </div>
                </div>
                <InputError :message="form.errors.bank_account_number" class="mt-2" />
              </div>

              <div class="space-y-2">
                <InputLabel for="bank_account_name" value="Account Holder Name" class="text-sm font-medium text-gray-700" />
                <div class="relative">
                  <TextInput
                    id="bank_account_name"
                    v-model="form.bank_account_name"
                    type="text"
                    class="mt-1 block w-full pl-10"
                    placeholder="Enter account holder name"
                    :readonly="isSubmitted"
                  />
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                  </div>
                </div>
                <InputError :message="form.errors.bank_account_name" class="mt-2" />
              </div>

              <div class="md:col-span-2 space-y-2">
                <div class="flex items-center gap-3 mb-4">
                  <div class="w-10 h-10 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                  </div>
                  <div>
                    <h3 class="text-sm font-semibold text-gray-900">RIB Document <span class="text-red-500">*</span></h3>
                    <p class="text-xs text-gray-600">Upload your bank statement document</p>
                  </div>
                </div>

                <div v-if="!isSubmitted">
                  <input ref="ribFileInput" type="file" @change="onRibFileChange" accept=".pdf,.jpg,.jpeg,.png" class="hidden" />
                  <div class="flex items-center gap-3">
                    <PrimaryButton type="button" @click="ribFileInput?.click()" class="px-4 py-2 bg-[#5997ac] hover:bg-[#4a7a95] rounded-lg transition-colors duration-200 flex items-center gap-2">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      Add RIB Document
                    </PrimaryButton>
                    <div v-if="form.rib_file" class="text-sm text-gray-600">
                      1 file selected
                    </div>
                  </div>
                  <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded-lg">
                    Accepted formats: PDF, JPG, PNG. Maximum 5MB.
                  </div>
                  <InputError class="mt-2" :message="form.errors.rib_file" />

                  <!-- Display selected RIB file -->
                  <div v-if="form.rib_file" class="space-y-2">
                    <h4 class="text-sm font-medium text-gray-900 flex items-center gap-2">
                      <svg class="w-4 h-4 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      Selected File
                    </h4>
                    <div class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                          <svg class="w-4 h-4 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                          </svg>
                        </div>
                        <div>
                          <span class="text-sm font-medium text-gray-700">{{ form.rib_file.name }}</span>
                          <span class="text-xs text-gray-500 block">({{ (form.rib_file.size / 1024 / 1024).toFixed(2) }} MB)</span>
                        </div>
                      </div>
                      <button
                        type="button"
                        @click="form.rib_file = null; if (ribFileInput.value) ribFileInput.value.value = ''"
                        class="text-red-600 hover:text-red-800 p-1.5 rounded-lg hover:bg-red-50 transition-colors duration-200"
                        title="Remove file"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- View existing RIB file when submitted -->
                <div v-else-if="isSubmitted && props.verification_details.rib_file_url" class="space-y-2">
                  <h4 class="text-sm font-medium text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    RIB Document
                  </h4>
                  <div class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                      </div>
                      <div>
                        <span class="text-sm font-medium text-gray-700">RIB Document</span>
                        <span class="text-xs text-gray-500 block">Uploaded document</span>
                      </div>
                    </div>
                    <a
                      :href="props.verification_details.rib_file_url"
                      target="_blank"
                      class="text-[#5997ac] hover:text-[#4a7a95] p-1.5 rounded-lg hover:bg-[#5997ac]/10 transition-colors duration-200"
                      title="View file"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Identity Information Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
          <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                </svg>
              </div>
              <div>
                <h2 class="text-lg font-semibold text-white">Identity Information</h2>
                <p class="text-sm text-white/80">Your personal identification details</p>
              </div>
            </div>
          </div>
          <div class="p-6 sm:p-8">
            <div class="space-y-4">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="text-sm font-semibold text-gray-900">Identity Documents <span class="text-red-500">*</span></h3>
                  <p class="text-xs text-gray-600">Upload your identification documents</p>
                </div>
              </div>

              <div class="space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <InputLabel for="identity_type" class="text-sm font-medium text-gray-700">
                      Identity Type <span class="text-red-500">*</span>
                    </InputLabel>
                    <div class="relative">
                      <select
                        v-model="form.identity_type"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac] sm:text-sm pl-10"
                        :disabled="isSubmitted"
                      >
                        <option value="">Select identity type</option>
                        <option value="National ID">National ID</option>
                        <option value="Passport">Passport</option>
                      </select>
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                      </div>
                    </div>
                    <InputError :message="form.errors.identity_type" class="mt-2" />
                  </div>
                  <div>
                    <InputLabel for="identity_number" class="text-sm font-medium text-gray-700">
                      Identity Number <span class="text-red-500">*</span>
                    </InputLabel>
                    <div class="relative">
                      <TextInput
                        v-model="form.identity_number"
                        type="text"
                        class="mt-1 block w-full pl-10"
                        placeholder="Enter identity number"
                        :readonly="isSubmitted"
                      />
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                      </div>
                    </div>
                    <InputError :message="form.errors.identity_number" class="mt-2" />
                  </div>
                </div>

                <div v-if="!isSubmitted">
                  <input ref="identityInput" type="file" @change="onIdentityChange" accept=".pdf,.jpg,.jpeg,.png" class="hidden" />
                  <div class="flex items-center gap-3">
                    <PrimaryButton type="button" @click="identityInput?.click()" class="px-4 py-2 bg-[#5997ac] hover:bg-[#4a7a95] rounded-lg transition-colors duration-200 flex items-center gap-2">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      Add Identity Document
                    </PrimaryButton>
                    <div v-if="form.identity_file" class="text-sm text-gray-600">
                      1 file selected
                    </div>
                  </div>
                  <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded-lg">
                    Accepted formats: PDF, JPG, PNG. Maximum 5MB.
                  </div>
                  <InputError class="mt-2" :message="form.errors.identity_file" />

                  <!-- Display selected identity file -->
                  <div v-if="form.identity_file" class="space-y-2">
                    <h4 class="text-sm font-medium text-gray-900 flex items-center gap-2">
                      <svg class="w-4 h-4 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      Selected File
                    </h4>
                    <div class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                          <svg class="w-4 h-4 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                          </svg>
                        </div>
                        <div>
                          <span class="text-sm font-medium text-gray-700">{{ form.identity_file.name }}</span>
                          <span class="text-xs text-gray-500 block">({{ (form.identity_file.size / 1024 / 1024).toFixed(2) }} MB)</span>
                        </div>
                      </div>
                      <button
                        type="button"
                        @click="form.identity_file = null; if (identityInput.value) identityInput.value.value = ''"
                        class="text-red-600 hover:text-red-800 p-1.5 rounded-lg hover:bg-red-50 transition-colors duration-200"
                        title="Remove file"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- View existing identity files when submitted -->
                <div v-else-if="isSubmitted && props.verification_details.identity_files_urls && props.verification_details.identity_files_urls.length > 0" class="space-y-2">
                  <h4 class="text-sm font-medium text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Identity Documents
                  </h4>
                  <div class="space-y-2">
                    <div v-for="(fileUrl, index) in props.verification_details.identity_files_urls" :key="index" class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                          <svg class="w-4 h-4 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                          </svg>
                        </div>
                        <div>
                          <span class="text-sm font-medium text-gray-700">Identity Document {{ index + 1 }}</span>
                          <span class="text-xs text-gray-500 block">Uploaded document</span>
                        </div>
                      </div>
                      <a
                        :href="fileUrl"
                        target="_blank"
                        class="text-[#5997ac] hover:text-[#4a7a95] p-1.5 rounded-lg hover:bg-[#5997ac]/10 transition-colors duration-200"
                        title="View file"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Diplomas Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
          <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14l9-5-9-5-9 5 9 5z"></path>
                </svg>
              </div>
              <div>
                <h2 class="text-lg font-semibold text-white">Professional Diplomas</h2>
                <p class="text-sm text-white/80">Upload your educational qualifications</p>
              </div>
            </div>
          </div>
          <div class="p-6 sm:p-8">
            <div class="space-y-4">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="text-sm font-semibold text-gray-900">Certified Diplomas Copies <span class="text-red-500">*</span></h3>
                  <p class="text-xs text-gray-600">Upload your certified professional qualifications</p>
                </div>
              </div>

              <div class="space-y-3">
                <div v-if="!isSubmitted">
                  <input ref="diplomaInput" type="file" multiple accept=".pdf,.jpg,.jpeg,.png" @change="onDiplomaChange" class="hidden" />
                  <div class="flex items-center gap-3">
                    <PrimaryButton type="button" @click="diplomaInput?.click()" class="px-4 py-2 bg-[#5997ac] hover:bg-[#4a7a95] rounded-lg transition-colors duration-200 flex items-center gap-2">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                      </svg>
                      Add Diploma
                    </PrimaryButton>
                    <div v-if="form.diploma_files.length > 0" class="text-sm text-gray-600">
                      {{ form.diploma_files.length }} file(s) selected
                    </div>
                  </div>
                  <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded-lg">
                    Accepted formats: PDF, JPG, PNG. Maximum 5MB per file.
                  </div>
                  <InputError class="mt-2" :message="form.errors.diploma_files" />

                  <!-- Display selected diploma files -->
                  <div v-if="form.diploma_files.length > 0" class="space-y-2">
                    <h4 class="text-sm font-medium text-gray-900 flex items-center gap-2">
                      <svg class="w-4 h-4 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      Selected Files
                    </h4>
                    <div class="space-y-2">
                      <div v-for="(file, index) in form.diploma_files" :key="index" class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                        <div class="flex items-center gap-3">
                          <div class="w-8 h-8 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                          </div>
                          <div>
                            <span class="text-sm font-medium text-gray-700">{{ file.name }}</span>
                            <span class="text-xs text-gray-500 block">({{ (file.size / 1024 / 1024).toFixed(2) }} MB)</span>
                          </div>
                        </div>
                        <button
                          type="button"
                          @click="removeDiploma(index)"
                          class="text-red-600 hover:text-red-800 p-1.5 rounded-lg hover:bg-red-50 transition-colors duration-200"
                          title="Remove file"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- View existing diploma files when submitted -->
                <div v-else-if="isSubmitted && props.verification_details.diploma_files_urls && props.verification_details.diploma_files_urls.length > 0" class="space-y-2">
                  <h4 class="text-sm font-medium text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Certified Diplomas Copies
                  </h4>
                  <div class="space-y-2">
                    <div v-for="(fileUrl, index) in props.verification_details.diploma_files_urls" :key="index" class="flex items-center justify-between p-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                          <svg class="w-4 h-4 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                          </svg>
                        </div>
                        <div>
                          <span class="text-sm font-medium text-gray-700">Diploma Document {{ index + 1 }}</span>
                          <span class="text-xs text-gray-500 block">Uploaded document</span>
                        </div>
                      </div>
                      <a
                        :href="fileUrl"
                        target="_blank"
                        class="text-[#5997ac] hover:text-[#4a7a95] p-1.5 rounded-lg hover:bg-[#5997ac]/10 transition-colors duration-200"
                        title="View file"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit Section -->
        <div v-if="!isSubmitted" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
          <div class="bg-gradient-to-r from-[#af5166] to-[#c66b85] px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div>
                <h2 class="text-lg font-semibold text-white">Submit Verification</h2>
                <p class="text-sm text-white/80">Review and submit your verification details</p>
              </div>
            </div>
          </div>
          <div class="p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div class="flex-1">
                <p class="text-sm text-gray-600 mb-2">Please ensure all information is accurate. Your documents will be reviewed by our team.</p>
              </div>
              <div class="flex items-center gap-3">
                <Link
                  :href="route('psychologist.profile.self')"
                  class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                >
                  Back to Profile
                </Link>
                <PrimaryButton
                  :disabled="form.processing"
                  class="inline-flex items-center gap-2 px-6 py-2 bg-gradient-to-r from-[#af5166] to-[#c66b85] hover:from-[#8b4156] hover:to-[#a54f6a] text-white font-medium rounded-lg shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#af5166] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  {{ form.processing ? 'Submitting Verification...' : 'Submit Verification' }}
                </PrimaryButton>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>
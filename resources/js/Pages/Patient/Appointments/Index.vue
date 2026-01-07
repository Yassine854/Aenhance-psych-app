<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import Swal from 'sweetalert2'
import Navbar from '@/Components/Navbar.vue'
import Footer from '@/Components/Footer.vue'

const props = defineProps({
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
  authUser: { type: Object },
  status: { type: String, default: '' },
  appointments: { type: Array, default: () => [] },
})

const page = usePage()

const flashStatus = computed(() => props.status || page.props?.flash?.status || '')
const confirmingId = ref(null)
const cancelingId = ref(null)

const toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 2200,
  timerProgressBar: true,
})

function formatDateTime(value) {
  if (!value) return ''
  try {
    return new Date(value).toLocaleString(undefined, {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return String(value)
  }
}

function statusBadgeClass(status) {
  const s = String(status || '').toLowerCase()
  if (s === 'confirmed') return 'bg-green-50 text-green-700 ring-1 ring-green-200'
  if (s === 'pending') return 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200'
  if (s === 'completed') return 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'
  if (s === 'cancelled') return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
  if (s === 'no_show') return 'bg-red-50 text-red-700 ring-1 ring-red-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}

function statusLabel(status) {
  const s = String(status || '').toLowerCase()
  if (s === 'no_show') return 'No show'
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : '—'
}

function canPay(a) {
  return String(a?.status || '').toLowerCase() === 'pending'
}

function payAndConfirm(a) {
  if (!a?.id || !canPay(a)) return
  confirmingId.value = a.id

  router.patch(
    route('appointments.update', a.id),
    { status: 'confirmed' },
    {
      preserveScroll: true,
      onFinish: () => {
        confirmingId.value = null
      },
    }
  )
}

function canCancel(a) {
  return String(a?.status || '').toLowerCase() === 'pending'
}

async function cancelAppointment(a) {
  if (!a?.id || !canCancel(a)) return

  const res = await Swal.fire({
    title: 'Cancel this appointment?',
    text: 'This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, cancel it',
    cancelButtonText: 'Keep it',
    reverseButtons: true,
    focusCancel: true,
  })

  if (!res.isConfirmed) return

  cancelingId.value = a.id
  router.delete(route('appointments.destroy', a.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast.fire({ icon: 'success', title: 'Appointment cancelled' })
    },
    onError: () => {
      toast.fire({ icon: 'error', title: 'Could not cancel appointment' })
    },
    onFinish: () => {
      cancelingId.value = null
    },
  })
}
</script>

<template>
  <Head title="My appointments" />

  <Navbar :canLogin="canLogin" :canRegister="canRegister" :authUser="authUser" />

  <div class="bg-gray-50 py-10 md:py-14">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-start justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">My appointments</h1>
          <p class="mt-2 text-gray-700 max-w-3xl">
            View your booked sessions and confirm pending ones.
          </p>
        </div>

        <Link
          :href="route('services.consultation')"
          class="hidden sm:inline-flex items-center justify-center px-4 py-2 rounded-md bg-white border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-100 transition"
        >
          Book a new one
        </Link>
      </div>

      <div v-if="flashStatus" class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        {{ flashStatus }}
      </div>

      <div v-if="!appointments.length" class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 text-gray-700">
        You don’t have any appointments yet.
        <div class="mt-4">
          <Link
            :href="route('services.consultation')"
            class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-[#5997ac] text-white text-sm font-semibold hover:opacity-90 transition"
          >
            Find a psychologist
          </Link>
        </div>
      </div>

      <div v-else class="space-y-4">
        <TransitionGroup name="list" tag="div" class="space-y-4">
          <div
            v-for="a in appointments"
            :key="a.id"
            class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden"
          >
            <div class="p-5 md:p-6">
              <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div class="min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <div class="text-lg font-semibold text-gray-900 truncate">
                      {{ a.psychologist_name || 'Psychologist' }}
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold" :class="statusBadgeClass(a.status)">
                      {{ statusLabel(a.status) }}
                    </span>
                  </div>

                  <div class="mt-2 text-sm text-gray-700">
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-1">
                      <div>
                        <span class="text-gray-500">Start:</span>
                        <span class="font-medium text-gray-900">{{ formatDateTime(a.scheduled_start) }}</span>
                      </div>
                      <div>
                        <span class="text-gray-500">End:</span>
                        <span class="font-medium text-gray-900">{{ formatDateTime(a.scheduled_end) }}</span>
                      </div>
                      <div v-if="a.price != null">
                        <span class="text-gray-500">Price:</span>
                        <span class="font-semibold text-gray-900">{{ Number(a.price).toFixed(2) }} {{ a.currency || 'TND' }}</span>
                      </div>
                    </div>
                  </div>

                  <div v-if="a.reference" class="mt-2 text-xs text-gray-500">Ref: {{ a.reference }}</div>
                </div>

                <div class="flex items-center gap-2 md:justify-end">
                  <button
                    v-if="canPay(a)"
                    type="button"
                    @click="payAndConfirm(a)"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-[#af5166] text-white text-sm font-semibold hover:opacity-90 transition disabled:opacity-60 disabled:cursor-not-allowed"
                    :disabled="confirmingId === a.id"
                  >
                    {{ confirmingId === a.id ? 'Processing…' : 'Pay & confirm' }}
                  </button>

                  <button
                    v-if="canCancel(a)"
                    type="button"
                    @click="cancelAppointment(a)"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white border border-red-200 text-red-700 text-sm font-semibold hover:bg-red-50 transition disabled:opacity-60 disabled:cursor-not-allowed"
                    :disabled="cancelingId === a.id"
                  >
                    {{ cancelingId === a.id ? 'Cancelling…' : 'Cancel' }}
                  </button>
                </div>
              </div>

            </div>
          </div>
        </TransitionGroup>
      </div>

      <div class="sm:hidden mt-8">
        <Link
          :href="route('services.consultation')"
          class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-white border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-100 transition"
        >
          Book a new one
        </Link>
      </div>
    </div>
  </div>

  <Footer />
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: opacity 180ms ease, transform 180ms ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>

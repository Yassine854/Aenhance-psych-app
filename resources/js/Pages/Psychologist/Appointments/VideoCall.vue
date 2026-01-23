<template>
  <Head title="Video call" />

  <Navbar :canLogin="canLogin" :canRegister="canRegister" :authUser="authUser || page.props?.auth?.user" />

  <div class="bg-gray-50 py-10 md:py-14">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <VideoCallPanel
        :appointment-id="appointmentId"
        :room-id="roomId"
        :display-name="displayName"
        :signaling-url="signalingUrl"
        :role="role"
        :back-href="route('psychologist.appointments.index')"
        :height-offset="190"
      />
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import Navbar from '@/Components/Navbar.vue'
import Footer from '@/Components/Footer.vue'
import VideoCallPanel from '@/Components/VideoCall/VideoCallPanel.vue'

const page = usePage()

const props = defineProps({
  appointmentId: { type: Number, required: true },
  roomId: { type: String, required: true },
  displayName: { type: String, default: '' },
  signalingUrl: { type: String, default: '' },
  role: { type: String, default: 'psychologist' },
  canLogin: { type: Boolean, default: false },
  canRegister: { type: Boolean, default: false },
  authUser: { type: Object, default: null },
})
</script>

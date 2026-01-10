<template>
  <VideoCallPanel
    :appointment-id="appointmentId"
    :room-id="roomId"
    :display-name="displayName"
    :signaling-url="signalingUrl"
    :role="role"
    :back-href="route('psychologist.appointments.index')"
    :height-offset="120"
  />
</template>

<script setup>
import UserLayout from '@/Layouts/UserLayout.vue'
import VideoCallPanel from '@/Components/VideoCall/VideoCallPanel.vue'

defineOptions({ layout: UserLayout })

const props = defineProps({
  appointmentId: { type: Number, required: true },
  roomId: { type: String, required: true },
  displayName: { type: String, default: '' },
  signalingUrl: { type: String, default: '' },
  role: { type: String, default: 'psychologist' },
})
</script>

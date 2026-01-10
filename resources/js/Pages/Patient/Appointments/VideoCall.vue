<template>
  <Head title="Video call" />

  <Navbar :canLogin="canLogin" :canRegister="canRegister" :authUser="authUser" />

  <div class="bg-gray-50 py-10 md:py-14">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-4 flex items-center justify-between gap-3">
        <div class="min-w-0">
          <h1 class="text-2xl font-bold text-gray-900">Video call</h1>
          <p class="text-sm text-gray-600 truncate">Room: {{ roomId }}</p>
        </div>

        <Link
          :href="route('patient.appointments')"
          class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition"
        >
          Back
        </Link>
      </div>

      <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden" style="height: calc(100vh - 190px)">
        <div v-if="error" class="p-4 text-sm text-red-700 bg-red-50 border-b border-red-200">
          {{ error }}
        </div>

        <div class="w-full h-full relative bg-black">
          <video ref="remoteVideo" class="w-full h-full object-cover" autoplay playsinline></video>
          <video
            ref="localVideo"
            class="absolute bottom-3 right-3 w-48 h-32 bg-black rounded-lg border border-white/20 object-cover"
            autoplay
            playsinline
            muted
          ></video>
        </div>
      </div>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import Navbar from '@/Components/Navbar.vue'
import Footer from '@/Components/Footer.vue'

const props = defineProps({
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
  authUser: { type: Object },
  appointmentId: { type: Number, required: true },
  roomId: { type: String, required: true },
  displayName: { type: String, default: '' },
  signalingUrl: { type: String, default: '' },
  role: { type: String, default: 'patient' },
})

const localVideo = ref(null)
const remoteVideo = ref(null)
const error = ref('')

let ws = null
let pc = null
let localStream = null
let pendingIce = []

function resetPeerConnection() {
  try {
    if (pc) pc.close()
  } catch {}
  pc = null

  pendingIce = []

  try {
    if (remoteVideo.value) remoteVideo.value.srcObject = null
  } catch {}
}

async function addIceCandidateBuffered(candidate) {
  if (!candidate) return
  if (!pc || !pc.remoteDescription) {
    pendingIce.push(candidate)
    return
  }
  try {
    await pc.addIceCandidate(candidate)
  } catch {
    // ignore
  }
}

async function flushPendingIce() {
  if (!pc || !pc.remoteDescription || pendingIce.length === 0) return
  const toAdd = pendingIce
  pendingIce = []
  for (const c of toAdd) {
    await addIceCandidateBuffered(c)
  }
}

function getRtcConfig() {
  return {
    iceServers: [{ urls: ['stun:stun.l.google.com:19302'] }],
  }
}

function wsSend(obj) {
  if (!ws || ws.readyState !== WebSocket.OPEN) return
  ws.send(JSON.stringify(obj))
}

async function createPeerConnection() {
  if (pc) return pc
  pc = new RTCPeerConnection(getRtcConfig())

  pc.ontrack = (event) => {
    const [stream] = event.streams
    if (remoteVideo.value && stream) {
      remoteVideo.value.srcObject = stream
    }
  }

  pc.onicecandidate = (event) => {
    if (event.candidate) {
      wsSend({ type: 'ice', candidate: event.candidate })
    }
  }

  if (localStream) {
    for (const track of localStream.getTracks()) {
      pc.addTrack(track, localStream)
    }
  }

  return pc
}

async function startLocalMedia() {
  localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true })
  if (localVideo.value) localVideo.value.srcObject = localStream
}

onMounted(async () => {
  try {
    const roomId = String(props.roomId || '').trim()
    if (!roomId) throw new Error('Missing room id')

    const signalingUrl = String(props.signalingUrl || import.meta.env.VITE_SIGNALING_URL || '').trim()
    if (!signalingUrl) throw new Error('Missing signaling URL (set VITE_SIGNALING_URL)')

    if (!localVideo.value || !remoteVideo.value) throw new Error('Video container not ready')

    await startLocalMedia()

    ws = new WebSocket(signalingUrl)

    ws.onopen = async () => {
      wsSend({ type: 'join', roomId, role: props.role, name: props.displayName || 'Patient' })
    }

    ws.onerror = () => {
      error.value = 'Failed to connect to signaling server.'
    }

    ws.onmessage = async (event) => {
      let msg
      try {
        msg = JSON.parse(String(event.data))
      } catch {
        return
      }

      if (msg.type === 'peer-joined') {
        // Peer joined/re-joined.
        // Do NOT reset here: 'peer-joined' can arrive after a successful offer/answer exchange,
        // and resetting would kill the newly established connection.
      }

      if (msg.type === 'offer') {
        // If we still have an old connection (peer rejoined), rebuild.
        if (pc) resetPeerConnection()
        const peer = await createPeerConnection()
        await peer.setRemoteDescription(new RTCSessionDescription(msg.sdp))
        await flushPendingIce()
        const answer = await peer.createAnswer()
        await peer.setLocalDescription(answer)
        wsSend({ type: 'answer', sdp: answer })
      }

      if (msg.type === 'answer') {
        const peer = await createPeerConnection()
        await peer.setRemoteDescription(new RTCSessionDescription(msg.sdp))
        await flushPendingIce()
      }

      if (msg.type === 'ice') {
        await createPeerConnection()
        await addIceCandidateBuffered(msg.candidate)
      }

      if (msg.type === 'peer-left') {
            // Other side disconnected; reset so a new offer can succeed.
            resetPeerConnection()
      }
    }
  } catch (e) {
    error.value = e?.message ? String(e.message) : 'Failed to start video call.'
  }
})

onBeforeUnmount(() => {
  try {
    if (ws && ws.readyState === WebSocket.OPEN) {
      wsSend({ type: 'leave' })
    }
    if (ws) ws.close()
  } catch {}

  resetPeerConnection()
  ws = null

  try {
    if (localStream) localStream.getTracks().forEach((t) => t.stop())
  } catch {}
  localStream = null
})
</script>

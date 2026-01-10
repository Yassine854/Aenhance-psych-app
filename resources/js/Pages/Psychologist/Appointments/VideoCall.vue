<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
      <div class="min-w-0">
        <h1 class="text-xl font-semibold text-gray-900">Video call</h1>
        <p class="text-sm text-gray-600 truncate">Room: {{ roomId }}</p>
      </div>

      <Link
        :href="route('psychologist.appointments.index')"
        class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        Back
      </Link>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white overflow-hidden" style="height: calc(100vh - 120px)">
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
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import UserLayout from '@/Layouts/UserLayout.vue'

defineOptions({ layout: UserLayout })

const props = defineProps({
  appointmentId: { type: Number, required: true },
  roomId: { type: String, required: true },
  displayName: { type: String, default: '' },
  signalingUrl: { type: String, default: '' },
  role: { type: String, default: 'psychologist' },
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

async function makeOffer() {
  const peer = await createPeerConnection()
  const offer = await peer.createOffer()
  await peer.setLocalDescription(offer)
  wsSend({ type: 'offer', sdp: offer })
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
      wsSend({ type: 'join', roomId, role: props.role, name: props.displayName || 'Psychologist' })
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

      if (msg.type === 'joined') {
        // If patient joined first, we won't receive 'peer-joined' for ourselves.
        // In that case, start the offer immediately.
        if (props.role === 'psychologist' && Number(msg.peerCount || 0) > 1) {
          await createPeerConnection()
          await makeOffer()
        }
      }

      if (msg.type === 'peer-joined') {
        // Psychologist initiates the call.
        await createPeerConnection()
        await makeOffer()
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
        // Other side disconnected; reset so rejoin triggers a clean negotiation.
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

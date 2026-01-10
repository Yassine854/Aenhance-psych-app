<template>
  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-gray-100 bg-gradient-to-r from-white to-gray-50">
      <div class="min-w-0">
        <div class="flex items-center gap-2 min-w-0">
          <div
            class="h-2.5 w-2.5 rounded-full"
            :class="statusDotClass"
            :title="statusLabel"
          ></div>
          <h2 class="text-base sm:text-lg font-semibold text-gray-900 truncate">
            {{ remoteDisplayName }}
          </h2>
          <span v-if="statusLabel === 'Connected' && isRemoteSpeaking" class="inline-flex" title="Speaking">
            <span class="speaking-dots">
              <span></span><span></span><span></span>
            </span>
          </span>
          <div v-if="statusLabel === 'Connected'" class="inline-flex items-center gap-1 text-gray-500">
            <span v-if="remoteAudioEnabled === false" class="inline-flex" title="Muted">
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 1a3 3 0 0 0-3 3v6" />
                <path d="M19 10v2a7 7 0 0 1-11.2 5.6" />
                <path d="M12 19v4" />
                <path d="M23 1 1 23" />
              </svg>
            </span>
            <span v-if="remoteVideoEnabled === false" class="inline-flex" title="Camera off">
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M23 7l-7 5 7 5V7z" />
                <path d="M1 1l22 22" />
                <path d="M14 8H6a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-2" />
              </svg>
            </span>
          </div>
          <span
            class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border"
            :class="statusPillClass"
          >
            {{ statusLabel }}
          </span>
          <span v-if="callDurationLabel" class="inline text-xs text-gray-500">
            • {{ callDurationLabel }}
          </span>
        </div>
        <p class="text-xs text-gray-500 truncate">
          Room: <span class="font-mono">{{ roomId }}</span>
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          class="hidden sm:inline-flex items-center gap-2 h-9 px-3 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
          @click="copyRoomId"
        >
          <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 9h10v10H9z" />
            <path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1" />
          </svg>
          Copy room
        </button>

        <Link
          :href="backHref"
          class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
          @click.prevent="hangUpAndLeave"
        >
          Back
        </Link>
      </div>
    </div>

    <div ref="stageEl" class="relative bg-black" :style="stageStyle">
      <div v-if="error" class="absolute top-0 left-0 right-0 z-20 p-4 text-sm text-red-700 bg-red-50 border-b border-red-200">
        {{ error }}
      </div>

      <video
        ref="remoteVideo"
        class="absolute inset-0 w-full h-full object-cover transition-opacity duration-200"
        :class="showRemotePlaceholder ? 'opacity-0' : 'opacity-100'"
        autoplay
        playsinline
      ></video>

      <div v-if="showRemotePlaceholder" class="absolute inset-0 flex items-center justify-center">
        <div class="text-center text-white">
          <div class="mx-auto h-28 w-28 sm:h-32 sm:w-32 rounded-full bg-white/10 border border-white/15 backdrop-blur flex items-center justify-center shadow-xl">
            <span class="text-4xl sm:text-5xl font-semibold tracking-tight">{{ remoteInitials }}</span>
          </div>
          <div class="mt-3">
            <p class="text-lg sm:text-xl font-semibold">{{ remoteDisplayName }}</p>
            <div class="mt-1 flex items-center justify-center gap-2 text-sm text-white/75">
              <span v-if="remoteRoleLabel">{{ remoteRoleLabel }}</span>
              <span v-if="statusLabel === 'Connected' && remoteAudioEnabled === false" class="inline-flex items-center gap-1">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 1a3 3 0 0 0-3 3v6" />
                  <path d="M19 10v2a7 7 0 0 1-11.2 5.6" />
                  <path d="M12 19v4" />
                  <path d="M23 1 1 23" />
                </svg>
                <span class="text-xs">Muted</span>
              </span>
              <span v-if="statusLabel === 'Connected' && remoteVideoEnabled === false" class="inline-flex items-center gap-1">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M23 7l-7 5 7 5V7z" />
                  <path d="M1 1l22 22" />
                  <path d="M14 8H6a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-2" />
                </svg>
                <span class="text-xs">Camera off</span>
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="absolute inset-0 pointer-events-none bg-gradient-to-t from-black/35 via-black/0 to-black/20"></div>

      <div class="absolute bottom-3 left-3 pointer-events-none">
        <div
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-black/45 text-white text-sm backdrop-blur"
          :class="isRemoteSpeaking ? 'speaking-pulse' : ''"
        >
          <span class="font-semibold truncate max-w-[220px]">{{ remoteDisplayName }}</span>
          <span v-if="remoteRoleLabel" class="text-white/70 text-xs">({{ remoteRoleLabel }})</span>
          <span v-if="statusLabel === 'Connected' && remoteAudioEnabled === false" class="ml-1" title="Muted">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 1a3 3 0 0 0-3 3v6" />
              <path d="M19 10v2a7 7 0 0 1-11.2 5.6" />
              <path d="M12 19v4" />
              <path d="M23 1 1 23" />
            </svg>
          </span>
          <span v-if="statusLabel === 'Connected' && remoteVideoEnabled === false" class="ml-0.5" title="Camera off">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M23 7l-7 5 7 5V7z" />
              <path d="M1 1l22 22" />
              <path d="M14 8H6a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-2" />
            </svg>
          </span>
        </div>
      </div>

      <div
        class="absolute top-3 right-3 w-44 sm:w-56 aspect-video rounded-xl overflow-hidden border border-white/15 shadow-lg"
        :class="isLocalSpeaking ? 'speaking-pulse' : ''"
      >
        <video
          ref="localVideo"
          class="w-full h-full object-cover bg-black transition-opacity duration-200"
          :class="showLocalPlaceholder ? 'opacity-0' : 'opacity-100'"
          autoplay
          playsinline
          muted
        ></video>

        <div v-if="showLocalPlaceholder" class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-900 to-black">
          <div class="text-center text-white">
            <div class="mx-auto h-12 w-12 rounded-full bg-white/10 border border-white/15 flex items-center justify-center">
              <span class="text-base font-semibold">{{ localInitials }}</span>
            </div>
            <p class="mt-2 text-xs font-semibold px-2 max-w-[180px] truncate">{{ localDisplayName }}</p>
          </div>
        </div>

        <div class="absolute inset-0 pointer-events-none bg-gradient-to-t from-black/40 via-black/0 to-black/0"></div>
        <div class="absolute bottom-2 left-2 right-2 flex items-center justify-between gap-2 pointer-events-none">
          <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-black/45 text-white text-xs backdrop-blur min-w-0">
            <span class="font-semibold truncate">{{ localUiName }}</span>
            <span v-if="isLocalSpeaking" class="inline-flex" title="Speaking">
              <span class="speaking-dots">
                <span></span><span></span><span></span>
              </span>
            </span>
          </div>
          <div class="flex items-center gap-1 text-white/90">
            <span v-if="isMuted" title="Muted">
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 5.3 2" />
                <path d="M19 10v2a7 7 0 0 1-11.2 5.6" />
                <path d="M23 1 1 23" />
              </svg>
            </span>
            <span v-if="isVideoOff" title="Camera off">
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M23 7l-7 5 7 5V7z" />
                <path d="M1 1l22 22" />
                <path d="M14 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-3" />
              </svg>
            </span>
          </div>
        </div>
      </div>

      <div class="absolute bottom-4 left-0 right-0 flex items-center justify-center px-4">
        <div class="pointer-events-auto inline-flex items-center gap-2 sm:gap-3 px-3 py-2 rounded-2xl bg-white/10 backdrop-blur border border-white/15 shadow-xl">
          <button
            type="button"
            class="control-btn"
            :class="isMuted ? 'bg-red-600/90 hover:bg-red-600 text-white border-red-500/40' : 'bg-white/10 hover:bg-white/15 text-white border-white/15'"
            :title="isMuted ? 'Unmute' : 'Mute'"
            @click="toggleMute"
          >
            <svg v-if="!isMuted" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z" />
              <path d="M19 10v2a7 7 0 0 1-14 0v-2" />
              <path d="M12 19v4" />
            </svg>
            <svg v-else viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 1a3 3 0 0 0-3 3v6" />
              <path d="M19 10v2a7 7 0 0 1-11.2 5.6" />
              <path d="M12 19v4" />
              <path d="M23 1 1 23" />
            </svg>
          </button>

          <button
            type="button"
            class="control-btn"
            :class="isVideoOff ? 'bg-red-600/90 hover:bg-red-600 text-white border-red-500/40' : 'bg-white/10 hover:bg-white/15 text-white border-white/15'"
            :title="isVideoOff ? 'Turn camera on' : 'Turn camera off'"
            @click="toggleVideo"
          >
            <svg v-if="!isVideoOff" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 8H6a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2z" />
              <path d="M22 10l-4 3 4 3v-6z" />
            </svg>
            <svg v-else viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M23 7l-7 5 7 5V7z" />
              <path d="M1 1l22 22" />
              <path d="M14 8H6a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-2" />
            </svg>
          </button>

          <button
            type="button"
            class="control-btn bg-white/10 hover:bg-white/15 text-white border-white/15"
            :title="isFullscreen ? 'Minimize (exit fullscreen)' : 'Fullscreen'"
            @click="toggleFullscreen"
          >
            <svg v-if="!isFullscreen" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 3H5a2 2 0 0 0-2 2v4" />
              <path d="M15 3h4a2 2 0 0 1 2 2v4" />
              <path d="M9 21H5a2 2 0 0 1-2-2v-4" />
              <path d="M15 21h4a2 2 0 0 0 2-2v-4" />
            </svg>
            <svg v-else viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 9H5V5" />
              <path d="M15 9h4V5" />
              <path d="M9 15H5v4" />
              <path d="M15 15h4v4" />
            </svg>
          </button>

          <button
            v-if="role === 'psychologist'"
            type="button"
            class="control-btn"
            :class="(isEndingSession || sessionStatus === 'completed' || callEndedAt) ? 'bg-amber-500/60 text-white border-amber-400/30 cursor-not-allowed' : 'bg-amber-500 hover:bg-amber-600 text-white border-amber-400/40'"
            :disabled="isEndingSession || sessionStatus === 'completed' || Boolean(callEndedAt)"
            :title="sessionStatus === 'completed' || callEndedAt ? 'Session ended' : 'End session'"
            @click="endSession"
          >
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 6h12v12H6z" />
            </svg>
          </button>

          <button
            type="button"
            class="control-btn bg-red-600 hover:bg-red-700 text-white border-red-500/40"
            title="Hang up"
            @click="hangUpAndLeave"
          >
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4.5 10.5c5-5 10-5 15 0" />
              <path d="M8 14.5v3a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-3" />
              <path d="M22 14.5v3a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2v-3" />
            </svg>
          </button>
        </div>
      </div>

      <div v-if="statusLabel === 'Waiting'" class="absolute inset-0 flex items-center justify-center px-6 pointer-events-none">
        <div class="max-w-md w-full rounded-2xl bg-black/55 border border-white/10 backdrop-blur p-6 text-center text-white">
          <div class="mx-auto h-10 w-10 rounded-full bg-white/10 flex items-center justify-center mb-3">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 20v-6" />
              <path d="M12 14l4-4" />
              <path d="M12 14l-4-4" />
              <path d="M20 12a8 8 0 1 0-16 0" />
            </svg>
          </div>
          <p class="font-semibold">Waiting for the other participant…</p>
          <p class="text-sm text-white/75 mt-1">Keep this tab open. The call will connect automatically.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const props = defineProps({
  appointmentId: { type: Number, required: true },
  roomId: { type: String, required: true },
  displayName: { type: String, default: '' },
  signalingUrl: { type: String, default: '' },
  role: { type: String, default: 'patient' },
  backHref: { type: String, required: true },
  heightOffset: { type: Number, default: 190 },
})

const stageEl = ref(null)
const localVideo = ref(null)
const remoteVideo = ref(null)

const error = ref('')
const isMuted = ref(false)
const isVideoOff = ref(false)
const isFullscreen = ref(false)

const isLocalSpeaking = ref(false)
const isRemoteSpeaking = ref(false)

const remoteName = ref('')
// Default the remote role based on who is viewing this page.
// Patient call screen: remote is the psychologist.
// Psychologist call screen: remote is the patient.
const remoteRole = ref(props.role === 'patient' ? 'psychologist' : 'patient')
const remoteVideoAvailable = ref(false)
const remoteVideoEnabled = ref(true)
const remoteAudioEnabled = ref(true)
const remoteVideoRenderable = ref(false)

const connectionState = ref('connecting')
const callStartedAt = ref(null)
const callEndedAt = ref(null)
const callDurationSec = ref(0)
let callTimer = null

const sessionStatus = ref('')
const isEndingSession = ref(false)
let hasJoinedSession = false

let ws = null
let pc = null
let localStream = null
let pendingIce = []
let pendingMediaState = null

let localVideoSender = null
let makingOffer = false

let audioCtx = null
let localAnalyser = null
let localTimeData = null
let remoteAnalyser = null
let remoteTimeData = null
let speakingRaf = null

let localSpeakOn = 0
let localSpeakOff = 0
let remoteSpeakOn = 0
let remoteSpeakOff = 0

const page = usePage()
const authUser = computed(() => page.props?.value?.auth?.user ?? page.props?.auth?.user ?? null)

const localBaseName = computed(() => {
  const fromProp = String(props.displayName || '').trim()
  if (fromProp) return fromProp

  const fromAuth = String(authUser.value?.name || '').trim()
  if (fromAuth) return fromAuth

  const fromEmail = String(authUser.value?.email || '').trim()
  if (fromEmail) return fromEmail

  return ''
})

const localDisplayName = computed(() => localBaseName.value || (props.role === 'psychologist' ? 'You (Psychologist)' : 'You (Patient)'))

const localUiName = computed(() => 'You')

const localSignalingName = computed(() => {
  const clean = String(localBaseName.value || '').trim()
  if (clean) return clean
  return props.role === 'psychologist' ? 'Psychologist' : 'Patient'
})

function initialsFromName(name) {
  const clean = String(name || '').trim()
  if (!clean) return '?'
  const parts = clean.split(/\s+/).filter(Boolean)
  const letters = parts.slice(0, 2).map((p) => p[0]).join('')
  return letters.toUpperCase() || '?'
}

const localInitials = computed(() => initialsFromName(localDisplayName.value))
const remoteInitials = computed(() => initialsFromName(remoteDisplayName.value))

const remoteRoleLabel = computed(() => {
  const r = String(remoteRole.value || '').trim()
  if (!r) return ''
  return r === 'psychologist' ? 'Psychologist' : r === 'patient' ? 'Patient' : r
})

const remoteDisplayName = computed(() => {
  if (remoteName.value) return remoteName.value
  if (connectionState.value === 'waiting') return 'Waiting…'
  // Never show "Participant"; fall back to role label.
  if (remoteRoleLabel.value) return remoteRoleLabel.value
  return props.role === 'patient' ? 'Psychologist' : 'Patient'
})

const statusLabel = computed(() => {
  if (connectionState.value === 'connected') return 'Connected'
  if (connectionState.value === 'waiting') return 'Waiting'
  if (connectionState.value === 'failed') return 'Failed'
  return 'Connecting'
})

const showLocalPlaceholder = computed(() => {
  // Users asked to avoid a black box when they turn camera off.
  return Boolean(isVideoOff.value)
})

const showRemotePlaceholder = computed(() => {
  // Connected: show placeholder if remote camera is off OR we are not rendering frames.
  if (statusLabel.value === 'Connected') {
    return remoteVideoEnabled.value === false || remoteVideoAvailable.value === false || remoteVideoRenderable.value === false
  }
  // Waiting/Connecting/Failed: always show a pleasant placeholder instead of black.
  return true
})

const statusDotClass = computed(() => {
  switch (statusLabel.value) {
    case 'Connected':
      return 'bg-emerald-500'
    case 'Waiting':
      return 'bg-amber-400'
    case 'Failed':
      return 'bg-red-500'
    default:
      return 'bg-blue-500 animate-pulse'
  }
})

const statusPillClass = computed(() => {
  switch (statusLabel.value) {
    case 'Connected':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200'
    case 'Waiting':
      return 'bg-amber-50 text-amber-700 border-amber-200'
    case 'Failed':
      return 'bg-red-50 text-red-700 border-red-200'
    default:
      return 'bg-blue-50 text-blue-700 border-blue-200'
  }
})

const callDurationLabel = computed(() => {
  if (!callStartedAt.value) return ''
  const total = Math.max(0, callDurationSec.value)
  const mm = String(Math.floor(total / 60)).padStart(2, '0')
  const ss = String(total % 60).padStart(2, '0')
  return `${mm}:${ss}`
})

const stageStyle = computed(() => ({
  height: `calc(100vh - ${Number(props.heightOffset || 190)}px)`,
}))

function resetPeerConnection() {
  try {
    if (pc) pc.close()
  } catch {}
  pc = null

  localVideoSender = null
  makingOffer = false

  pendingIce = []

  try {
    if (remoteVideo.value) remoteVideo.value.srcObject = null
  } catch {}

  remoteVideoAvailable.value = false
  remoteVideoEnabled.value = true
  remoteAudioEnabled.value = true
  remoteVideoRenderable.value = false
}

function stopSpeakingDetection() {
  try {
    if (speakingRaf) cancelAnimationFrame(speakingRaf)
  } catch {}
  speakingRaf = null

  isLocalSpeaking.value = false
  isRemoteSpeaking.value = false

  localSpeakOn = 0
  localSpeakOff = 0
  remoteSpeakOn = 0
  remoteSpeakOff = 0

  try {
    localAnalyser?.disconnect?.()
  } catch {}
  try {
    remoteAnalyser?.disconnect?.()
  } catch {}

  localAnalyser = null
  localTimeData = null
  remoteAnalyser = null
  remoteTimeData = null

  try {
    audioCtx?.close?.()
  } catch {}
  audioCtx = null
}

async function ensureAudioContext() {
  try {
    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)()
    if (audioCtx && audioCtx.state === 'suspended') {
      try {
        await audioCtx.resume()
      } catch {
        // ignore
      }
    }
    return audioCtx
  } catch {
    return null
  }
}

function setupLocalSpeakingAnalyser() {
  try {
    if (!localStream) return
    const hasAudio = (localStream.getAudioTracks?.() || []).length > 0
    if (!hasAudio) return
    if (localAnalyser) return

    const ctx = audioCtx
    if (!ctx) return

    const src = ctx.createMediaStreamSource(localStream)
    const analyser = ctx.createAnalyser()
    analyser.fftSize = 1024
    analyser.smoothingTimeConstant = 0.8
    src.connect(analyser)

    localAnalyser = analyser
    localTimeData = new Uint8Array(analyser.fftSize)
  } catch {
    // ignore
  }
}

function setupRemoteSpeakingAnalyser(stream) {
  try {
    if (!stream) return
    const hasAudio = (stream.getAudioTracks?.() || []).length > 0
    if (!hasAudio) return

    const ctx = audioCtx
    if (!ctx) return

    // Recreate remote analyser if stream changes.
    try {
      remoteAnalyser?.disconnect?.()
    } catch {}
    remoteAnalyser = null
    remoteTimeData = null

    const src = ctx.createMediaStreamSource(stream)
    const analyser = ctx.createAnalyser()
    analyser.fftSize = 1024
    analyser.smoothingTimeConstant = 0.8
    src.connect(analyser)

    remoteAnalyser = analyser
    remoteTimeData = new Uint8Array(analyser.fftSize)
  } catch {
    // ignore
  }
}

function rmsFromTimeDomain(buffer) {
  let sum = 0
  const n = buffer.length || 1
  for (let i = 0; i < n; i++) {
    const v = (buffer[i] - 128) / 128
    sum += v * v
  }
  return Math.sqrt(sum / n)
}

function stepSpeakingDetection() {
  // This is an activity indicator (energy-based), not a perfect voice detector.
  const ON = 0.06
  const OFF = 0.04
  const ON_FRAMES = 3
  const OFF_FRAMES = 8

  try {
    // Local
    if (localAnalyser && localTimeData && !isMuted.value) {
      localAnalyser.getByteTimeDomainData(localTimeData)
      const level = rmsFromTimeDomain(localTimeData)
      if (level > ON) {
        localSpeakOn++
        localSpeakOff = 0
      } else if (level < OFF) {
        localSpeakOff++
        localSpeakOn = 0
      }
      if (!isLocalSpeaking.value && localSpeakOn >= ON_FRAMES) isLocalSpeaking.value = true
      if (isLocalSpeaking.value && localSpeakOff >= OFF_FRAMES) isLocalSpeaking.value = false
    } else {
      isLocalSpeaking.value = false
      localSpeakOn = 0
      localSpeakOff = 0
    }

    // Remote
    if (remoteAnalyser && remoteTimeData && remoteAudioEnabled.value !== false) {
      remoteAnalyser.getByteTimeDomainData(remoteTimeData)
      const level = rmsFromTimeDomain(remoteTimeData)
      if (level > ON) {
        remoteSpeakOn++
        remoteSpeakOff = 0
      } else if (level < OFF) {
        remoteSpeakOff++
        remoteSpeakOn = 0
      }
      if (!isRemoteSpeaking.value && remoteSpeakOn >= ON_FRAMES) isRemoteSpeaking.value = true
      if (isRemoteSpeaking.value && remoteSpeakOff >= OFF_FRAMES) isRemoteSpeaking.value = false
    } else {
      isRemoteSpeaking.value = false
      remoteSpeakOn = 0
      remoteSpeakOff = 0
    }
  } catch {
    // ignore
  }

  speakingRaf = requestAnimationFrame(stepSpeakingDetection)
}

function startSpeakingDetectionLoop() {
  if (speakingRaf) return
  speakingRaf = requestAnimationFrame(stepSpeakingDetection)
}

function updateRemoteVideoRenderable() {
  try {
    const el = remoteVideo.value
    if (!el) {
      remoteVideoRenderable.value = false
      return
    }
    // If the video element has dimensions, it has decoded/rendered at least a frame.
    remoteVideoRenderable.value = Boolean(el.videoWidth > 0 && el.videoHeight > 0)
  } catch {
    remoteVideoRenderable.value = false
  }
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

function wsSendOrQueue(obj) {
  if (!ws || ws.readyState !== WebSocket.OPEN) {
    if (obj?.type === 'media') pendingMediaState = obj
    return
  }
  ws.send(JSON.stringify(obj))
}

function getMetaCsrfToken() {
  try {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
  } catch {
    return ''
  }
}

function getXsrfCookieToken() {
  try {
    const m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/)
    if (m && m[1]) return decodeURIComponent(m[1])
    return ''
  } catch {
    return ''
  }
}

async function fetchJson(url, options = {}) {
  const doFetch = async () => {
    const metaToken = getMetaCsrfToken()
    const xsrfToken = getXsrfCookieToken()
    const isWrite = Boolean(options.method && options.method !== 'GET')

    const headers = {
      'X-Requested-With': 'XMLHttpRequest',
      Accept: 'application/json',
      ...(options.headers || {}),
    }

    // Laravel accepts either X-CSRF-TOKEN (raw token) or X-XSRF-TOKEN (cookie token).
    // Sending both makes this resilient to cases where the meta token is stale but the cookie is refreshed.
    if (isWrite && metaToken) headers['X-CSRF-TOKEN'] = metaToken
    if (isWrite && xsrfToken) headers['X-XSRF-TOKEN'] = xsrfToken

    return fetch(url, {
      // Use include to be resilient even if the app is served from a different host/port in dev.
      credentials: 'include',
      headers,
      ...options,
    })
  }

  const parse = async (res) => {
    const text = await res.text()
    let data = null
    try {
      data = text ? JSON.parse(text) : null
    } catch {
      data = null
    }
    return { res, data }
  }

  let first = await doFetch()
  let { res, data } = await parse(first)

  if (res.status === 419) {
    // Try to refresh CSRF cookies (Sanctum) and retry once.
    try {
      await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' })
    } catch {
      // ignore
    }

    // Give the browser a moment to persist cookies.
    try {
      await new Promise((r) => setTimeout(r, 50))
    } catch {
      // ignore
    }

    const second = await doFetch()
    ;({ res, data } = await parse(second))
  }

  if (!res.ok) {
    const msg = data?.message || `Request failed (${res.status})`
    throw new Error(msg)
  }

  return data
}

function applySession(session) {
  if (!session || typeof session !== 'object') return
  sessionStatus.value = String(session.status || '')

  const startedAt = session.started_at ? Date.parse(String(session.started_at)) : NaN
  const endedAt = session.ended_at ? Date.parse(String(session.ended_at)) : NaN

  if (!Number.isNaN(startedAt)) {
    callStartedAt.value = startedAt
  }
  if (!Number.isNaN(endedAt)) {
    callEndedAt.value = endedAt
  }

  // Drive the duration timer based on session times.
  if (callTimer) {
    clearInterval(callTimer)
    callTimer = null
  }

  if (callStartedAt.value && !callEndedAt.value) {
    callDurationSec.value = Math.floor((Date.now() - callStartedAt.value) / 1000)
    callTimer = setInterval(() => {
      callDurationSec.value = Math.floor((Date.now() - callStartedAt.value) / 1000)
    }, 1000)
  } else if (callStartedAt.value && callEndedAt.value) {
    callDurationSec.value = Math.max(0, Math.floor((callEndedAt.value - callStartedAt.value) / 1000))
  } else {
    callDurationSec.value = 0
  }
}

async function joinSessionBackend() {
  if (!props.appointmentId) return
  try {
    const data = await fetchJson(`/appointments/${props.appointmentId}/session/join`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ roomId: String(props.roomId || '') }),
    })
    if (data?.session) applySession(data.session)
    hasJoinedSession = true
  } catch (e) {
    // Don't block the call UI; show non-fatal error.
    error.value = e?.message ? String(e.message) : 'Failed to join session.'
  }
}

async function leaveSessionBackend() {
  if (!props.appointmentId) return
  if (!hasJoinedSession) return
  try {
    await fetchJson(`/appointments/${props.appointmentId}/session/leave`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({}),
    })
  } catch {
    // ignore
  }
}

async function syncSessionBackend() {
  if (!props.appointmentId) return
  try {
    const data = await fetchJson(`/appointments/${props.appointmentId}/session`)
    if (data?.session) applySession(data.session)
  } catch {
    // ignore
  }
}

function setConnectionState(next) {
  connectionState.value = next
}

async function createPeerConnection() {
  if (pc) return pc
  pc = new RTCPeerConnection(getRtcConfig())

  pc.ontrack = (event) => {
    const [stream] = event.streams
    if (remoteVideo.value && stream) {
      remoteVideo.value.srcObject = stream
    }

    // Remote speaking indicator (if audio track exists)
    try {
      ensureAudioContext().then(() => {
        setupRemoteSpeakingAnalyser(stream)
        startSpeakingDetectionLoop()
      })
    } catch {
      // ignore
    }

    // Update element-based rendering state.
    updateRemoteVideoRenderable()

    // Track remote video availability so we can show initials/name when camera is off.
    try {
      const [videoTrack] = stream?.getVideoTracks?.() || []
      if (videoTrack) {
        const update = () => {
          remoteVideoAvailable.value = Boolean(videoTrack.enabled && !videoTrack.muted && videoTrack.readyState === 'live')
          updateRemoteVideoRenderable()
        }
        videoTrack.onmute = update
        videoTrack.onunmute = update
        videoTrack.onended = update
        update()
      } else {
        remoteVideoAvailable.value = false
        updateRemoteVideoRenderable()
      }
    } catch {
      remoteVideoAvailable.value = false
      updateRemoteVideoRenderable()
    }
  }

  pc.onicecandidate = (event) => {
    if (event.candidate) {
      wsSend({ type: 'ice', candidate: event.candidate })
    }
  }

  pc.onconnectionstatechange = () => {
    const st = pc?.connectionState
    if (st === 'connected') setConnectionState('connected')
    else if (st === 'failed' || st === 'disconnected') setConnectionState('failed')
    else setConnectionState('connecting')
  }

  pc.onnegotiationneeded = async () => {
    // We keep the "psychologist offers" rule to avoid offer glare.
    if (props.role !== 'psychologist') return
    if (!ws || ws.readyState !== WebSocket.OPEN) return
    if (!pc || pc.signalingState !== 'stable') return
    if (makingOffer) return

    makingOffer = true
    try {
      await makeOffer()
    } catch {
      // ignore
    } finally {
      makingOffer = false
    }
  }

  if (localStream) {
    for (const track of localStream.getTracks()) {
      const sender = pc.addTrack(track, localStream)
      if (track.kind === 'video') localVideoSender = sender
    }
  }

  return pc
}

async function startLocalMedia() {
  localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true })

  // Apply initial toggle states.
  const audioTracks = localStream.getAudioTracks()
  const videoTracks = localStream.getVideoTracks()
  audioTracks.forEach((t) => (t.enabled = !isMuted.value))
  videoTracks.forEach((t) => (t.enabled = !isVideoOff.value))

  if (localVideo.value) localVideo.value.srcObject = localStream

  await ensureAudioContext()
  setupLocalSpeakingAnalyser()
  startSpeakingDetectionLoop()
}

function stopAndRemoveLocalVideoTracks() {
  try {
    if (!localStream) return
    const tracks = localStream.getVideoTracks()
    for (const t of tracks) {
      try {
        localStream.removeTrack(t)
      } catch {
        // ignore
      }
      try {
        t.stop()
      } catch {
        // ignore
      }
    }
  } catch {
    // ignore
  }
}

async function ensureLocalVideoTrack() {
  if (!localStream) return null
  const existing = localStream.getVideoTracks()[0]
  if (existing) return existing

  const camStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false })
  const track = camStream.getVideoTracks()[0]
  if (!track) return null

  localStream.addTrack(track)
  if (localVideo.value) localVideo.value.srcObject = localStream
  return track
}

async function makeOffer() {
  const peer = await createPeerConnection()
  const offer = await peer.createOffer()
  await peer.setLocalDescription(offer)
  wsSend({ type: 'offer', sdp: offer })
}

function setRemoteIdentity(payload) {
  if (!payload || typeof payload !== 'object') return
  const nm = String(payload.name || '').trim()
  const rl = String(payload.role || '').trim()
  if (nm) remoteName.value = nm
  if (rl) remoteRole.value = rl

  const media = payload.media && typeof payload.media === 'object' ? payload.media : null
  if (media) {
    remoteVideoEnabled.value = media.videoEnabled !== false
    remoteAudioEnabled.value = media.audioEnabled !== false
  }
}

function sendMediaState() {
  wsSendOrQueue({ type: 'media', audioEnabled: !isMuted.value, videoEnabled: !isVideoOff.value })
}

function toggleMute() {
  isMuted.value = !isMuted.value
  try {
    if (localStream) localStream.getAudioTracks().forEach((t) => (t.enabled = !isMuted.value))
  } catch {}

  // Help browsers that suspend AudioContext until a user gesture.
  try {
    ensureAudioContext()
  } catch {}

  sendMediaState()
}

async function toggleVideo() {
  const nextOff = !isVideoOff.value
  isVideoOff.value = nextOff

  // Important: setting track.enabled=false usually sends black frames.
  // To truly hide video on the remote side, stop/remove the track and replace it with null.
  if (nextOff) {
    try {
      if (pc) {
        if (localVideoSender) {
          try {
            await localVideoSender.replaceTrack(null)
          } catch {
            // ignore
          }
        } else {
          for (const sender of pc.getSenders()) {
            if (sender.track && sender.track.kind === 'video') {
              try {
                await sender.replaceTrack(null)
              } catch {
                // ignore
              }
            }
          }
        }
      }
    } catch {
      // ignore
    }

    stopAndRemoveLocalVideoTracks()
    sendMediaState()
    return
  }

  // Turn camera back on.
  try {
    const track = await ensureLocalVideoTrack()
    if (!track) {
      sendMediaState()
      return
    }

    if (pc) {
      if (localVideoSender) {
        try {
          await localVideoSender.replaceTrack(track)
        } catch {
          // ignore
        }
      } else {
        try {
          // Fallback: if we somehow don't have a cached sender, adding a track may require renegotiation.
          localVideoSender = pc.addTrack(track, localStream)
        } catch {
          // ignore
        }
      }
    }
  } catch {
    // ignore
  }

  sendMediaState()
}

async function toggleFullscreen() {
  try {
    const el = stageEl.value
    if (!el) return

    if (!document.fullscreenElement) {
      await el.requestFullscreen()
      isFullscreen.value = true
      return
    }

    await document.exitFullscreen()
    isFullscreen.value = false
  } catch {
    // ignore
  }
}

async function copyRoomId() {
  try {
    await navigator.clipboard.writeText(String(props.roomId))
  } catch {
    // ignore
  }
}

function hangUp() {
  try {
    // Mark presence off in backend (best-effort)
    leaveSessionBackend()
  } catch {}

  try {
    if (ws && ws.readyState === WebSocket.OPEN) {
      wsSend({ type: 'leave' })
    }
    if (ws) ws.close()
  } catch {}

  resetPeerConnection()
  ws = null

  stopSpeakingDetection()

  try {
    if (localStream) localStream.getTracks().forEach((t) => t.stop())
  } catch {}
  localStream = null

  try {
    if (callTimer) clearInterval(callTimer)
  } catch {}
  callTimer = null

  setConnectionState('waiting')
}

async function endSession() {
  if (props.role !== 'psychologist') return
  if (isEndingSession.value) return

  const res = await Swal.fire({
    title: 'End session?',
    text: 'This will mark the session as completed and end the call for both participants.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'End session',
    cancelButtonText: 'Cancel',
    reverseButtons: true,
    focusCancel: true,
  })

  if (!res.isConfirmed) return

  isEndingSession.value = true
  try {
    const data = await fetchJson(`/appointments/${props.appointmentId}/session/end`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({}),
    })
    if (data?.session) applySession(data.session)

    // Notify the other participant via signaling (best-effort).
    try {
      wsSend({ type: 'session-ended', endedAt: data?.session?.ended_at || null })
    } catch {
      // ignore
    }

    hangUpAndLeave()
  } catch (e) {
    error.value = e?.message ? String(e.message) : 'Failed to end session.'
  } finally {
    isEndingSession.value = false
  }
}

function hangUpAndLeave() {
  hangUp()
  router.visit(props.backHref)
}

onMounted(async () => {
  try {
    // Track session presence immediately.
    await joinSessionBackend()

    const roomId = String(props.roomId || '').trim()
    if (!roomId) throw new Error('Missing room id')

    const signalingUrl = String(props.signalingUrl || import.meta.env.VITE_SIGNALING_URL || '').trim()
    if (!signalingUrl) throw new Error('Missing signaling URL (set VITE_SIGNALING_URL)')

    if (!localVideo.value || !remoteVideo.value) throw new Error('Video container not ready')

    setConnectionState('connecting')
    await startLocalMedia()

    ws = new WebSocket(signalingUrl)

    ws.onopen = async () => {
      wsSend({ type: 'join', roomId, role: props.role, name: localSignalingName.value })
      sendMediaState()

      // Sync session start in case the other participant already joined.
      syncSessionBackend()

      if (pendingMediaState) {
        wsSendOrQueue(pendingMediaState)
        pendingMediaState = null
      }
    }

    ws.onerror = () => {
      error.value = 'Failed to connect to signaling server.'
      setConnectionState('failed')
    }

    ws.onmessage = async (event) => {
      let msg
      try {
        msg = JSON.parse(String(event.data))
      } catch {
        return
      }

      if (msg.type === 'joined') {
        // If someone is already in the room, capture their identity and (if psychologist) start offer.
        if (Array.isArray(msg.peers) && msg.peers.length > 0) {
          setRemoteIdentity(msg.peers[0])
          setConnectionState('connecting')
        } else {
          setConnectionState('waiting')
        }

        if (props.role === 'psychologist' && Number(msg.peerCount || 0) > 1) {
          await createPeerConnection()
          await makeOffer()
        }

        // If the other peer is already present, the session might have started.
        syncSessionBackend()
      }

      if (msg.type === 'peer-joined') {
        setRemoteIdentity(msg)
        setConnectionState('connecting')

        // Second participant joined; backend will set started_at.
        syncSessionBackend()

        if (props.role === 'psychologist') {
          await createPeerConnection()
          await makeOffer()
        }
      }

      if (msg.type === 'offer') {
        setRemoteIdentity(msg.from || msg)
        if (pc) resetPeerConnection()
        const peer = await createPeerConnection()
        await peer.setRemoteDescription(new RTCSessionDescription(msg.sdp))
        await flushPendingIce()
        const answer = await peer.createAnswer()
        await peer.setLocalDescription(answer)
        wsSend({ type: 'answer', sdp: answer })
      }

      if (msg.type === 'answer') {
        setRemoteIdentity(msg.from || msg)
        const peer = await createPeerConnection()
        await peer.setRemoteDescription(new RTCSessionDescription(msg.sdp))
        await flushPendingIce()
      }

      if (msg.type === 'ice') {
        setRemoteIdentity(msg.from || msg)
        await createPeerConnection()
        await addIceCandidateBuffered(msg.candidate)
      }

      if (msg.type === 'peer-left') {
        setConnectionState('waiting')
        remoteName.value = ''
        remoteRole.value = ''
        remoteVideoEnabled.value = true
        remoteAudioEnabled.value = true
        resetPeerConnection()
      }

      if (msg.type === 'media') {
        // Remote toggled mic/camera.
        setRemoteIdentity(msg.from || msg)
        updateRemoteVideoRenderable()
      }

      if (msg.type === 'session-ended') {
        // Psychologist ended the session.
        await syncSessionBackend()
        error.value = 'Session ended by the psychologist.'
        setTimeout(() => {
          try {
            hangUpAndLeave()
          } catch {
            // ignore
          }
        }, 1200)
      }

      if (msg.type === 'error') {
        error.value = String(msg.message || 'Signaling error.')
      }
    }

    const onFsChange = () => {
      isFullscreen.value = Boolean(document.fullscreenElement)
    }
    document.addEventListener('fullscreenchange', onFsChange)

    const onRemoteVideoMeta = () => updateRemoteVideoRenderable()
    try {
      remoteVideo.value?.addEventListener('loadedmetadata', onRemoteVideoMeta)
      remoteVideo.value?.addEventListener('resize', onRemoteVideoMeta)
      remoteVideo.value?.addEventListener('playing', onRemoteVideoMeta)
    } catch {
      // ignore
    }

    onBeforeUnmount(() => {
      document.removeEventListener('fullscreenchange', onFsChange)
      try {
        remoteVideo.value?.removeEventListener('loadedmetadata', onRemoteVideoMeta)
        remoteVideo.value?.removeEventListener('resize', onRemoteVideoMeta)
        remoteVideo.value?.removeEventListener('playing', onRemoteVideoMeta)
      } catch {
        // ignore
      }
    })
  } catch (e) {
    error.value = e?.message ? String(e.message) : 'Failed to start video call.'
    setConnectionState('failed')
  }
})

onBeforeUnmount(() => {
  try {
    hangUp()
  } catch {}
})
</script>

<style scoped>
.control-btn {
  height: 44px;
  width: 44px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-width: 1px;
  transition: background-color 150ms ease, transform 150ms ease;
}
.control-btn:active {
  transform: translateY(1px);
}

.speaking-pulse {
  animation: speakingPulse 1200ms ease-in-out infinite;
}

@keyframes speakingPulse {
  0%,
  100% {
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
  }
  45% {
    box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.35);
  }
}

.speaking-dots {
  display: inline-flex;
  align-items: flex-end;
  gap: 2px;
}

.speaking-dots span {
  width: 3px;
  border-radius: 9999px;
  background: rgba(16, 185, 129, 0.95);
  animation: speakingDot 900ms ease-in-out infinite;
}

.speaking-dots span:nth-child(1) {
  height: 6px;
  animation-delay: 0ms;
}

.speaking-dots span:nth-child(2) {
  height: 10px;
  animation-delay: 120ms;
}

.speaking-dots span:nth-child(3) {
  height: 7px;
  animation-delay: 240ms;
}

@keyframes speakingDot {
  0%,
  100% {
    transform: scaleY(0.7);
    opacity: 0.6;
  }
  50% {
    transform: scaleY(1.2);
    opacity: 1;
  }
}
</style>

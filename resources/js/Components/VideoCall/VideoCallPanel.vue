<template>
  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden flex relative">
    <div class="flex-1 min-w-0 flex flex-col">
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
          <span v-if="connectionState === 'connected' && isRemoteSpeaking" class="inline-flex" :title="t('videoCall.speaking')">
            <span class="speaking-dots">
              <span></span><span></span><span></span>
            </span>
          </span>
          <div v-if="connectionState === 'connected'" class="inline-flex items-center gap-1 text-gray-500">
            <span v-if="remoteAudioEnabled === false" class="inline-flex" :title="t('videoCall.muted')">
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 1a3 3 0 0 0-3 3v6" />
                <path d="M19 10v2a7 7 0 0 1-11.2 5.6" />
                <path d="M12 19v4" />
                <path d="M23 1 1 23" />
              </svg>
            </span>
            <span v-if="remoteVideoEnabled === false" class="inline-flex" :title="t('videoCall.cameraOff')">
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
          <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border border-gray-200 bg-gray-50 text-gray-700">
            ICE: {{ iceModeLabel }}
          </span>
          <span v-if="callDurationLabel" class="inline text-xs text-gray-500">
            • {{ callDurationLabel }}
          </span>
        </div>
        <p class="text-xs text-gray-500 truncate">
          {{ t('videoCall.room') }}: <span class="font-mono">{{ roomId }}</span>
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
              {{ t('videoCall.copyRoom') }}
            </button>

            <button
              type="button"
              class="hidden sm:inline-flex items-center gap-2 h-9 px-3 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
              :title="remoteViewMode === 'cover' ? t('videoCall.viewMode.cover') : t('videoCall.viewMode.fit')"
              @click="toggleRemoteViewMode"
            >
              <svg v-if="remoteViewMode === 'cover'" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h10v10H7z"/></svg>
              <svg v-else viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 12h18M12 3v18"/></svg>
            </button>

            <Link
              :href="backHref"
              class="inline-flex items-center justify-center h-8 sm:h-9 px-2 sm:px-3 rounded-lg border border-gray-200 bg-white text-xs sm:text-sm font-medium text-gray-700 hover:bg-gray-50 gap-2 min-w-[40px] sm:min-w-0"
              :title="t('videoCall.back')"
              @click.prevent="hangUpAndLeave"
            >
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M15 19l-7-7 7-7" />
              </svg>
              <span class="hidden sm:inline truncate">{{ t('videoCall.back') }}</span>
            </Link>
      </div>
    </div>

    <div ref="stageEl" class="relative bg-black" :style="stageStyle">
      <div v-if="error" role="alert" class="absolute top-0 left-0 right-0 z-20 flex items-start gap-3 p-4 text-sm text-red-700 bg-red-50 border-b border-red-200">
        <span class="min-w-0 flex-1 break-words">{{ error }}</span>
        <button
          type="button"
          class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md hover:bg-red-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500"
          :aria-label="t('videoCall.dismissError')"
          :title="t('videoCall.dismissError')"
          @click="error = ''"
        >
          <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <path d="m6 6 12 12M18 6 6 18" />
          </svg>
        </button>
      </div>

      <video
        ref="remoteVideo"
        :class="[
          'absolute inset-0 w-full h-full transition-opacity duration-200 bg-black',
          remoteViewMode === 'cover' ? 'object-cover' : 'object-contain',
          showRemotePlaceholder ? 'opacity-0' : 'opacity-100'
        ]"
        :style="remoteViewMode === 'cover' ? { objectPosition: 'center' } : {}"
        autoplay
        playsinline
      ></video>

      <div v-if="showRemotePlaceholder" class="absolute inset-0 flex items-center justify-center remote-placeholder-container">
        <div class="text-center text-white">
          <div class="mx-auto remote-placeholder-avatar rounded-full bg-white/10 border border-white/15 backdrop-blur flex items-center justify-center shadow-xl">
            <span class="remote-placeholder-initials font-semibold tracking-tight">{{ remoteInitials }}</span>
          </div>
          <div class="mt-3">
            <p class="remote-placeholder-name font-semibold truncate">{{ remoteDisplayName }}</p>
            <div class="mt-1 flex items-center justify-center gap-2 remote-placeholder-meta text-white/75">
              <span v-if="remoteRoleLabel" class="remote-role-label">{{ remoteRoleLabel }}</span>
              <span v-if="connectionState === 'connected' && remoteAudioEnabled === false" class="inline-flex items-center gap-1">
                <svg viewBox="0 0 24 24" class="remote-placeholder-icon" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 1a3 3 0 0 0-3 3v6" />
                  <path d="M19 10v2a7 7 0 0 1-11.2 5.6" />
                  <path d="M12 19v4" />
                  <path d="M23 1 1 23" />
                </svg>
                <span class="remote-placeholder-meta-text">{{ t('videoCall.muted') }}</span>
              </span>
              <span v-if="connectionState === 'connected' && remoteVideoEnabled === false" class="inline-flex items-center gap-1">
                <svg viewBox="0 0 24 24" class="remote-placeholder-icon" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M23 7l-7 5 7 5V7z" />
                  <path d="M1 1l22 22" />
                  <path d="M14 8H6a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-2" />
                </svg>
                <span class="remote-placeholder-meta-text">{{ t('videoCall.cameraOff') }}</span>
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
          <span v-if="connectionState === 'connected' && remoteAudioEnabled === false" class="ml-1" :title="t('videoCall.muted')">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 1a3 3 0 0 0-3 3v6" />
              <path d="M19 10v2a7 7 0 0 1-11.2 5.6" />
              <path d="M12 19v4" />
              <path d="M23 1 1 23" />
            </svg>
          </span>
          <span v-if="connectionState === 'connected' && remoteVideoEnabled === false" class="ml-0.5" :title="t('videoCall.cameraOff')">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M23 7l-7 5 7 5V7z" />
              <path d="M1 1l22 22" />
              <path d="M14 8H6a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-2" />
            </svg>
          </span>
        </div>
      </div>

      <div
        class="absolute top-3 right-3 rounded-xl overflow-hidden border border-white/15 shadow-lg local-preview"
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
            <span v-if="isLocalSpeaking" class="inline-flex" :title="t('videoCall.speaking')">
              <span class="speaking-dots">
                <span></span><span></span><span></span>
              </span>
            </span>
          </div>
          <div class="flex items-center gap-1 text-white/90">
            <span v-if="isMuted" :title="t('videoCall.muted')">
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 5.3 2" />
                <path d="M19 10v2a7 7 0 0 1-11.2 5.6" />
                <path d="M23 1 1 23" />
              </svg>
            </span>
            <span v-if="isVideoOff" :title="t('videoCall.cameraOff')">
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M23 7l-7 5 7 5V7z" />
                <path d="M1 1l22 22" />
                <path d="M14 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-3" />
              </svg>
            </span>
          </div>
        </div>
      </div>

      <div class="absolute left-0 right-0 flex items-center justify-center px-4 bottom-24 sm:bottom-4 z-30">
        <div class="pointer-events-auto inline-flex items-center gap-2 sm:gap-3 px-3 py-2 rounded-2xl bg-white/10 backdrop-blur border border-white/15 shadow-xl">
          <button
            type="button"
            class="control-btn"
            :class="isMuted ? 'bg-red-600/90 hover:bg-red-600 text-white border-red-500/40' : 'bg-white/10 hover:bg-white/15 text-white border-white/15'"
            :title="isMuted ? t('videoCall.unmute') : t('videoCall.mute')"
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
            :title="isVideoOff ? t('videoCall.cameraOn') : t('videoCall.cameraOffControl')"
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
            :title="isFullscreen ? t('videoCall.minimize') : t('videoCall.fullscreen')"
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
            v-if="role === 'psychologist' && callDurationSec >= 1500"
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

      <div v-if="connectionState === 'waiting'" class="absolute inset-0 z-10 flex items-center justify-center px-4 sm:px-6 pointer-events-none">
        <div class="pointer-events-auto w-full max-w-lg rounded-2xl bg-black/60 border border-white/10 backdrop-blur p-6 sm:p-8 text-center text-white">
          <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-emerald-500/15 mb-4">
            <svg viewBox="0 0 24 24" class="h-8 w-8 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 20v-6"/><path d="M12 14l4-4"/><path d="M12 14l-4-4"/><path d="M20 12a8 8 0 1 0-16 0"/></svg>
          </div>
          <h3 class="text-lg sm:text-xl font-semibold mb-2">{{ t('videoCall.waitingTitle') }}</h3>
          <p class="text-sm sm:text-base text-white/80 mb-4">{{ t('videoCall.waitingDesc') }}</p>

          <div class="flex items-center justify-center gap-2 sm:gap-3 mb-3">
            <button type="button" @click="copyRoomId" class="inline-flex items-center gap-2 px-3 py-2 rounded-md bg-white/10 hover:bg-white/15 text-sm sm:text-sm" :title="t('videoCall.copyRoom')">
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 9h10v10H9z"/><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"/></svg>
              <span class="hidden sm:inline">{{ t('videoCall.copyRoom') }}</span>
            </button>

            <button type="button" @click="shareRoom" class="inline-flex items-center gap-2 px-3 py-2 rounded-md bg-emerald-500/90 hover:bg-emerald-600 text-white text-sm" :title="t('videoCall.shareLink')">
              <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7"/><path d="M16 6l-4-4-4 4"/><path d="M12 2v13"/></svg>
              <span class="hidden sm:inline">{{ t('videoCall.shareLink') }}</span>
            </button>
          </div>

          <p class="text-xs text-white/60">{{ t('videoCall.waitingTip') }}</p>
        </div>
      </div>
    </div>
    </div>
    <!-- Floating chat toggle -->
    <button
      @click="toggleChat"
      class="chat-toggle absolute right-4 bottom-4 sm:bottom-4 z-40 inline-flex items-center justify-center h-12 w-12 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg focus:outline-none"
      :title="showChat ? t('videoCall.chat.close') : t('videoCall.chat.open')"
    >
      <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      <span v-if="unreadCount > 0" class="chat-badge absolute -top-1 -right-1 inline-flex items-center justify-center h-5 min-w-[20px] px-1 rounded-full bg-red-600 text-white text-xs font-semibold">{{ unreadCount }}</span>
    </button>

    <transition name="slide-chat">
      <div v-show="showChat" class="absolute top-0 right-0 bottom-0 z-50 w-full max-w-sm min-h-0">
        <VideoCallChat
          :ws="ws"
          :display-name="localDisplayName"
          :role="props.role"
          :session-active="sessionActive"
          ref="chatRef"
          @new-message="onNewMessage"
          @chat-opened="clearUnread"
          @close="showChat = false"
        />
      </div>
    </transition>
    <VideoCallRating ref="ratingRef" />
    <VideoCallSessionNotes ref="notesRef" />
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import VideoCallChat from './VideoCallChat.vue'
import VideoCallRating from './VideoCallRating.vue'
import VideoCallSessionNotes from './VideoCallSessionNotes.vue'
// Chat state
const chatRef = ref(null)
const showChat = ref(false)
const unreadCount = ref(0)
const sessionActive = computed(() => !!callStartedAt.value && !callEndedAt.value)
const swalConfirmColor = 'rgb(89, 151, 172)'

function toggleChat() {
  showChat.value = !showChat.value
  if (showChat.value) {
    unreadCount.value = 0
    if (chatRef.value && chatRef.value.scrollToBottom) chatRef.value.scrollToBottom()
  }
}

function clearUnread() {
  unreadCount.value = 0
}

function onNewMessage() {
  if (!showChat.value) unreadCount.value++
}
import { Link, router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { fetchConsultationJson as fetchJson } from '@/utils/consultationRequest'
import { callAudioConstraints, callVideoConstraints, configureCallVideoSender } from '@/utils/callMedia'

const props = defineProps({
  appointmentId: { type: Number, required: true },
  roomId: { type: String, required: true },
  displayName: { type: String, default: '' },
  signalingUrl: { type: String, default: '' },
  role: { type: String, default: 'patient' },
  backHref: { type: String, required: true },
  heightOffset: { type: Number, default: 190 },
})

const { t } = useI18n()

const stageEl = ref(null)
const localVideo = ref(null)
const remoteVideo = ref(null)
const remoteViewMode = ref('cover') // 'cover' = fill/crop (like Meet/Zoom), 'contain' = fit (no crop)

const error = ref('')
const isMuted = ref(false)
const isVideoOff = ref(false)
const isFullscreen = ref(false)
const iceModeLabel = ref('HOST')

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
const sessionId = ref(null)
const sessionPsychologistId = ref(null)
let hasJoinedSession = false

let ws = null
let leavingCall = false
let reconnectTimer = null
let reconnectAttempt = 0
let signalingBlocked = false
let authorizationRetryCount = 0
let mediaRecoveryTimer = null
let turnCredentialsExpiresAt = 0
let iceConfigurationRefreshedAt = 0
let signalingConnecting = false
let removeCallListeners = () => {}

function scheduleSignalingReconnect() {
  if (leavingCall || signalingBlocked || reconnectTimer) return
  if (pc?.connectionState !== 'connected') setConnectionState('connecting')
  reconnectTimer = setTimeout(() => {
    reconnectTimer = null
    connectSignaling().catch(handleSignalingRequestError)
  }, Math.min(1000 * 2 ** Math.min(reconnectAttempt++, 4), 15000))
}

function handleSignalingRequestError(requestError) {
  if ([401, 403, 404, 409, 419].includes(requestError?.status)) {
    signalingBlocked = true
    error.value = requestError.message
    if (pc?.connectionState !== 'connected') setConnectionState('failed')
    return
  }
  scheduleSignalingReconnect()
}

function scheduleMediaRecovery() {
  if (leavingCall || mediaRecoveryTimer) return
  mediaRecoveryTimer = setTimeout(async () => {
    mediaRecoveryTimer = null
    if (leavingCall || !pc || pc.connectionState === 'connected') return
    if (ws?.readyState === WebSocket.OPEN) {
      try {
        if (props.role === 'psychologist') await makeOffer(true)
        else wsSend({ type: 'restart-request' })
      } catch { /* Retry while connectivity recovers. */ }
    }
    scheduleMediaRecovery()
  }, 5000)
}
let pc = null
let localStream = null
let pendingIce = []
let pendingMediaState = null

let localVideoSender = null
let makingOffer = false
let localOfferSentAt = 0

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
let iceModeTimer = null

const page = usePage()
const authUser = computed(() => page.props?.value?.auth?.user ?? page.props?.auth?.user ?? null)

  const ratingRef = ref(null)
  const notesRef = ref(null)

const localBaseName = computed(() => {
  const fromProp = String(props.displayName || '').trim()
  if (fromProp) return fromProp

  const fromAuth = String(authUser.value?.name || '').trim()
  if (fromAuth) return fromAuth

  const fromEmail = String(authUser.value?.email || '').trim()
  if (fromEmail) return fromEmail

  return ''
})

const localDisplayName = computed(() => localBaseName.value || (props.role === 'psychologist' ? t('videoCall.youPsychologist') : t('videoCall.youPatient')))

const localUiName = computed(() => t('videoCall.you'))

const localSignalingName = computed(() => {
  const clean = String(localBaseName.value || '').trim()
  if (clean) return clean
  return props.role === 'psychologist' ? t('videoCall.psychologist') : t('videoCall.patient')
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
  return r === 'psychologist' ? t('videoCall.psychologist') : r === 'patient' ? t('videoCall.patient') : r
})

const remoteDisplayName = computed(() => {
  if (remoteName.value) return remoteName.value
  if (connectionState.value === 'waiting') return t('videoCall.waitingShort')
  // Never show "Participant"; fall back to role label.
  if (remoteRoleLabel.value) return remoteRoleLabel.value
  return props.role === 'patient' ? t('videoCall.psychologist') : t('videoCall.patient')
})

const statusLabel = computed(() => {
  if (connectionState.value === 'connected') return t('videoCall.status.connected')
  if (connectionState.value === 'waiting') return t('videoCall.status.waiting')
  if (connectionState.value === 'failed') return t('videoCall.status.failed')
  return t('videoCall.status.connecting')
})

const showLocalPlaceholder = computed(() => {
  // Users asked to avoid a black box when they turn camera off.
  return Boolean(isVideoOff.value)
})

const showRemotePlaceholder = computed(() => {
  // Connected: show placeholder if remote camera is off OR we are not rendering frames.
  if (connectionState.value === 'connected') {
    return remoteVideoEnabled.value === false || remoteVideoAvailable.value === false || remoteVideoRenderable.value === false
  }
  // Waiting/Connecting/Failed: always show a pleasant placeholder instead of black.
  return true
})

const statusDotClass = computed(() => {
  switch (connectionState.value) {
    case 'connected':
      return 'bg-emerald-500'
    case 'waiting':
      return 'bg-amber-400'
    case 'failed':
      return 'bg-red-500'
    default:
      return 'bg-blue-500 animate-pulse'
  }
})

const statusPillClass = computed(() => {
  switch (connectionState.value) {
    case 'connected':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200'
    case 'waiting':
      return 'bg-amber-50 text-amber-700 border-amber-200'
    case 'failed':
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
  minHeight: '320px',
}))

function resetPeerConnection() {
  clearTimeout(mediaRecoveryTimer)
  mediaRecoveryTimer = null
  try {
    if (pc) pc.close()
  } catch {}
  pc = null

  try {
    if (iceModeTimer) clearInterval(iceModeTimer)
  } catch {}
  iceModeTimer = null

  localVideoSender = null
  makingOffer = false
  localOfferSentAt = 0

  pendingIce = []

  try {
    if (remoteVideo.value) remoteVideo.value.srcObject = null
  } catch {}

  remoteVideoAvailable.value = false
  remoteVideoEnabled.value = true
  remoteAudioEnabled.value = true
  remoteVideoRenderable.value = false
}

function toggleRemoteViewMode() {
  try { remoteViewMode.value = remoteViewMode.value === 'cover' ? 'contain' : 'cover' } catch {}
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

async function getRtcConfig(mode = 'default') {
  try {
    // Base STUN servers (multiple for reliability)
    const defaultStunServers = [
      { urls: 'stun:stun.l.google.com:19302' },
      { urls: 'stun:stun1.l.google.com:19302' },
      { urls: 'stun:stun2.l.google.com:19302' },
      { urls: 'stun:stun3.l.google.com:19302' },
      { urls: 'stun:stun4.l.google.com:19302' },
      { urls: 'stun:stun.stunprotocol.org:3478' },
      { urls: 'stun:stun.voipbuster.com:3478' },
    ]

    const stunUrl = import.meta.env.VITE_STUN_URL || null

    async function fetchRuntimeTurnServers() {
      try {
        const data = await fetchCallJson(`/appointments/${props.appointmentId}/turn-credentials`)
        turnCredentialsExpiresAt = Number(data?.expiresAt || 0) * 1000
        iceConfigurationRefreshedAt = Date.now()
        if (error.value === 'TURN credentials are unavailable. The application server must provide relay credentials before this network can use TURN.') {
          error.value = ''
        }
        return Array.isArray(data?.iceServers) ? data.iceServers : []
      } catch (requestError) {
        console.warn('[webrtc] Temporary TURN credentials unavailable', { status: requestError.status })
        error.value = 'TURN credentials are unavailable. The application server must provide relay credentials before this network can use TURN.'
        if (mode === 'relayOnly') throw new Error(error.value)
        setIceModeLabel('STUN ONLY')
        return []
      }
    }

    function parseUrlList(raw) {
      if (!raw) return []
      return String(raw)
        .split(/[\n,]/)
        .map((v) => v.trim())
        .filter(Boolean)
    }

    function normalizeStunUrls(rawUrls) {
      const values = Array.isArray(rawUrls) ? rawUrls : parseUrlList(rawUrls)
      const out = []
      for (const value of values) {
        if (!value) continue
        if (/^stuns?:/i.test(value)) out.push(value)
        else out.push(`stun:${value.replace(/^\/+/, '')}`)
      }
      return [...new Set(out)]
    }

    const envStunServers = normalizeStunUrls(stunUrl).map((urls) => ({ urls }))
    const stunServers = [...envStunServers, ...defaultStunServers]
    const turnServers = await fetchRuntimeTurnServers()

    if (mode === 'stunOnly') {
      try { console.debug('[webrtc] using STUN-only iceServers', stunServers) } catch {}
      return { iceServers: stunServers }
    }

    if (mode === 'relayOnly') {
      if (!turnServers.length) throw new Error('TURN-only mode requires valid relay credentials.')
      try { console.debug('[webrtc] debug relay-only mode', { serverCount: turnServers.length }) } catch {}
      return { iceServers: turnServers, iceTransportPolicy: 'relay' }
    }

    // default: include STUN servers and TURN as fallback in the same config.
    const iceServers = [...stunServers, ...turnServers]
    try { console.debug('[webrtc] ICE configuration', { stunCount: stunServers.length, turnCount: turnServers.length, policy: 'all' }) } catch {}
    return { iceServers }
  } catch (e) {
    if (mode === 'relayOnly') {
      setIceModeLabel('UNAVAILABLE')
      throw e
    }
    return { iceServers: [{ urls: 'stun:stun.l.google.com:19302' }] }
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

function applySession(session) {
  if (!session || typeof session !== 'object') return
  sessionStatus.value = String(session.status || '')

  // record session id and psychologist id if available (best-effort)
  try {
    sessionId.value = session.id || null
  } catch {
    sessionId.value = null
  }
  try {
    sessionPsychologistId.value = session.psychologist_id || (session.appointment && session.appointment.psychologist_id) || null
  } catch {
    sessionPsychologistId.value = null
  }

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

function setIceModeLabel(mode) {
  iceModeLabel.value = mode
}

function normalizeIceMode(candidateType) {
  const type = String(candidateType || '').trim().toLowerCase()
  if (type === 'relay') return 'TURN'
  if (type === 'srflx') return 'STUN'
  if (type === 'host') return 'HOST'
  return 'HOST'
}

async function refreshIceModeFromStats(peer) {
  if (!peer || typeof peer.getStats !== 'function') return

  try {
    const stats = await peer.getStats()
    let selectedPair = null

    stats.forEach((report) => {
      if (report.type !== 'candidate-pair') return
      if (report.state === 'succeeded' && (report.nominated || report.selected)) {
        selectedPair = report
      }
    })

    stats.forEach((report) => {
      if (report.type === 'transport' && report.selectedCandidatePairId) {
        selectedPair = stats.get(report.selectedCandidatePairId) || selectedPair
      }
    })

    const localCandidate = selectedPair?.localCandidateId ? stats.get(selectedPair.localCandidateId) : null
    const remoteCandidate = selectedPair?.remoteCandidateId ? stats.get(selectedPair.remoteCandidateId) : null
    if (localCandidate?.candidateType) {
      const usesRelay = [localCandidate, remoteCandidate].some((candidate) => candidate?.candidateType === 'relay')
      const nextMode = usesRelay ? 'TURN' : normalizeIceMode(localCandidate.candidateType)
      if (iceModeLabel.value !== nextMode) {
        console.debug('[webrtc] Selected ICE pair', {
          localType: localCandidate.candidateType,
          remoteType: remoteCandidate?.candidateType,
          protocol: localCandidate.protocol,
          relayProtocol: localCandidate.relayProtocol,
        })
      }
      setIceModeLabel(nextMode)
    }
  } catch {
    // ignore stats errors
  }
}

function startIceModePolling(peer) {
  if (iceModeTimer) return
  iceModeTimer = setInterval(() => {
    refreshIceModeFromStats(peer)
    if (turnCredentialsExpiresAt > 0 && turnCredentialsExpiresAt <= Date.now() + 300000) {
      refreshIceConfiguration(peer).then((refreshed) => {
        if (refreshed && props.role === 'psychologist') return makeOffer(true)
      }).catch(() => { /* Keep the existing media route during credential outages. */ })
    }
  }, 2500)
}

async function createPeerConnection() {
  if (pc) return pc

  const forceRelay = import.meta.env.VITE_FORCE_RELAY === '1'
  const initialMode = forceRelay ? 'relayOnly' : 'default'
  // TURN is shown only after getStats confirms a selected relay candidate.
  setIceModeLabel('AUTO')
  const rtcConfig = await getRtcConfig(initialMode)
  if (leavingCall) throw new Error('Call has ended')
  // ICE messages can arrive while the asynchronous credential request is pending.
  if (pc) return pc

  // Helper to attach the standard handlers to a peer connection.
  const attachHandlers = (peer) => {
    peer.ontrack = (event) => {
      // Some transceivers deliver streamless tracks, particularly when a
      // camera is enabled after joining without video.
      const stream = event.streams[0] || remoteVideo.value?.srcObject || new MediaStream()
      if (!stream.getTracks().includes(event.track)) stream.addTrack(event.track)
      if (remoteVideo.value && stream) remoteVideo.value.srcObject = stream

      try {
        ensureAudioContext().then(() => {
          setupRemoteSpeakingAnalyser(stream)
          startSpeakingDetectionLoop()
        })
      } catch {}

      updateRemoteVideoRenderable()

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

    peer.onicecandidate = (event) => {
      try { console.debug('[webrtc] ICE candidate', { type: event.candidate?.type, protocol: event.candidate?.protocol, gatheringComplete: !event.candidate }) } catch {}
      if (event.candidate) wsSend({ type: 'ice', candidate: event.candidate })
    }

    peer.oniceconnectionstatechange = () => {
      console.debug('[webrtc] ICE connection state', peer.iceConnectionState)
    }
    peer.onicegatheringstatechange = () => {
      console.debug('[webrtc] ICE gathering state', peer.iceGatheringState)
    }
    peer.onicecandidateerror = (event) => {
      console.warn('[webrtc] ICE server error', { code: event.errorCode })
    }

    peer.onconnectionstatechange = () => {
      if (leavingCall || pc !== peer) return
      const st = peer?.connectionState
      try { console.debug('[webrtc] connectionState changed', st) } catch {}
      if (st === 'connected') {
        clearTimeout(mediaRecoveryTimer)
        mediaRecoveryTimer = null
        setConnectionState('connected')
        for (const sender of peer.getSenders()) configureCallVideoSender(sender)
        refreshIceModeFromStats(peer)
        startIceModePolling(peer)
      }
      else if (st === 'failed' || st === 'disconnected') {
        setConnectionState('connecting')
        scheduleMediaRecovery()
      }
      else setConnectionState('connecting')
      if (st === 'connecting') scheduleMediaRecovery()
    }

    peer.onnegotiationneeded = async () => {
      if (!ws || ws.readyState !== WebSocket.OPEN) return
      if (!peer || peer.signalingState !== 'stable') return
      if (props.role !== 'psychologist') {
        if (peer.remoteDescription) wsSend({ type: 'restart-request' })
        return
      }
      if (makingOffer) return

      try { await makeOffer() } catch { scheduleMediaRecovery() }
    }
  }

  // Create initial peer connection.
  pc = new RTCPeerConnection(rtcConfig)
  attachHandlers(pc)

  // Add existing local tracks if any.
  if (localStream) {
    for (const track of localStream.getTracks()) {
      const sender = pc.addTrack(track, localStream)
      if (track.kind === 'video') localVideoSender = sender
    }
  }

  // Reserve a video sender so either role can enable a missing camera later.
  if (!localVideoSender) {
    localVideoSender = pc.addTransceiver('video', {
      direction: 'sendrecv',
      streams: localStream ? [localStream] : [],
    }).sender
  }
  return pc
}

async function startLocalMedia() {
  if (leavingCall) return
  const mediaAttempts = [
    { video: callVideoConstraints(), audio: callAudioConstraints() },
    { video: false, audio: callAudioConstraints() },
    { video: callVideoConstraints(), audio: false },
  ]

  let lastError = null
  for (const constraints of mediaAttempts) {
    try {
      localStream = await navigator.mediaDevices.getUserMedia(constraints)
      if (leavingCall) {
        localStream.getTracks().forEach((track) => track.stop())
        localStream = null
        return
      }
      break
    } catch (error) {
      lastError = error
      localStream = null
    }
  }

  if (!localStream) {
    console.warn('[webrtc] unable to access camera/microphone, continuing without local media', lastError)
    localStream = new MediaStream()
  }

  // Apply initial toggle states.
  const audioTracks = localStream.getAudioTracks()
  const videoTracks = localStream.getVideoTracks()
  audioTracks.forEach((t) => (t.enabled = !isMuted.value))
  videoTracks.forEach((t) => (t.enabled = !isVideoOff.value))

  if (videoTracks.length === 0) {
    isVideoOff.value = true
  }

  if (audioTracks.length === 0) {
    isMuted.value = true
  }

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

  try {
    const camStream = await navigator.mediaDevices.getUserMedia({ video: callVideoConstraints(), audio: false })
    if (leavingCall) {
      camStream.getTracks().forEach((track) => track.stop())
      return null
    }
    const track = camStream.getVideoTracks()[0]
    if (!track) return null

    localStream.addTrack(track)
    if (localVideo.value) localVideo.value.srcObject = localStream
    return track
  } catch (error) {
    console.warn('[webrtc] camera is busy, keeping video off', error)
    return null
  }
}

async function makeOffer(iceRestart = false) {
  if (leavingCall || makingOffer || ws?.readyState !== WebSocket.OPEN) return
  makingOffer = true
  try {
    const peer = await createPeerConnection()
    if (iceRestart) await refreshIceConfiguration(peer)
    if (leavingCall || pc !== peer || ws?.readyState !== WebSocket.OPEN) return
    if (iceRestart && peer.signalingState === 'have-local-offer') {
      // Allow a slow answer to arrive before replacing its pending offer.
      if (Date.now() - localOfferSentAt < 15000) return
      await peer.setLocalDescription({ type: 'rollback' })
    }
    if (peer.signalingState !== 'stable') return
    const offer = await peer.createOffer({ iceRestart })
    if (leavingCall || pc !== peer || ws?.readyState !== WebSocket.OPEN) return
    await peer.setLocalDescription(offer)
    localOfferSentAt = Date.now()
    wsSend({ type: 'offer', sdp: peer.localDescription })
    scheduleMediaRecovery()
  } finally {
    makingOffer = false
  }
}

async function refreshIceConfiguration(peer) {
  // Refresh expiring relay credentials before gathering a replacement route.
  if (turnCredentialsExpiresAt > Date.now() + 300000) return
  if (Date.now() - iceConfigurationRefreshedAt < 30000) return
  iceConfigurationRefreshedAt = Date.now()
  const mode = import.meta.env.VITE_FORCE_RELAY === '1' ? 'relayOnly' : 'default'
  const configuration = await getRtcConfig(mode)
  if (leavingCall || pc !== peer) return
  // A temporary credential endpoint outage must not remove the current relay.
  if (configuration.iceServers?.some((server) => server.credential)) {
    peer.setConfiguration(configuration)
    return true
  }
  return false
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
      isVideoOff.value = true
      sendMediaState()
      return
    }

    if (pc) {
      if (localVideoSender) {
        try {
          await localVideoSender.replaceTrack(track)
          await configureCallVideoSender(localVideoSender)
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

async function shareRoom() {
  try {
    const url = typeof location !== 'undefined' ? location.href : String(props.roomId)
    if (navigator.share) {
      await navigator.share({ title: t('videoCall.shareTitle'), text: t('videoCall.shareText', { room: props.roomId }), url })
      return
    }
    // fallback: copy url
    await navigator.clipboard.writeText(url)
    try { alert(t('videoCall.copiedToClipboard')) } catch {}
  } catch (e) {
    try { console.error(e) } catch {}
  }
}

function hangUp() {
  leavingCall = true
  removeCallListeners()
  if (ws) clearTimeout(ws.joinTimeout)
  clearTimeout(reconnectTimer)
  clearTimeout(mediaRecoveryTimer)
  reconnectTimer = null
  mediaRecoveryTimer = null
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
    title: t('videoCall.endSessionConfirmTitle'),
    text: t('videoCall.endSessionConfirmText'),
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: t('videoCall.endSessionConfirm'),
    cancelButtonText: t('videoCall.cancel'),
    reverseButtons: true,
    focusCancel: true,
    confirmButtonColor: 'rgb(89, 151, 172)', // <-- CHANGE THIS LINE
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

    try {
      wsSend({ type: 'session-ended', endedAt: data?.session?.ended_at || null })
    } catch {
      // ignore
    }

    if (props.role === 'psychologist') {
      try {
        const noteValues = notesRef.value ? await notesRef.value.open({
          appointment_session_id: sessionId.value,
          session_date: data?.session?.started_at || new Date().toISOString(),
          session_duration: data?.session?.duration_minutes || 0,
        }) : null

        if (noteValues) {
          try {
            await fetchJson('/appointment-session-notes', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(noteValues),
            })
            Swal.fire({ 
              icon: 'success', 
              title: t('videoCall.notesSaved'), 
              text: t('videoCall.notesSavedText'), 
              confirmButtonColor: 'rgb(89, 151, 172)' // <-- CHANGE THIS LINE
            })
          } catch (e) {
            console.error('Failed to save session notes', e)
            Swal.fire({ 
              icon: 'error', 
              title: t('videoCall.couldNotSaveNotes'), 
              confirmButtonColor: 'rgb(89, 151, 172)' // <-- CHANGE THIS LINE
            })
          }
        }
      } catch (e) {
        // ignore modal errors
      }
    }

    hangUpAndLeave()
  } catch (e) {
    error.value = e?.message ? String(e.message) : t('videoCall.error.endFailed')
  } finally {
    isEndingSession.value = false
  }
}

function hangUpAndLeave() {
  hangUp()
  router.visit(props.backHref)
}

async function handlePatientSessionEnded() {
  try {
    const formValues = ratingRef.value ? await ratingRef.value.open() : null

    if (formValues && formValues.rating) {
      try {
        try {
          await fetchJson('/sanctum/csrf-cookie', { method: 'GET' })
        } catch {
          // ignore
        }

        await fetchJson('/session-ratings', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            session_id: sessionId.value,
            appointment_id: props.appointmentId,
            patient_id: authUser.value?.id || null,
            psychologist_id: sessionPsychologistId.value || null,
            rating: formValues.rating,
            comment: formValues.comment || '',
          }),
        })
        Swal.fire({
          icon: 'success',
          title: t('videoCall.feedback.thankYou'),
          text: t('videoCall.feedback.thankYouText'),
          confirmButtonColor: swalConfirmColor,
        })
      } catch (e) {
        console.error('Failed to submit session rating', e)
        Swal.fire({
          icon: 'error',
          title: t('videoCall.feedback.submitError'),
          confirmButtonColor: swalConfirmColor,
        })
      }
    }
  } catch {
    // ignore
  } finally {
    setTimeout(() => {
      try { hangUpAndLeave() } catch {}
    }, 600)
  }
}

async function handleSessionEnded() {
  await syncSessionBackend()
  error.value = 'Session ended by the psychologist.'

  if (props.role === 'patient') {
    await handlePatientSessionEnded()
    return
  }

  setTimeout(() => { try { hangUpAndLeave() } catch {} }, 1200)
}

async function fetchCallJson(url) {
  const controller = new AbortController()
  const timeout = setTimeout(() => controller.abort(), 10000)
  try {
    return await fetchJson(url, { cache: 'no-store', signal: controller.signal })
  } finally {
    clearTimeout(timeout)
  }
}

async function connectSignaling() {
  if (leavingCall || signalingBlocked) return
  if (signalingConnecting || ws?.readyState === WebSocket.OPEN || ws?.readyState === WebSocket.CONNECTING) return
  const roomId = String(props.roomId || '').trim()
  const signalingUrl = String(props.signalingUrl || import.meta.env.VITE_SIGNALING_URL || '').trim()
  let signalingToken
  signalingConnecting = true
  try {
    const data = await fetchCallJson(`/appointments/${props.appointmentId}/signaling-token`)
    signalingToken = data.token
  } finally {
    signalingConnecting = false
  }
  if (leavingCall) return
  const socket = new WebSocket(signalingUrl)
  ws = socket
  // A stalled upgrade or room join must not leave recovery stuck forever.
  socket.joinTimeout = setTimeout(() => {
    if (leavingCall || ws !== socket) return
    socket.close()
    scheduleSignalingReconnect()
  }, 15000)

  socket.onopen = async () => {
    if (leavingCall || ws !== socket) { socket.close(); return }
    wsSend({ type: 'join', roomId, token: signalingToken })
    sendMediaState()

    // Sync session start in case the other participant already joined.
    syncSessionBackend()

    if (pendingMediaState) {
      wsSendOrQueue(pendingMediaState)
      pendingMediaState = null
    }
  }

  socket.onerror = () => {
    // close schedules recovery without interrupting active media.
  }

  socket.onclose = (event) => {
    clearTimeout(socket.joinTimeout)
    if (leavingCall || ws !== socket) return
    const joinTimedOut = event.code === 4008 || (event.code === 1008 && event.reason === 'Authentication required')
    const rateLimited = event.code === 4029 || (event.code === 1008 && event.reason === 'Too many messages')
    console.warn('[webrtc] Signaling connection closed', {
      code: event.code,
      category: joinTimedOut ? 'join_timeout' : rateLimited ? 'rate_limited' : event.code === 1008 ? 'room_authorization' : 'connection_closed',
    })
    if (joinTimedOut) {
      scheduleSignalingReconnect()
      return
    }
    if (rateLimited) {
      signalingBlocked = true
      error.value = 'Too many call connection updates. Close duplicate call tabs and try again.'
      if (pc?.connectionState !== 'connected') setConnectionState('failed')
      return
    }
    // A suspended tab may resume with an old token. Fetch a fresh one once;
    // persistent rejection stops retries rather than bypassing authorization.
    if (event.code === 1008 && authorizationRetryCount++ < 1) {
      scheduleSignalingReconnect()
      return
    }
    if (event.code === 1008 || event.code === 4001) {
      signalingBlocked = true
      error.value = event.code === 4001
        ? 'This consultation was opened in another tab or device.'
        : 'The call server could not verify this room. Please refresh the page and try again.'
      if (pc?.connectionState !== 'connected') setConnectionState('failed')
      return
    }
    scheduleSignalingReconnect()
  }

  let messageQueue = Promise.resolve()
  socket.onmessage = (event) => {
    // SDP application and TURN fetching are asynchronous. Process subsequent
    // candidates only after the preceding offer/answer has been applied.
    messageQueue = messageQueue.then(async () => {
      if (leavingCall || ws !== socket) return
      try {
        let msg
        try {
          msg = JSON.parse(String(event.data))
        } catch {
          return
        }
        // Handle chat messages
        if (msg.type === 'chat' && sessionActive.value && chatRef.value) {
          chatRef.value.receiveMessage(msg.payload)
          return
        }
        if (msg.type === 'joined') {
          authorizationRetryCount = 0
          clearTimeout(socket.joinTimeout)
          reconnectAttempt = 0
          // If someone is already in the room, capture their identity and (if psychologist) start offer.
          if (Array.isArray(msg.peers) && msg.peers.length > 0) {
            setRemoteIdentity(msg.peers[0])
            if (pc?.connectionState !== 'connected') setConnectionState('connecting')
          } else {
            if (pc?.connectionState !== 'connected') setConnectionState('waiting')
          }

          if (props.role === 'psychologist' && Number(msg.peerCount || 0) > 1) {
            await createPeerConnection()
            await makeOffer(pc?.connectionState !== 'connected' || pc?.signalingState !== 'stable')
          }

          // If the other peer is already present, the session might have started.
          syncSessionBackend()
        }

        if (msg.type === 'peer-joined') {
          setRemoteIdentity(msg)
          if (pc?.connectionState !== 'connected') setConnectionState('connecting')

          // Second participant joined; backend will set started_at.
          syncSessionBackend()

          if (props.role === 'psychologist') {
            await createPeerConnection()
            await makeOffer(pc?.connectionState !== 'connected' || pc?.signalingState !== 'stable')
          }
        }

        if (msg.type === 'restart-request' && props.role === 'psychologist') {
          await makeOffer(true)
        }

        if (msg.type === 'offer') {
          setRemoteIdentity(msg.from || msg)
          const peer = await createPeerConnection()
          await refreshIceConfiguration(peer)
          if (leavingCall || ws !== socket || pc !== peer) return
          await peer.setRemoteDescription(new RTCSessionDescription(msg.sdp))
          await flushPendingIce()
          const answer = await peer.createAnswer()
          await peer.setLocalDescription(answer)
          wsSend({ type: 'answer', sdp: peer.localDescription })
          for (const sender of peer.getSenders()) configureCallVideoSender(sender)
        }

        if (msg.type === 'answer') {
          setRemoteIdentity(msg.from || msg)
          const peer = await createPeerConnection()
          await peer.setRemoteDescription(new RTCSessionDescription(msg.sdp))
          await flushPendingIce()
          for (const sender of peer.getSenders()) configureCallVideoSender(sender)
        }

        if (msg.type === 'ice') {
          setRemoteIdentity(msg.from || msg)
          await createPeerConnection()
          await addIceCandidateBuffered(msg.candidate)
        }

        if (msg.type === 'peer-left') {
          if (msg.signalingTimeout && pc?.connectionState === 'connected') return
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
          await handleSessionEnded()
        }

        if (msg.type === 'error') {
          error.value = String(msg.message || 'Signaling error.')
        }
      } catch (negotiationError) {
        if (leavingCall || ws !== socket) return
        if (pc?.connectionState !== 'connected') setConnectionState('connecting')
        scheduleMediaRecovery()
        console.warn('[webrtc] Media negotiation failed', { name: negotiationError?.name })
      }
    })
    return messageQueue
  }

}

function handleConnectionWake() {
  if (leavingCall || signalingBlocked || document.visibilityState === 'hidden') return
  if (!ws || ws.readyState === WebSocket.CLOSED || ws.readyState === WebSocket.CLOSING) {
    clearTimeout(reconnectTimer)
    reconnectTimer = null
    connectSignaling().catch(handleSignalingRequestError)
  }
  if (pc && pc.connectionState !== 'connected') scheduleMediaRecovery()
}

onMounted(async () => {
  const onFsChange = () => {
    isFullscreen.value = Boolean(document.fullscreenElement)
  }
  const onRemoteVideoMeta = () => updateRemoteVideoRenderable()
  const videoElement = remoteVideo.value
  document.addEventListener('fullscreenchange', onFsChange)
  document.addEventListener('visibilitychange', handleConnectionWake)
  window.addEventListener('online', handleConnectionWake)
  for (const event of ['loadedmetadata', 'resize', 'playing']) videoElement?.addEventListener(event, onRemoteVideoMeta)
  removeCallListeners = () => {
    document.removeEventListener('fullscreenchange', onFsChange)
    document.removeEventListener('visibilitychange', handleConnectionWake)
    window.removeEventListener('online', handleConnectionWake)
    for (const event of ['loadedmetadata', 'resize', 'playing']) videoElement?.removeEventListener(event, onRemoteVideoMeta)
  }
  try {
    // Track session presence immediately.
    await joinSessionBackend()
    if (leavingCall) return

    const roomId = String(props.roomId || '').trim()
    if (!roomId) throw new Error('Missing room id')

    const signalingUrl = String(props.signalingUrl || import.meta.env.VITE_SIGNALING_URL || '').trim()
    if (!signalingUrl) throw new Error('Missing signaling URL (set VITE_SIGNALING_URL)')

    if (!localVideo.value || !remoteVideo.value) throw new Error('Video container not ready')

    setConnectionState('connecting')
    await startLocalMedia()

    try { await connectSignaling() } catch (requestError) { handleSignalingRequestError(requestError) }
  } catch (e) {
    if (leavingCall) return
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

/* Chat slide-in transition and badge */
.slide-chat-enter-active, .slide-chat-leave-active {
  transition: transform 250ms ease, opacity 200ms ease;
}
.slide-chat-enter-from, .slide-chat-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
.slide-chat-enter-to, .slide-chat-leave-from {
  transform: translateX(0%);
  opacity: 1;
}
.chat-badge { box-shadow: 0 4px 10px rgba(0,0,0,0.15); }

/* Responsive remote placeholder styles - scale smoothly with viewport/zoom */
.remote-placeholder-container {
  padding: 0.5rem;
}
.remote-placeholder-avatar {
  width: clamp(56px, 18vw, 128px);
  height: clamp(56px, 18vw, 128px);
}
.remote-placeholder-initials {
  font-size: clamp(1.25rem, 6vw, 2.75rem);
}
.remote-placeholder-name {
  font-size: clamp(1rem, 3.5vw, 1.25rem);
}
.remote-placeholder-meta {
  font-size: clamp(.75rem, 2.5vw, .95rem);
}
.remote-placeholder-meta .remote-placeholder-icon {
  width: clamp(14px, 2.5vw, 18px);
  height: clamp(14px, 2.5vw, 18px);
}
.remote-placeholder-meta .remote-placeholder-meta-text {
  font-size: clamp(.65rem, 2.2vw, .85rem);
}
</style>

<style scoped>
/* Mobile/touch improvements */
@media (max-width: 640px) {
  .control-btn {
    height: 56px;
    width: 56px;
    border-radius: 14px;
  }

  .chat-toggle {
    height: 56px;
    width: 56px;
  }

  /* Make the local preview larger on small screens and inset a bit */
  .local-preview {
    right: 12px !important;
    top: 12px !important;
    width: calc(34vw) !important;
  }

  /* Ensure the control bar is reachable and not overlapped */
  .control-bar-spacer {
    height: 88px;
  }
}
</style>

<style scoped>
/* Local preview (portrait) default sizes */
.local-preview {
  width: 156px; /* reduced width */
  height: 198px; /* reduced height, keep portrait */
  right: 12px;
  top: 12px;
}

@media (min-width: 640px) {
  .local-preview {
    width: 178px; /* slightly reduced on larger screens */
    height: 232px;
    right: 12px;
    top: 12px;
  }
}

@media (max-width: 640px) {
  .local-preview {
    width: calc(24vw);
    height: calc(30vw);
    right: 10px !important;
    top: 10px !important;
  }
}

.local-preview video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
</style>

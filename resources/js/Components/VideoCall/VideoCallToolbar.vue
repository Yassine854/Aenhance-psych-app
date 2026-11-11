<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import VideoCallInteractions from './VideoCallInteractions.vue'

const props = defineProps({
  connected: Boolean,
  handRaised: Boolean,
  unreadCount: { type: Number, default: 0 },
  displayName: { type: String, default: '' },
  sendReaction: { type: Function, required: true },
  fullscreen: Boolean,
  viewMode: String,
  chatOpen: Boolean,
})
const emit = defineEmits(['toggle-hand', 'chat', 'fullscreen', 'fit'])
const { t } = useI18n()
const root = ref(null)
const bar = ref(null)
const menu = ref(null)
const moreButton = ref(null)
const reactions = ref(null)
const overlay = ref(null)
const visible = ref(true)
const moreOpen = ref(false)
const hovering = ref(false)
let hideTimer
const pinned = computed(() => !props.connected || props.chatOpen || moreOpen.value || hovering.value)

function scheduleHide() {
  clearTimeout(hideTimer)
  if (pinned.value) return
  hideTimer = setTimeout(() => {
    const active = document.activeElement
    if (root.value?.contains(active) && active?.matches(':focus-visible')) return
    visible.value = false
  }, 3500)
}
function revealControls() {
  visible.value = true
  scheduleHide()
}
async function toggleMore() {
  moreOpen.value = !moreOpen.value
  revealControls()
  if (moreOpen.value) {
    await nextTick()
    menu.value?.querySelector('button')?.focus()
  }
}
function closeMore(restoreFocus = false) {
  moreOpen.value = false
  if (restoreFocus) moreButton.value?.focus()
  revealControls()
}
function action(name) {
  emit(name)
  closeMore()
}
function onKeydown(event) {
  if (event.key === 'Escape' && moreOpen.value) {
    event.preventDefault()
    closeMore(true)
  }
  if (event.key === 'Tab' && moreOpen.value) {
    const buttons = [...menu.value.querySelectorAll('button:not(:disabled)')]
    const first = buttons[0], last = buttons.at(-1)
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus() }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus() }
  }
}
watch(pinned, () => { if (pinned.value) visible.value = true; scheduleHide() })
onMounted(revealControls)
onBeforeUnmount(() => clearTimeout(hideTimer))
defineExpose({ revealControls, receiveReaction: (id, name) => reactions.value?.receiveReaction(id, name) })
</script>

<template>
  <div ref="root" class="pointer-events-none absolute inset-0 z-30" @keydown="onKeydown" @pointerdown.stop @click.stop>
    <div ref="overlay" class="pointer-events-none absolute bottom-28 left-4 right-4" />

    <div class="absolute bottom-4 right-4 flex flex-col gap-3">
      <button type="button" class="floating-action pointer-events-auto shadow-lg"
        :class="handRaised ? 'bg-amber-300 text-gray-950' : 'bg-gray-900/80 text-white hover:bg-gray-800'"
        :aria-pressed="handRaised"
        :aria-label="t(handRaised ? 'videoCall.interactions.lowerHand' : 'videoCall.interactions.raiseHand')"
        :title="t(handRaised ? 'videoCall.interactions.lowerHand' : 'videoCall.interactions.raiseHand')"
        @click="$emit('toggle-hand'); revealControls()">
        <span aria-hidden="true" class="text-2xl">✋</span>
      </button>
      <button type="button" class="floating-action relative pointer-events-auto bg-emerald-500 text-white shadow-lg hover:bg-emerald-600"
        :aria-label="t(chatOpen ? 'videoCall.chat.close' : 'videoCall.chat.open')"
        :title="t(chatOpen ? 'videoCall.chat.close' : 'videoCall.chat.open')" @click="$emit('chat'); revealControls()">
        <svg aria-hidden="true" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" /></svg>
        <span v-if="unreadCount > 0 && !chatOpen" class="absolute -right-1 -top-1 min-w-5 rounded-full bg-red-600 px-1 text-xs font-semibold">{{ unreadCount }}</span>
      </button>
    </div>

    <div v-if="moreOpen" class="pointer-events-auto absolute inset-0 bg-black/30" @click="closeMore(true)" />
    <div v-show="moreOpen" ref="menu" role="dialog" aria-modal="true" :aria-label="t('videoCall.toolbar.more')"
      class="call-options pointer-events-auto absolute bottom-24 left-3 right-3 max-h-[calc(100%-112px)] overflow-y-auto rounded-2xl border border-white/15 bg-gray-900 p-3 text-white shadow-2xl sm:left-auto sm:right-4 sm:w-80">
      <div class="mb-2 flex items-center justify-between px-2">
        <span class="text-sm font-semibold">{{ t('videoCall.toolbar.more') }}</span>
        <button type="button" class="flex h-10 w-10 items-center justify-center rounded-lg hover:bg-white/10" :aria-label="t('videoCall.toolbar.close')" @click="closeMore(true)">✕</button>
      </div>
      <VideoCallInteractions ref="reactions" embedded :overlay-target="overlay" :hand-raised="handRaised" :display-name="displayName"
        :reactions-enabled="connected" :send-reaction="sendReaction" class="!flex-col !items-stretch"
        @toggle-hand="$emit('toggle-hand')" />
      <div class="mt-2 grid gap-1 border-t border-white/10 pt-2">
        <button type="button" class="option-btn" @click="action('chat')">{{ t('videoCall.chat.open') }} <span v-if="unreadCount" class="rounded-full bg-emerald-600 px-2">{{ unreadCount }}</span></button>
        <button type="button" class="option-btn" @click="action('fullscreen')">{{ t(fullscreen ? 'videoCall.minimize' : 'videoCall.fullscreen') }}</button>
        <button type="button" class="option-btn" @click="action('fit')">{{ t(viewMode === 'cover' ? 'videoCall.viewMode.fit' : 'videoCall.viewMode.cover') }}</button>
        <slot name="session-action" />
      </div>
    </div>

    <div v-show="visible" ref="bar" class="main-call-bar pointer-events-auto absolute bottom-4 left-3 flex max-w-[calc(100%-92px)] items-center gap-1 rounded-2xl border border-white/15 bg-gray-900/80 px-2 py-2 text-white shadow-xl backdrop-blur sm:left-1/2 sm:max-w-[calc(100%-160px)] sm:-translate-x-1/2 sm:gap-2 sm:px-3"
      @pointerenter="hovering = $event.pointerType === 'mouse'" @pointerleave="hovering = false" @pointerdown="revealControls" @focusin="revealControls" @focusout="scheduleHide">
      <slot />
      <button type="button" class="fullscreen-btn" :aria-label="t(fullscreen ? 'videoCall.minimize' : 'videoCall.fullscreen')"
        :title="t(fullscreen ? 'videoCall.minimize' : 'videoCall.fullscreen')" @click="$emit('fullscreen'); revealControls()">
        <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
          <path v-if="fullscreen" d="M9 9H5V5M15 9h4V5M9 15H5v4M15 15h4v4" />
          <path v-else d="M9 3H5a2 2 0 0 0-2 2v4M15 3h4a2 2 0 0 1 2 2v4M9 21H5a2 2 0 0 1-2-2v-4M15 21h4a2 2 0 0 0 2-2v-4" />
        </svg>
      </button>
      <button ref="moreButton" type="button" class="more-btn relative" :aria-label="t('videoCall.toolbar.more')" :title="t('videoCall.toolbar.more')" :aria-expanded="moreOpen" aria-haspopup="dialog" @click="toggleMore">
        <span aria-hidden="true" class="text-2xl leading-none">⋯</span>
        <span v-if="unreadCount > 0" class="absolute -right-1 -top-1 h-2.5 w-2.5 rounded-full bg-emerald-400" />
      </button>
    </div>
  </div>
</template>

<style scoped>
.more-btn, .fullscreen-btn { display: inline-flex; width: 44px; height: 44px; flex-shrink: 0; align-items: center; justify-content: center; border-radius: 14px; background: rgb(255 255 255 / .1); }
.more-btn:hover, .fullscreen-btn:hover, .option-btn:hover { background: rgb(255 255 255 / .15); }
.option-btn { display: flex; min-height: 44px; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 12px; border-radius: 12px; text-align: start; font-size: 14px; }
.floating-action { display: inline-flex; height: 48px; width: 48px; align-items: center; justify-content: center; border-radius: 9999px; }
.call-options { scrollbar-width: thin; scrollbar-color: #5997ac #1f2937; }
.call-options::-webkit-scrollbar { width: 7px; }
.call-options::-webkit-scrollbar-track { background: #1f2937; border-radius: 9999px; }
.call-options::-webkit-scrollbar-thumb { background: #5997ac; border-radius: 9999px; }
.call-options::-webkit-scrollbar-thumb:hover { background: #477e90; }
@media (max-width: 359px) {
  .main-call-bar :slotted(.control-btn), .main-call-bar .more-btn, .main-call-bar .fullscreen-btn { width: 36px; height: 40px; flex-shrink: 0; }
}
button:focus-visible { outline: 2px solid #34d399; outline-offset: 2px; }
@media (max-width: 639px) {
  .call-options { padding-bottom: max(12px, env(safe-area-inset-bottom)); }
}
</style>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { formatCallMessage } from '../../utils/callInteractions.js'

const props = defineProps({
  handRaised: Boolean,
  displayName: { type: String, default: '' },
  reactionsEnabled: Boolean,
  sendReaction: { type: Function, required: true },
  embedded: Boolean,
  overlayTarget: { default: null },
})
defineEmits(['toggle-hand'])
const { t } = useI18n()
const reactions = [
  { id: 'thumbs-up', emoji: '👍' }, { id: 'happy', emoji: '😊' },
  { id: 'sad', emoji: '😢' }, { id: 'love', emoji: '❤️' },
  { id: 'laugh', emoji: '😂' }, { id: 'applause', emoji: '👏' },
  { id: 'celebrate', emoji: '🎉' }, { id: 'surprised', emoji: '😮' },
]
const root = ref(null)
const pickerButton = ref(null)
const pickerOpen = ref(false)
const floatingReactions = ref([])
const announcement = ref('')
const coolingDown = ref(false)
const timers = new Set()
let nextId = 0

function later(callback, delay) {
  const timer = setTimeout(() => { timers.delete(timer); callback() }, delay)
  timers.add(timer)
}

function receiveReaction(id, name) {
  const reaction = reactions.find(item => item.id === id)
  if (!reaction) return
  const entry = { ...reaction, name, key: ++nextId }
  floatingReactions.value = [...floatingReactions.value.slice(-5), entry]
  announcement.value = formatCallMessage(t, 'videoCall.interactions.reacted', {
    name, reaction: t(`videoCall.interactions.${id}`),
  })
  later(() => { floatingReactions.value = floatingReactions.value.filter(item => item.key !== entry.key) }, 3500)
}

function selectReaction(id) {
  if (!props.reactionsEnabled || coolingDown.value) return
  if (!props.sendReaction(id)) {
    announcement.value = t('videoCall.interactions.unavailable')
    return
  }
  receiveReaction(id, props.displayName || t('videoCall.you'))
  coolingDown.value = true
  later(() => { coolingDown.value = false }, 700)
}

function closePicker(event) {
  if (!root.value?.contains(event.target)) pickerOpen.value = false
}
function onEscape(event) {
  if (event.key === 'Escape' && pickerOpen.value) {
    pickerOpen.value = false
    pickerButton.value?.focus()
  }
}
onMounted(() => {
  document.addEventListener('pointerdown', closePicker)
  document.addEventListener('keydown', onEscape)
})
onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', closePicker)
  document.removeEventListener('keydown', onEscape)
  for (const timer of timers) clearTimeout(timer)
})
defineExpose({ receiveReaction })
</script>

<template>
  <div ref="root" class="flex shrink-0 items-center gap-2 sm:gap-3">
    <Teleport :to="overlayTarget" :disabled="!overlayTarget">
    <span role="status" aria-live="polite" class="sr-only">{{ announcement }}</span>
    <div aria-hidden="true" class="pointer-events-none absolute bottom-full left-0 z-40 mb-4 flex max-w-full flex-wrap items-end gap-2">
      <div v-for="reaction in floatingReactions" :key="reaction.key" class="reaction-float flex flex-col items-center rounded-2xl bg-gray-900/80 px-3 py-2 text-white shadow-lg">
        <span class="text-4xl">{{ reaction.emoji }}</span>
        <span class="mt-1 max-w-[120px] truncate text-xs">{{ reaction.name }}</span>
      </div>
    </div>
    </Teleport>
    <div v-if="pickerOpen || embedded" role="group" :aria-label="t('videoCall.interactions.reactions')"
      :class="embedded ? 'grid w-full grid-cols-4 gap-1' : 'absolute bottom-full left-1/2 z-50 mb-3 grid w-60 max-w-[calc(100vw-64px)] -translate-x-1/2 grid-cols-4 gap-1 rounded-2xl border border-white/20 bg-gray-900 p-2 shadow-xl'">
      <button v-for="reaction in reactions" :key="reaction.id" type="button"
        class="flex h-11 items-center justify-center rounded-xl text-2xl hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 disabled:opacity-40"
        :disabled="!reactionsEnabled || coolingDown"
        :title="t(`videoCall.interactions.${reaction.id}`)"
        :aria-label="t(`videoCall.interactions.${reaction.id}`)"
        @click="selectReaction(reaction.id)">{{ reaction.emoji }}</button>
    </div>
    <button v-if="!embedded" ref="pickerButton" type="button" class="interaction-btn bg-white/10 hover:bg-white/15 disabled:opacity-40"
      :disabled="!reactionsEnabled" :aria-expanded="pickerOpen"
      :title="t('videoCall.interactions.reactions')" :aria-label="t('videoCall.interactions.reactions')"
      @click="pickerOpen = !pickerOpen"><span aria-hidden="true">😊</span></button>
    <button type="button" :class="[embedded ? 'flex min-h-11 w-full items-center gap-3 rounded-xl px-3 text-sm' : 'interaction-btn',
      handRaised ? 'bg-amber-400 text-gray-950' : 'bg-white/10 hover:bg-white/15']"
      :aria-pressed="handRaised" :title="t(handRaised ? 'videoCall.interactions.lowerHand' : 'videoCall.interactions.raiseHand')"
      :aria-label="t(handRaised ? 'videoCall.interactions.lowerHand' : 'videoCall.interactions.raiseHand')"
      @click="$emit('toggle-hand')"><span aria-hidden="true">✋</span><span v-if="embedded">{{ t(handRaised ? 'videoCall.interactions.lowerHand' : 'videoCall.interactions.raiseHand') }}</span></button>
  </div>
</template>

<style scoped>
.interaction-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  border-radius: 14px;
  border: 1px solid rgb(255 255 255 / 0.15);
  font-size: 23px;
}
.interaction-btn:focus-visible { outline: 2px solid #34d399; outline-offset: 2px; }
.reaction-float { animation: reaction-rise 3.5s ease-out forwards; }
@keyframes reaction-rise {
  0% { opacity: 0; transform: translateY(12px) scale(.8); }
  12%, 75% { opacity: 1; }
  100% { opacity: 0; transform: translateY(-80px); }
}
@media (prefers-reduced-motion: reduce) { .reaction-float { animation: none; } }
</style>

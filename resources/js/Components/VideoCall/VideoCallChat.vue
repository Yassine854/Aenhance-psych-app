<template>
  <div class="chat-panel flex flex-col h-full w-80 max-w-full border-l border-gray-200 bg-white shadow-2xl">
    <div class="chat-header flex items-center justify-between px-4 py-3 border-b bg-gradient-to-r from-emerald-50 to-white font-semibold">
      <div class="flex items-center gap-3">
        <div class="h-9 w-9 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-semibold">💬</div>
        <div>
          <div class="text-sm font-semibold">{{ t('videoCall.chat.title') }}</div>
          <div class="text-xs text-gray-400">{{ t('videoCall.chat.subtitle') }}</div>
        </div>
      </div>
      <button @click="$emit('close')" class="ml-2 text-gray-400 hover:text-emerald-500 transition p-1 rounded-full focus:outline-none" :aria-label="t('videoCall.chat.close')">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div ref="chatBody" class="chat-body flex-1 overflow-y-auto px-4 py-3 space-y-3 bg-gradient-to-b from-white to-emerald-50">
        <div v-for="msg in messages" :key="msg.id" :class="['chat-msg flex', msg.isOwn ? 'justify-end' : 'justify-start']">
          <div class="flex flex-col items-end" v-if="msg.isOwn">
            <div class="chat-bubble own inline-block max-w-[72%] px-3 py-2 rounded-2xl shadow text-sm bg-emerald-500 text-white font-medium overflow-hidden box-border">
              <span v-if="msg.type === 'text'" class="chat-text" dir="auto">{{ msg.text }}</span>
              <div v-else-if="msg.type === 'file'">
                <div class="file-card flex flex-wrap items-start sm:items-center gap-3 w-full">
                  <div class="file-thumb h-16 w-16 sm:h-12 sm:w-12 flex-shrink-0 flex items-center justify-center rounded-md bg-emerald-600/10 text-emerald-50 overflow-hidden">
                    <img v-if="isImage(msg)" :src="msg.fileUrl" class="max-h-full max-w-full object-contain" />
                    <svg v-else class="h-6 w-6 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium truncate">{{ msg.fileName }}</div>
                    <div class="text-xs text-emerald-100 mt-0.5">{{ formatSize(msg.fileSize) }}</div>
                  </div>
                  <div class="mt-2 sm:mt-0 sm:ml-3 flex-shrink-0">
                    <a :href="msg.fileUrl" :download="msg.fileName" class="inline-flex items-center px-3 py-1.5 bg-white/10 text-white rounded-full text-xs hover:bg-white/20">{{ t('videoCall.chat.download') }}</a>
                  </div>
                </div>
              </div>
            </div>
            <div class="text-xs text-gray-400 mt-1 pr-1">{{ t('videoCall.chat.you') }}</div>
          </div>
          <div class="flex flex-col items-start" v-else>
            <div class="chat-bubble other inline-block max-w-[72%] px-3 py-2 rounded-2xl shadow text-sm bg-gray-100 text-gray-900 font-medium overflow-hidden box-border">
              <span v-if="msg.type === 'text'" class="chat-text" dir="auto">{{ msg.text }}</span>
              <div v-else-if="msg.type === 'file'">
                <div class="file-card flex flex-wrap items-start sm:items-center gap-3 w-full">
                  <div class="file-thumb h-16 w-16 sm:h-12 sm:w-12 flex-shrink-0 flex items-center justify-center rounded-md bg-gray-100 text-gray-600 overflow-hidden">
                    <img v-if="isImage(msg)" :src="msg.fileUrl" class="max-h-full max-w-full object-contain" />
                    <svg v-else class="h-6 w-6 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium truncate">{{ msg.fileName }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">{{ formatSize(msg.fileSize) }}</div>
                  </div>
                  <div class="mt-2 sm:mt-0 sm:ml-3 flex-shrink-0">
                    <a :href="msg.fileUrl" :download="msg.fileName" class="inline-flex items-center px-3 py-1.5 bg-emerald-500 text-white rounded-full text-xs hover:bg-emerald-600">{{ t('videoCall.chat.download') }}</a>
                  </div>
                </div>
              </div>
            </div>
            <div class="text-xs text-gray-400 mt-1 pl-1">{{ msg.displayName }}</div>
          </div>
        </div>
      </div>
    <form class="chat-input flex items-center gap-2 px-4 py-3 border-t bg-white" @submit.prevent="sendMessage">
      <input v-model="input" type="text" :dir="isRtl ? 'rtl' : 'ltr'" class="flex-1 min-w-0 border border-gray-200 rounded-full px-3 py-2 text-sm sm:text-base focus:ring-2 focus:ring-emerald-200 focus:outline-none bg-emerald-50" :placeholder="t('videoCall.chat.placeholder')" :disabled="!sessionActive" @focus="emitOpened" />
      <input ref="fileInput" type="file" class="hidden" @change="handleFile" :disabled="!sessionActive" />
      <button type="button" class="p-2 sm:p-2 text-emerald-600 hover:bg-emerald-100 rounded-full transition flex-shrink-0" @click="triggerFile" :disabled="!sessionActive" :title="t('videoCall.chat.attach')"><svg class="h-5 w-5 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.586-6.586a4 4 0 10-5.656-5.656l-6.586 6.586"/></svg></button>
      <button type="submit" class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-sm bg-emerald-500 text-white rounded-full hover:bg-emerald-600 transition disabled:opacity-50 flex-shrink-0" :disabled="!input || !sessionActive">{{ t('videoCall.chat.send') }}</button>
    </form>
  </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'

  const i18n = useI18n()
  const { t } = i18n

  const rtlLangs = ['ar', 'he', 'fa', 'ur']
  const isRtl = computed(() => {
    try {
      const loc = typeof i18n.locale === 'function' ? i18n.locale() : (i18n.locale?.value ?? i18n.locale)
      const code = String(loc || '').split('-')[0]
      return rtlLangs.includes(code)
    } catch {
      return false
    }
  })

const emit = defineEmits(['new-message', 'close', 'chat-opened'])

const props = defineProps({
  ws: Object, // WebSocket instance from parent
  displayName: String,
  role: String,
  sessionActive: Boolean,
})

const input = ref('')
const messages = ref([])
const chatBody = ref(null)
const fileInput = ref(null)

function scrollToBottom() {
  nextTick(() => {
    if (chatBody.value) chatBody.value.scrollTop = chatBody.value.scrollHeight
  })
}

function sendMessage() {
  if (!input.value.trim() || !props.sessionActive) return
  const msg = {
    id: Date.now() + Math.random(),
    type: 'text',
    text: input.value,
    displayName: props.displayName,
    role: props.role,
    isOwn: true,
  }
  messages.value.push(msg)
  emit('new-message')
  if (props.ws && props.ws.readyState === WebSocket.OPEN) {
    props.ws.send(JSON.stringify({ type: 'chat', payload: { ...msg, isOwn: undefined } }))
  }
  input.value = ''
  scrollToBottom()
}

function triggerFile() {
  if (fileInput.value) fileInput.value.click()
}

function handleFile(e) {
  const file = e.target.files[0]
  if (!file || !props.sessionActive) return
  const reader = new FileReader()
  reader.onload = () => {
    const msg = {
      id: Date.now() + Math.random(),
      type: 'file',
      fileName: file.name,
      fileUrl: reader.result,
      fileType: file.type,
      fileSize: file.size,
      displayName: props.displayName,
      role: props.role,
      isOwn: true,
    }
    messages.value.push(msg)
    emit('new-message')
    if (props.ws && props.ws.readyState === WebSocket.OPEN) {
      props.ws.send(JSON.stringify({ type: 'chat', payload: { ...msg, isOwn: undefined } }))
    }
    scrollToBottom()
  }
  reader.readAsDataURL(file)
  e.target.value = ''
}

function isImage(msg) {
  try {
    if (msg.fileType) return String(msg.fileType).startsWith('image/')
    return String(msg.fileUrl || '').startsWith('data:image')
  } catch { return false }
}

function formatSize(bytes) {
  if (!bytes && bytes !== 0) return ''
  const b = Number(bytes) || 0
  if (b < 1024) return b + ' B'
  if (b < 1024 * 1024) return (b / 1024).toFixed(1) + ' KB'
  return (b / (1024 * 1024)).toFixed(2) + ' MB'
}

function receiveMessage(msg) {
  if (msg.role === props.role && msg.displayName === props.displayName) return // skip own
  messages.value.push({ ...msg, isOwn: false })
  emit('new-message')
  scrollToBottom()
}

watch(() => props.sessionActive, (active) => {
  if (!active) messages.value = []
})

function emitOpened() {
  emit('chat-opened')
}



// Expose receiveMessage and scrollToBottom for parent
defineExpose({ receiveMessage, scrollToBottom })
</script>

<style scoped>
.chat-panel { width: min(320px, 86vw); min-width: 180px; max-width: 100%; }
.chat-header { border-bottom-width: 1px; }
.chat-body { background: #f9fafb; }
.chat-msg { margin-bottom: 0.5rem; }

/* Ensure the immediate wrapper inside the flex row occupies full width
  so bubbles can expand to use available horizontal space. */
.chat-msg > div { width: 100%; }

/* Small screen tweaks */
@media (max-width: 420px) {
  .chat-panel { width: min(300px, 92vw); }
  .chat-input input { font-size: .95rem; padding-top: .5rem; padding-bottom: .5rem; }
  .chat-input button[type="submit"] { padding-left: .75rem; padding-right: .75rem; }
}

@media (max-width: 350px) {
  .chat-panel { width: 92vw; min-width: 0; }
  .chat-input input { font-size: .9rem; }
  .chat-input button[type="submit"] { padding-left: .5rem; padding-right: .5rem; font-size: .85rem; }
}

/* Chat bubble fixes for single-word messages and wrapping */
.chat-bubble {
  display: inline-block;
  vertical-align: middle;
  /* Expand bubble to use available horizontal space (like messenger/whatsapp) */
  display: block; /* occupy full line width up to max-width */
  max-width: 92%;
  width: auto;
  line-height: 1.35;
  /* Wrap at whitespace; avoid mid-word breaks unless necessary */
  white-space: pre-wrap;
  word-break: normal;
  overflow-wrap: break-word;
}
.chat-bubble.own { margin-left: auto; text-align: left; }
.chat-bubble.other { margin-right: auto; text-align: left; }

@media (max-width: 420px) {
  .chat-bubble { min-width: 40px; }
}
@media (max-width: 350px) {
  .chat-bubble { min-width: 32px; }
}

/* Prevent mid-word breaks; allow horizontal scroll for very long non-breaking tokens */
/* remove horizontal scrolling; allow sensible wrapping */
.chat-bubble { overflow-x: visible; }
.chat-text { white-space: pre-wrap; overflow-wrap: break-word; word-break: normal; display: block; }

/* RTL tweaks: align input text to the right when using RTL locales */
.chat-input input[dir="rtl"] { text-align: right; }
</style>

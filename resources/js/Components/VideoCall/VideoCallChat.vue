<template>
  <div class="chat-panel flex flex-col h-full w-80 max-w-full border-l border-gray-200 bg-white shadow-2xl">
    <div class="chat-header flex items-center justify-between px-4 py-3 border-b bg-gradient-to-r from-emerald-50 to-white font-semibold">
      <div class="flex items-center gap-3">
        <div class="h-9 w-9 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-semibold">💬</div>
        <div>
          <div class="text-sm font-semibold">Session Chat</div>
          <div class="text-xs text-gray-400">Private session messages</div>
        </div>
      </div>
      <button @click="$emit('close')" class="ml-2 text-gray-400 hover:text-emerald-500 transition p-1 rounded-full focus:outline-none" aria-label="Close chat">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div ref="chatBody" class="chat-body flex-1 overflow-y-auto px-4 py-3 space-y-3 bg-gradient-to-b from-white to-emerald-50">
        <div v-for="msg in messages" :key="msg.id" :class="['chat-msg flex', msg.isOwn ? 'justify-end' : 'justify-start']">
          <div class="flex flex-col items-end" v-if="msg.isOwn">
            <div class="inline-block max-w-xs px-4 py-2 rounded-2xl shadow text-sm bg-emerald-500 text-white font-medium">
              <span v-if="msg.type === 'text'">{{ msg.text }}</span>
              <div v-else-if="msg.type === 'file'">
                <div class="file-card flex items-center gap-3">
                  <div class="file-thumb h-12 w-12 flex items-center justify-center rounded-md bg-emerald-600/10 text-emerald-50 overflow-hidden">
                    <img v-if="isImage(msg)" :src="msg.fileUrl" class="h-full w-full object-cover" />
                    <svg v-else class="h-6 w-6 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                  </div>
                  <div class="min-w-0">
                    <div class="text-sm font-medium truncate">{{ msg.fileName }}</div>
                    <div class="text-xs text-emerald-100 mt-0.5">{{ formatSize(msg.fileSize) }}</div>
                  </div>
                  <a :href="msg.fileUrl" :download="msg.fileName" class="ml-3 inline-flex items-center px-3 py-1.5 bg-white/10 text-white rounded-full text-xs hover:bg-white/20">Download</a>
                </div>
              </div>
            </div>
            <div class="text-xs text-gray-400 mt-1 pr-1">You</div>
          </div>
          <div class="flex flex-col items-start" v-else>
            <div class="inline-block max-w-xs px-4 py-2 rounded-2xl shadow text-sm bg-gray-100 text-gray-900 font-medium">
              <span v-if="msg.type === 'text'">{{ msg.text }}</span>
              <div v-else-if="msg.type === 'file'">
                <div class="file-card flex items-center gap-3">
                  <div class="file-thumb h-12 w-12 flex items-center justify-center rounded-md bg-gray-100 text-gray-600 overflow-hidden">
                    <img v-if="isImage(msg)" :src="msg.fileUrl" class="h-full w-full object-cover" />
                    <svg v-else class="h-6 w-6 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                  </div>
                  <div class="min-w-0">
                    <div class="text-sm font-medium truncate">{{ msg.fileName }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">{{ formatSize(msg.fileSize) }}</div>
                  </div>
                  <a :href="msg.fileUrl" :download="msg.fileName" class="ml-3 inline-flex items-center px-3 py-1.5 bg-emerald-500 text-white rounded-full text-xs hover:bg-emerald-600">Download</a>
                </div>
              </div>
            </div>
            <div class="text-xs text-gray-400 mt-1 pl-1">{{ msg.displayName }}</div>
          </div>
        </div>
      </div>
    <form class="chat-input flex items-center gap-2 px-4 py-3 border-t bg-white" @submit.prevent="sendMessage">
      <input v-model="input" type="text" class="flex-1 border border-gray-200 rounded-full px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-200 focus:outline-none bg-emerald-50" placeholder="Type a message..." :disabled="!sessionActive" @focus="emitOpened" />
      <input ref="fileInput" type="file" class="hidden" @change="handleFile" :disabled="!sessionActive" />
      <button type="button" class="p-2 text-emerald-600 hover:bg-emerald-100 rounded-full transition" @click="triggerFile" :disabled="!sessionActive" title="Attach file"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.586-6.586a4 4 0 10-5.656-5.656l-6.586 6.586"/></svg></button>
      <button type="submit" class="px-4 py-2 text-sm bg-emerald-500 text-white rounded-full hover:bg-emerald-600 transition disabled:opacity-50" :disabled="!input || !sessionActive">Send</button>
    </form>
  </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'

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
.chat-panel { width: 320px; min-width: 220px; max-width: 100%; }
.chat-header { border-bottom-width: 1px; }
.chat-body { background: #f9fafb; }
.chat-msg { margin-bottom: 0.5rem; }
</style>

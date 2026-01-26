<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="$emit('close')"></div>
    <div class="relative w-full max-w-7xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full overflow-hidden bg-white/10 flex items-center justify-center ring-2 ring-white/25">
              <img v-if="avatarUrl" :src="avatarUrl" alt="avatar" class="w-full h-full object-cover" />
              <span v-else class="text-white font-semibold">{{ initials }}</span>
            </div>
            <div>
              <div class="text-white text-lg font-semibold">{{ patient?.name || 'Patient' }}</div>
              <div class="text-sm text-white/90">
                {{ noteCount }} sessions
                <span v-if="patient?.age"> • {{ patient.age }} yrs</span>
              </div>
            </div>
          </div>
          <div>
            <button @click="$emit('close')" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
          </div>
        </div>
      </div>

      <!-- Side arrows (book-style) -->
      <button v-if="currentIndex > 0" @click="prev" aria-label="Previous note" class="side-arrow left-3 lg:left-6">
        <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M12 16L6 10l6-6" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      <button v-if="currentIndex < notes.length - 1" @click="next" aria-label="Next note" class="side-arrow right-3 lg:right-6">
        <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M8 4l6 6-6 6" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>

      <div class="bg-white p-6 max-h-[70vh] overflow-y-auto styled-scrollbar">
        <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-3 md:items-center">
          <div class="md:col-span-2 text-sm text-gray-600">
            <div>Session date: <span class="font-medium">{{ currentNote.session_date_display }}</span></div>
            <div class="text-xs text-gray-500 mt-1">Duration: <span class="font-medium">{{ currentNote.session_duration ? (currentNote.session_duration + ' mins') : '—' }}</span></div>
          </div>
          <div class="flex items-center gap-3 justify-start md:justify-end">
            <div class="flex items-center gap-2">
              <input v-model="startDate" type="date" class="rounded border-gray-300 px-3 py-2 bg-gray-50 text-sm" />
              <input v-model="endDate" type="date" class="rounded border-gray-300 px-3 py-2 bg-gray-50 text-sm" />
              <button @click="fetchNotes" :style="{ background: 'rgb(89 151 172 / var(--tw-bg-opacity, 1))' }" class="px-3 py-2 rounded text-white shadow-sm">Filter</button>
              <button v-if="startDate || endDate" @click="clearFilters" class="px-2 py-2 rounded border text-gray-600 bg-white">Clear</button>
            </div>

            <div class="flex items-center gap-2">
              <span v-if="startDate" class="filter-tag">Start: {{ formatDate(startDate) }} <button @click="clearStart" class="tag-x">✕</button></span>
              <span v-if="endDate" class="filter-tag">End: {{ formatDate(endDate) }} <button @click="clearEnd" class="tag-x">✕</button></span>
            </div>
          </div>
        </div>

        

        <div v-if="notes.length === 0" class="text-center text-gray-600 py-12">No notes for selected filters.</div>

        <div v-else class="notebook grid grid-cols-1 gap-6 md:grid-cols-2">
          <div class="book-page p-8 bg-[rgba(89,151,172,0.04)] rounded border border-[rgba(89,151,172,0.06)]">
            <div class="book-meta mb-4">
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-[rgb(24,58,63)] font-semibold text-lg">Session Note #{{ totalNotes - currentIndex }}</div>
                  <div class="text-xs text-gray-500 mt-1">Mode: <span class="ml-1 font-medium text-[rgb(24,58,63)]">{{ currentNote.session_mode || '-' }}</span></div>
                </div>
                <div class="flex items-center gap-3">
                  <div class="text-sm text-gray-500">{{ currentNote.session_date_display }}</div>
                </div>
              </div>
            </div>

            <div class="space-y-5 text-sm text-[rgb(24,58,63)]">
              <div class="book-section">
                <div class="book-heading">Risk Level</div>
                <div class="mt-1">
                  <span v-if="currentNote.risk_level" :style="riskStyle(currentNote.risk_level)">{{ currentNote.risk_level }}</span>
                  <span v-else class="text-gray-500">-</span>
                </div>
              </div>

              <div class="book-section">
                <div class="book-heading">Subjective</div>
                <div class="mt-1">{{ currentNote.subjective || '-' }}</div>
              </div>

              <div class="book-section">
                <div class="book-heading">Objective</div>
                <div class="mt-1">{{ currentNote.objective || '-' }}</div>
              </div>
            </div>
          </div>

          <div class="book-page p-8 bg-white rounded border border-[rgba(15,23,42,0.04)]">
            <div class="space-y-5 text-sm text-[rgb(24,58,63)]">
              <div class="book-section">
                <div class="book-heading">Assessment</div>
                <div class="mt-1">{{ currentNote.assessment || '-' }}</div>
              </div>

              <div class="book-section">
                <div class="book-heading">Intervention</div>
                <div class="mt-1">{{ currentNote.intervention || '-' }}</div>
              </div>

              <div class="book-section">
                <div class="book-heading">Plan</div>
                <div class="mt-1">{{ currentNote.plan || '-' }}</div>
              </div>

              <!-- Additional Details removed as requested -->
            </div>
          </div>
        </div>
      </div>

      <div class="px-4 py-3 border-t flex items-center justify-between text-sm text-gray-600 bg-white">
        <div>Note {{ currentIndex + 1 }} of {{ notes.length }}</div>
        <div class="flex items-center gap-3">
          <button @click="goToFirst" class="first-last-btn" title="Go to first note">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M19 18L13 12l6-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M11 18L5 12l6-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>

          <nav class="pagination flex items-center gap-2" aria-label="Notes pagination">
            <button v-for="p in pages" :key="p.key" @click="goToPage(p.index)" :class="['page-btn', { active: p.index === currentIndex } ]" v-html="p.label"></button>
          </nav>

          <button @click="goToLast" class="first-last-btn" title="Go to last note">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M5 6l6 6-6 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({ patient: Object })
const emit = defineEmits(['close'])

const notes = ref([])
const currentIndex = ref(0)
const startDate = ref('')
const endDate = ref('')

const visible = computed(() => {
  return Boolean(props.patient && (props.patient.id || Object.keys(props.patient).length > 0))
})

const noteCount = computed(() => notes.value.length)

const avatarUrl = computed(() => {
  try {
    const p = props.patient || {}

    const candidates = [
      p.profile_image_url,
      p.profileImageUrl,
      p.avatar_url,
      p.avatar,
      p.profile_image,
      p.profile_cloudinary,
      p.cloudinary,
    ]

    for (const c of candidates) {
      if (!c) continue
      if (typeof c === 'string') {
        if (c.startsWith('http://') || c.startsWith('https://') || c.startsWith('/')) return c
        return `/${c}`
      }
      if (typeof c === 'object') {
        const url = c.secure_url || c.secureUrl || c.url || c.src || null
        if (url && typeof url === 'string') return url
      }
    }

    if (p.cloudinary && typeof p.cloudinary === 'object') {
      const url = p.cloudinary.secure_url || p.cloudinary.url || null
      if (url) return url
    }

    return null
  } catch {
    return null
  }
})

const initials = computed(() => {
  const name = (props.patient?.name || '').toString().trim()
  if (!name) return 'P'
  const parts = name.split(/\s+/).filter(Boolean)
  return (parts.slice(0,2).map(p => p[0]).join('') || 'P').toUpperCase()
})


const currentNote = computed(() => {
  const n = notes.value[currentIndex.value] || {}
  return {
    ...n,
    session_date_display: n.session_date ? new Date(n.session_date).toLocaleString() : (n.session_date ?? '-'),
    session_duration: n.session_duration ?? n.session_duration,
    created_at_display: n.created_at ? new Date(n.created_at).toLocaleString() : (n.created_at ?? '-'),
    updated_at_display: n.updated_at ? new Date(n.updated_at).toLocaleString() : (n.updated_at ?? '-'),
    note_id: n.id ?? n.note_id ?? n.noteId ?? null,
    session_id: n.session_id ?? n.appointment_session_id ?? n.sessionId ?? null
  }
})

// derive common display fields
Object.defineProperty(currentNote, 'session_mode', {
  get() {
    const n = notes.value[currentIndex.value] || {}
    return n.session_mode ?? n.mode ?? n.sessionMode ?? null
  },
  configurable: true,
})

Object.defineProperty(currentNote, 'risk_level', {
  get() {
    const n = notes.value[currentIndex.value] || {}
    return n.risk_level ?? n.risk ?? n.riskLevel ?? null
  },
  configurable: true,
})

const totalNotes = computed(() => notes.value.length)

const pages = computed(() => {
  const total = totalNotes.value
  const current = currentIndex.value
  const out = []

  // show all when small (<=5)
  if (total <= 5) {
    for (let i = 0; i < total; i++) out.push({ index: i, label: String(i + 1), key: `p${i}` })
    return out
  }

  const windowSize = 5
  let start = current - Math.floor(windowSize / 2)
  let end = start + windowSize - 1

  if (start < 0) {
    start = 0
    end = windowSize - 1
  }
  if (end > total - 1) {
    end = total - 1
    start = total - windowSize
  }

  if (start > 0) out.push({ index: -1, label: '…', key: 'dots1' })

  for (let i = start; i <= end; i++) out.push({ index: i, label: String(i + 1), key: `p${i}` })

  if (end < total - 1) out.push({ index: -2, label: '…', key: 'dots2' })

  return out
})

// keys to hide from the "Additional Details" and metadata
const hiddenKeys = new Set([
  'id', 'created_at', 'updated_at', 'appointment_session_id', 'appointment_session',
  'psychologist_id', 'patient_id', 'session_date', 'session_duration', 'note_id',
  'noteId', 'sessionId'
])

function goToPage(idx) {
  if (idx === -1 || idx === -2) return
  currentIndex.value = Math.max(0, Math.min(totalNotes.value - 1, idx))
}

async function fetchNotes() {
  if (!props.patient || !props.patient.id) return
  const params = {}
  if (startDate.value) params.start_date = startDate.value
  if (endDate.value) params.end_date = endDate.value

    try {
    const res = await axios.get(`/psychologist/patients/${props.patient.id}/notes`, { params })
    notes.value = res.data || []
    currentIndex.value = 0
    // debug: show fetched count and computed pages
    try {
      // pages is a computed ref; access .value for debugging
      // eslint-disable-next-line no-console
      console.log('[NotesBook] fetched notes:', notes.value.length, 'pages:', pages.value)
    } catch (e) {
      // ignore logging errors
    }
  } catch (e) {
    console.error('Failed to fetch notes', e)
    notes.value = []
  }
}

function prev() {
  if (currentIndex.value > 0) currentIndex.value--
}

function next() {
  if (currentIndex.value < notes.value.length - 1) currentIndex.value++
}

function goToFirst() { currentIndex.value = 0 }
function goToLast() { currentIndex.value = Math.max(0, notes.value.length - 1) }

async function clearStart() { startDate.value = ''; await fetchNotes(); }
async function clearEnd() { endDate.value = ''; await fetchNotes(); }
async function clearFilters() { startDate.value = ''; endDate.value = ''; await fetchNotes(); }

function formatDate(d) {
  try { return new Date(d).toLocaleDateString() }
  catch { return d }
}

function formatExtra(v) {
  if (v === null || v === undefined) return '-'
  if (typeof v === 'object') {
    try { return JSON.stringify(v) }
    catch { return String(v) }
  }
  return String(v)
}

function riskStyle(level) {
  if (!level) return {}
  const l = String(level).toLowerCase()
  let bg = 'rgb(34,197,94)'
  if (l.includes('low')) bg = 'rgb(34,197,94)'
  else if (l.includes('medium') || l.includes('moderate')) bg = 'rgb(234,179,8)'
  else if (l.includes('very_high') || l.includes('very high') || (l.includes('very') && l.includes('high'))) bg = 'rgb(239,68,68)'
  else if (l.includes('high')) bg = 'rgb(249,115,22)'
  else if (l.includes('critical') || l.includes('severe')) bg = 'rgb(239,68,68)'
  else bg = 'rgb(99,102,241)'
  return {
    background: bg,
    color: '#fff',
    padding: '4px 8px',
    borderRadius: '9999px',
    fontWeight: 600,
    fontSize: '0.8rem',
    display: 'inline-block'
  }
}

onMounted(() => {
  fetchNotes()
})

watch(() => props.patient, () => {
  fetchNotes()
})

// keyboard navigation
window.addEventListener('keydown', (e) => {
  if (e.key === 'ArrowLeft') prev()
  if (e.key === 'ArrowRight') next()
})
</script>

<style scoped>
.book-page { min-height: 300px; }
.styled-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgb(89 151 172 / var(--tw-bg-opacity, 1)) rgba(229, 231, 235, 1);
}
.styled-scrollbar::-webkit-scrollbar {
  width: 10px;
  height: 10px;
}
.styled-scrollbar::-webkit-scrollbar-track {
  background: rgba(241, 245, 249, 1);
  border-radius: 9999px;
}
.styled-scrollbar::-webkit-scrollbar-thumb {
  background: rgb(89 151 172 / var(--tw-bg-opacity, 1));
  border-radius: 9999px;
  border: 2px solid #ffffff;
}
.styled-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgb(89 151 172 / 0.85);
}

/* Side arrow styling - placed inside the modal with a modern look */
.side-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: rgb(175 81 102 / var(--tw-bg-opacity, 1));
  border: 1px solid rgba(0,0,0,0.06);
  backdrop-filter: blur(4px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.14), inset 0 -3px 8px rgba(0,0,0,0.03);
  cursor: pointer;
  z-index: 60;
  transition: transform 140ms cubic-bezier(.2,.9,.3,1), box-shadow 140ms ease, opacity 120ms ease;
}
.side-arrow:hover { transform: translateY(-50%) scale(1.06); box-shadow: 0 14px 34px rgba(0,0,0,0.22), 0 0 14px rgba(175,81,102,0.12); }
.side-arrow:active { transform: translateY(-50%) scale(0.98); }
.side-arrow svg { width: 16px; height: 16px; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.14)); position: relative; z-index: 2; }
.side-arrow.left-3 { left: 10px; }
.side-arrow.right-3 { right: 10px; }
.side-arrow.left-6 { left: 12px; }
.side-arrow.right-6 { right: 12px; }

/* Accent inner glow to complement the main color */
.side-arrow::before {
  content: '';
  position: absolute;
  inset: 5px;
  border-radius: 6px;
  background: linear-gradient(135deg, rgba(255,255,255,0.06), rgba(0,0,0,0.02));
  mix-blend-mode: overlay;
  z-index: -1;
}

/* Small white accent behind the icon for contrast */
.side-arrow::after {
  content: '';
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  width: 14px;
  height: 14px;
  border-radius: 9999px;
  background: rgba(255,255,255,0.9);
  opacity: 0.09;
  z-index: 1;
  transition: opacity 150ms ease, transform 150ms ease;
}
.side-arrow:hover::after { opacity: 0.2; transform: translate(-50%, -50%) scale(1.06); }

/* Pagination styles */
.pagination { }
.page-btn {
  min-width: 34px;
  height: 34px;
  padding: 0 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  background: transparent;
  border: 1px solid rgba(15,23,42,0.06);
  color: #374151;
  font-size: 0.9rem;
  cursor: pointer;
  transition: background 120ms ease, color 120ms ease, transform 120ms ease;
}
.page-btn:hover { transform: translateY(-2px); }
.page-btn.active {
  background: rgb(89 151 172 / var(--tw-bg-opacity, 1));
  color: white;
  border-color: rgba(0,0,0,0.08);
  box-shadow: 0 8px 18px rgba(175,81,102,0.12), inset 0 -3px 6px rgba(0,0,0,0.06);
}
.page-btn[disabled] { opacity: 0.5; cursor: default; }

/* First/Last button styling */
.first-last-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  padding: 6px;
  border-radius: 8px;
  background: transparent;
  border: 1px solid rgba(15,23,42,0.06);
  color: #374151;
  cursor: pointer;
  transition: background 140ms ease, color 140ms ease, transform 140ms ease, box-shadow 140ms ease;
}
.first-last-btn svg { width: 18px; height: 18px; }
.first-last-btn:hover { transform: translateY(-2px); background: rgba(89,151,172,0.08); color: rgb(89 151 172 / var(--tw-bg-opacity, 1)); box-shadow: 0 8px 18px rgba(89,151,172,0.08); }
.first-last-btn:active { transform: translateY(0); }

/* Filter tag styles */
.filter-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 8px;
  border-radius: 9999px;
  background: rgba(14,165,233,0.04);
  border: 1px solid rgba(15,23,42,0.04);
  color: #374151;
  font-size: 0.85rem;
}
.tag-x {
  background: transparent;
  border: none;
  color: #6b7280;
  padding: 0 4px;
  cursor: pointer;
  font-size: 0.85rem;
}
.tag-x:hover { color: #111827 }

/* Book content styling */
.book-page { box-shadow: none; }
.book-meta { padding: 0.6rem; border-radius: 8px; }
.book-heading { font-weight: 600; color: rgb(24,58,63); margin-bottom: 6px; }
.book-section { background: transparent; padding: 6px 0; border-bottom: 1px dashed rgba(15,23,42,0.04); }
.book-section:last-child { border-bottom: none }
.book-page { background-clip: padding-box }
.book-page .book-heading { color: rgb(89,151,172); }

/* subtle body text color */
.book-page { color: rgb(31,41,55); }

/* metadata accent */
.book-meta .font-medium { color: rgb(89,151,172); }

/* risk badge minor styling fallback */
.risk-badge { padding: 4px 8px; border-radius: 9999px; font-weight: 600; font-size: 0.75rem; }

/* Prevent long labels/text from overflowing — wrap instead */
.notebook > * { min-width: 0; }
.book-page, .book-section { min-width: 0; }
.book-heading { white-space: normal; }
.book-section .mt-1 { white-space: pre-wrap; word-break: break-word; overflow-wrap: anywhere; }
.risk-badge { max-width: 100%; white-space: normal; word-break: break-word; overflow-wrap: anywhere; }
</style>

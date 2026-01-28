<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="$emit('close')"></div>
    <div class="relative w-full max-w-7xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-white text-lg font-semibold">Payout #{{ payout?.id ?? '—' }} details</div>
            <div class="text-sm text-white/90">{{ patient?.name || '' }}</div>
          </div>
          <div>
            <button @click="$emit('close')" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 max-h-[70vh] overflow-y-auto styled-scrollbar">
        <!-- header controls removed; appointment moved into body -->

        <div class="notebook grid grid-cols-1 gap-6 md:grid-cols-2">
          <div class="book-page p-8 bg-[rgba(89,151,172,0.04)] rounded border border-[rgba(89,151,172,0.06)]">
            <div class="book-meta mb-4">
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-[rgb(24,58,63)] font-semibold text-lg">Payout #{{ payout?.id ?? '—' }}</div>
                  <div class="text-xs text-gray-500 mt-1">Status:
                    <span :class="['ml-1 inline-flex items-center px-2 py-1 rounded text-xs font-medium', payoutBadge(payout?.status)]">
                      {{ payout?.status || '—' }}
                    </span>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <div class="text-sm text-gray-500">{{ formatDate(payout?.created_at || payout?.createdAt) }}</div>
                </div>
              </div>
            </div>

            <div class="space-y-5 text-sm text-[rgb(24,58,63)]">
              <div class="book-section">
                <div class="book-heading">Appointment</div>
                <div class="mt-1">Date: <span class="font-medium">{{ formatDate(payout?.appointment?.scheduled_start) }}</span></div>
              </div>
              <div class="book-section">
                <div class="book-heading">Payment</div>
                <div class="mt-1">Gross: <span class="font-medium">{{ payout?.gross_amount || '—' }} {{ payout?.currency || '' }}</span></div>
                <div class="mt-1">Platform fee: <span class="font-medium">{{ payout?.platform_fee || '—' }} {{ payout?.currency || '' }}</span></div>
              </div>

              <div class="book-section">
                <div class="book-heading">Net</div>
                <div class="mt-1 text-lg font-semibold">{{ payout?.net_amount || '—' }} {{ payout?.currency || '' }}</div>
              </div>
            </div>
          </div>

          <div class="book-page p-8 bg-white rounded border border-[rgba(15,23,42,0.04)]">
            <div class="space-y-5 text-sm text-[rgb(24,58,63)]">
              <div class="book-section">
                <div class="book-heading">Estimated availability</div>
                <div class="mt-1">{{ formatDateDateOnly(payout?.estimated_availability) }}</div>
              </div>

              <div class="book-section">
                <div class="book-heading">Paid at</div>
                <div class="mt-1">{{ formatDate(payout?.paid_at) }}</div>
              </div>
              <div class="book-section">
                <div class="book-heading">Refunded at</div>
                <div class="mt-1">{{ formatDate(payout?.refund_at) }}</div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- footer removed (close moved to top-right) -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ payout: Object })

// use computed refs so changes to props.payout update the UI
const payout = computed(() => props.payout || {})

const visible = computed(() => Boolean(payout.value && Object.keys(payout.value).length > 0))

const patient = computed(() => payout.value?.appointment?.patient ?? null)

const avatarUrl = computed(() => {
  try {
    const p = patient.value || {}
    const cands = [p.profile_image_url, p.profileImageUrl, p.avatar_url, p.avatar]
    for (const c of cands) if (c) return c
    return null
  } catch { return null }
})

const initials = computed(() => {
  const name = (patient.value?.name || '').toString().trim()
  if (!name) return 'P'
  const parts = name.split(/\s+/).filter(Boolean)
  return (parts.slice(0,2).map(p => p[0]).join('') || 'P').toUpperCase()
})

function payoutBadge(status) {
  const s = String(status || '').trim().toLowerCase()
  if (s === 'pending') return 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200'
  if (s === 'paid') return 'bg-green-50 text-green-700 ring-1 ring-green-200'
  if (s === 'on_hold') return 'bg-red-50 text-red-700 ring-1 ring-red-200'
  if (s === 'refund') return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}

function formatDate(d) {
  if (!d) return '—'
  try { return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' }).format(new Date(d)) } catch { return String(d) }
}

function formatDateDateOnly(d) {
  if (!d) return '—'
  try { return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: '2-digit' }).format(new Date(d)) } catch { return String(d) }
}
</script>

<style scoped>
/* reuse styling from NotesBook */
.styled-scrollbar { scrollbar-width: thin; scrollbar-color: rgb(89 151 172 / var(--tw-bg-opacity, 1)) rgba(229, 231, 235, 1); }
.styled-scrollbar::-webkit-scrollbar { width: 10px; height: 10px; }
.styled-scrollbar::-webkit-scrollbar-track { background: rgba(241, 245, 249, 1); border-radius: 9999px; }
.styled-scrollbar::-webkit-scrollbar-thumb { background: rgb(89 151 172 / var(--tw-bg-opacity, 1)); border-radius: 9999px; border: 2px solid #ffffff; }
.book-page { min-height: 300px; }
.book-heading { font-weight: 600; color: rgb(89,151,172); margin-bottom: 6px; }
.book-section { background: transparent; padding: 6px 0; border-bottom: 1px dashed rgba(15,23,42,0.04); }
</style>

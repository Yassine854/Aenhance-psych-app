<template>
  <div v-if="show && report" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="close"></div>

    <div class="relative w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-start justify-between gap-4">
          <div class="text-white">
            <div class="text-xl font-semibold leading-tight">Report #{{ report.id }}</div>
            <div class="text-sm opacity-90">{{ formatDate(report.created_at) }}</div>
          </div>
          <div class="flex items-center gap-3">
            <template v-if="!report.is_resolved">
              <button @click="confirmResolve" class="px-3 py-1.5 rounded-lg bg-white text-gray-800 font-medium">Resolve</button>
            </template>
            <button @click="close" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 max-h-[80vh] overflow-y-auto styled-scrollbar">
        <div class="grid grid-cols-1 gap-6">
          <div class="rounded-xl border border-gray-200 p-4">
            <div class="text-sm font-semibold text-gray-900">Participants</div>
            <dl class="mt-3 space-y-3">
              <div class="flex items-start justify-between gap-4">
                <dt class="text-xs font-medium text-gray-500">Reporter</dt>
                <dd class="text-sm font-medium text-gray-900 text-right">{{ report.reporter?.name || report.reporter?.type || '—' }}</dd>
              </div>
              <div class="flex items-start justify-between gap-4">
                <dt class="text-xs font-medium text-gray-500">Reported</dt>
                <dd class="text-sm font-medium text-gray-900 text-right">{{ report.reported?.name || report.reported?.type || '—' }}</dd>
              </div>
            </dl>
          </div>

          <div class="rounded-xl border border-gray-200 p-4">
            <div class="text-sm font-semibold text-gray-900">Details</div>
            <dl class="mt-3 space-y-3">
                <div v-if="report.proof_image" class="mb-4">
                  <div class="text-xs text-gray-500">Proof image</div>
                  <div class="mt-2">
                    <a :href="report.proof_image" target="_blank" class="inline-block">
                      <img :src="report.proof_image" alt="Proof image" class="w-full max-h-60 object-contain rounded" />
                    </a>
                  </div>
                </div>
              <div class="flex items-start justify-between gap-4">
                <dt class="text-xs font-medium text-gray-500">Reason</dt>
                <dd class="text-sm font-medium text-gray-900 text-right">{{ report.reason || '—' }}</dd>
              </div>
              <div v-if="report.proof_image" class="flex items-start justify-between gap-4">
                <dt class="text-xs font-medium text-gray-500">Proof</dt>
                <dd class="text-sm font-medium text-gray-900 text-right">
                  <a :href="report.proof_image" target="_blank" class="text-brand-600 hover:underline">View image</a>
                </dd>
              </div>
              <div class="flex items-start justify-between gap-4">
                <dt class="text-xs font-medium text-gray-500">Status</dt>
                <dd class="text-sm font-medium text-gray-900 text-right">{{ report.is_resolved ? 'Resolved' : 'Open' }}</dd>
              </div>
              <div v-if="report.is_resolved" class="flex items-start justify-between gap-4">
                <dt class="text-xs font-medium text-gray-500">Resolved at</dt>
                <dd class="text-sm font-medium text-gray-900 text-right">{{ formatDate(report.resolved_at) }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const props = defineProps({ show: Boolean, report: Object })
const emit = defineEmits(['close'])

const report = computed(() => props.report || null)
const saving = ref(false)

function close(){ emit('close') }

function formatDate(value) {
  if (!value) return '—'
  try { return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: '2-digit' }).format(new Date(value)) } catch { return String(value) }
}

async function confirmResolve() {
  if (!report.value || saving.value) return
  const res = await Swal.fire({ title: 'Resolve report?', text: `Mark report #${report.value.id} as resolved?`, icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, resolve', cancelButtonText: 'Cancel', confirmButtonColor: 'rgb(89,151,172)' })
  if (!res.isConfirmed) return
  saving.value = true
  await router.patch(route('admin.reports.update', report.value.id), { is_resolved: true }, {
    preserveScroll: true,
    onError: () => { saving.value = false },
    onFinish: () => { saving.value = false }
  })
}
</script>

<style scoped></style>

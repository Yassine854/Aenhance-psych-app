<script setup>
import { computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  kpis: { type: Object, default: () => ({}) },
  charts: { type: Object, default: () => ({}) },
  chart_filter: { type: Object, default: () => ({}) },
})

const themeColors = {
  brand: '#5997ac',
  rose: '#af5166',
  green: '#16a34a',
  amber: '#d97706',
  red: '#dc2626',
  gray: '#6b7280',
}

const periodLabels = computed(() => props.charts?.period_labels || [])
const selectedGranularity = computed(() => props.chart_filter?.granularity || 'months')

function setGranularity(granularity) {
  if (granularity === selectedGranularity.value) return
  router.get(
    route('dashboard'),
    { granularity },
    {
      preserveScroll: true,
      preserveState: true,
      replace: true,
      only: ['kpis', 'charts', 'chart_filter'],
    }
  )
}

const revenueSeries = computed(() => [
  { name: 'Revenue', data: props.charts?.timeline_revenue || [] },
])

const activitySeries = computed(() => [
  { name: 'Appointments', data: props.charts?.timeline_appointments || [] },
  { name: 'Payments', data: props.charts?.timeline_payments || [] },
])

const userGrowthSeries = computed(() => [
  { name: 'New Patients', data: props.charts?.timeline_new_patients || [] },
  { name: 'New Psychologists', data: props.charts?.timeline_new_psychologists || [] },
])

const paymentStatusSeries = computed(() => props.charts?.payment_status?.series || [])
const paymentStatusLabels = computed(() => props.charts?.payment_status?.labels || [])

const appointmentStatusSeries = computed(() => props.charts?.appointment_status?.series || [])
const appointmentStatusLabels = computed(() => props.charts?.appointment_status?.labels || [])

const lineOptions = computed(() => ({
  chart: { toolbar: { show: false }, zoom: { enabled: false } },
  stroke: { curve: 'smooth', width: 3 },
  xaxis: { categories: periodLabels.value },
  yaxis: { labels: { formatter: (value) => Math.round(value) } },
  dataLabels: { enabled: false },
  grid: { borderColor: '#f1f5f9' },
  legend: { position: 'top' },
  tooltip: { shared: true, intersect: false },
}))

const revenueOptions = computed(() => ({
  ...lineOptions.value,
  colors: [themeColors.green],
  yaxis: {
    labels: {
      formatter: (value) => `${Number(value || 0).toFixed(0)} TND`,
    },
  },
}))

const activityOptions = computed(() => ({
  ...lineOptions.value,
  colors: [themeColors.brand, themeColors.rose],
}))

const userGrowthOptions = computed(() => ({
  ...lineOptions.value,
  colors: [themeColors.brand, themeColors.amber],
}))

const paymentStatusOptions = computed(() => ({
  chart: { toolbar: { show: false } },
  labels: paymentStatusLabels.value,
  dataLabels: { enabled: true },
  legend: { position: 'bottom' },
  stroke: { show: false },
  colors: [themeColors.green, themeColors.amber, themeColors.red, themeColors.gray],
}))

const appointmentStatusOptions = computed(() => ({
  chart: { toolbar: { show: false } },
  labels: appointmentStatusLabels.value,
  dataLabels: { enabled: true },
  legend: { position: 'bottom' },
  stroke: { show: false },
  // Order: Pending, Confirmed, Completed, Cancelled, No show
  // Required colors: Completed green, Pending orange, Cancelled red, Confirmed blue, No show gray
  colors: ['#f59e0b', '#3b82f6', '#16a34a', '#dc2626', '#6b7280'],
}))

const dashboardCards = computed(() => [
  {
    label: 'Patients',
    value: props.kpis?.users_patients || 0,
    icon: 'patients',
    cardClass: 'from-sky-50 to-cyan-50 border-sky-200',
    badgeClass: 'bg-sky-100 text-sky-700',
  },
  {
    label: 'Psychologists',
    value: props.kpis?.users_psychologists || 0,
    icon: 'psychologists',
    cardClass: 'from-indigo-50 to-blue-50 border-indigo-200',
    badgeClass: 'bg-indigo-100 text-indigo-700',
  },
  {
    label: 'Appointments',
    value: props.kpis?.appointments_total || 0,
    icon: 'appointments',
    cardClass: 'from-orange-50 to-amber-50 border-amber-200',
    badgeClass: 'bg-amber-100 text-amber-700',
  },
  {
    label: 'Payments',
    value: props.kpis?.payments_total || 0,
    icon: 'payments',
    cardClass: 'from-rose-50 to-pink-50 border-rose-200',
    badgeClass: 'bg-rose-100 text-rose-700',
  },
  {
    label: 'Total revenue',
    value: `${Number(props.kpis?.revenue_total || 0).toFixed(2)} TND`,
    icon: 'revenue',
    cardClass: 'from-emerald-50 to-green-50 border-emerald-200',
    badgeClass: 'bg-emerald-100 text-emerald-700',
  },
])
</script>

<template>
  <Head title="Admin Analytics Dashboard" />

  <div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
      <h1 class="text-2xl font-bold text-gray-900">Analytics dashboard</h1>
      <p class="mt-2 text-sm text-gray-600">
        Global application statistics for users, appointments, payments, and revenue.
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
      <div
        v-for="card in dashboardCards"
        :key="card.label"
        class="rounded-2xl border bg-gradient-to-br p-4 shadow-sm"
        :class="card.cardClass"
      >
        <div class="flex items-center justify-between">
          <div class="text-xs font-semibold text-gray-600">{{ card.label }}</div>
          <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg" :class="card.badgeClass">
            <svg v-if="card.icon === 'patients'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19v-1a4 4 0 00-4-4H7a4 4 0 00-4 4v1" />
              <circle cx="9" cy="7" r="3" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M22 19v-1a4 4 0 00-3-3.87" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 4.13a3 3 0 010 5.74" />
            </svg>

            <svg v-else-if="card.icon === 'psychologists'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <circle cx="12" cy="7" r="3" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 21v-1.5A4.5 4.5 0 019.5 15h5a4.5 4.5 0 014.5 4.5V21" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M18.5 10.5v5M16 13h5" />
            </svg>

            <svg v-else-if="card.icon === 'appointments'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <rect x="3" y="4" width="18" height="18" rx="2" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 2v4M16 2v4M3 10h18" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 14h3v3H8z" />
            </svg>

            <svg v-else-if="card.icon === 'payments'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <rect x="2" y="5" width="20" height="14" rx="2" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M2 10h20" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 15h4" />
            </svg>

            <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 17l5-5 4 4 7-7" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M14 9h5v5" />
            </svg>
          </span>
        </div>
        <div class="mt-3 text-2xl font-extrabold text-gray-900 tracking-tight">{{ card.value }}</div>
      </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
      <div class="flex flex-wrap items-center gap-2">
        <span class="text-sm font-semibold text-gray-700 mr-2">Chart period:</span>
        <button
          type="button"
          @click="setGranularity('days')"
          class="px-3 py-1.5 rounded-lg text-sm font-semibold border transition"
          :class="selectedGranularity === 'days' ? 'bg-[#5997ac] text-white border-[#5997ac]' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
        >
          Days
        </button>
        <button
          type="button"
          @click="setGranularity('months')"
          class="px-3 py-1.5 rounded-lg text-sm font-semibold border transition"
          :class="selectedGranularity === 'months' ? 'bg-[#5997ac] text-white border-[#5997ac]' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
        >
          Months
        </button>
        <button
          type="button"
          @click="setGranularity('years')"
          class="px-3 py-1.5 rounded-lg text-sm font-semibold border transition"
          :class="selectedGranularity === 'years' ? 'bg-[#5997ac] text-white border-[#5997ac]' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
        >
          Years
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-900">Revenue trend</h2>
          <span class="text-xs font-medium text-green-700 bg-green-50 px-2.5 py-1 rounded-full">
            {{ Number(kpis?.payment_success_rate || 0).toFixed(1) }}% payment success
          </span>
        </div>
        <apexchart type="line" height="320" :options="revenueOptions" :series="revenueSeries" />
      </div>

      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-semibold text-gray-900">Appointments vs Payments</h2>
          <span class="text-xs font-medium text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full">
            {{ Number(kpis?.appointment_completion_rate || 0).toFixed(1) }}% completed appointments
          </span>
        </div>
        <apexchart type="line" height="320" :options="activityOptions" :series="activitySeries" />
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <div class="xl:col-span-2 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="text-base font-semibold text-gray-900">User growth</h2>
        <apexchart type="bar" height="320" :options="userGrowthOptions" :series="userGrowthSeries" />
      </div>

      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="text-base font-semibold text-gray-900">Payment status distribution</h2>
        <apexchart type="donut" height="320" :options="paymentStatusOptions" :series="paymentStatusSeries" />
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="text-base font-semibold text-gray-900">Appointment status distribution</h2>
        <apexchart type="donut" height="320" :options="appointmentStatusOptions" :series="appointmentStatusSeries" />
      </div>

      <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="text-base font-semibold text-gray-900">Payment health</h2>
        <div class="mt-4 grid grid-cols-2 gap-4">
          <div class="rounded-xl border border-green-200 bg-green-50 p-4">
            <div class="text-xs font-semibold text-green-700">Paid</div>
            <div class="mt-1 text-2xl font-bold text-green-900">{{ kpis?.payments_paid || 0 }}</div>
          </div>
          <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4">
            <div class="text-xs font-semibold text-yellow-700">Pending</div>
            <div class="mt-1 text-2xl font-bold text-yellow-900">{{ kpis?.payments_pending || 0 }}</div>
          </div>
          <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <div class="text-xs font-semibold text-red-700">Failed</div>
            <div class="mt-1 text-2xl font-bold text-red-900">{{ kpis?.payments_failed || 0 }}</div>
          </div>
          <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <div class="text-xs font-semibold text-gray-700">Refunded</div>
            <div class="mt-1 text-2xl font-bold text-gray-900">{{ kpis?.payments_refunded || 0 }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
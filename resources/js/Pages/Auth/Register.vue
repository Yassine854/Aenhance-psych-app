<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { getCountries, getCitiesByCountryName } from '@/utils/geoData'
import Multiselect from '@vueform/multiselect'

const props = defineProps({
    specialisations: {
        type: Array,
        default: () => [],
    },
    expertises: {
        type: Array,
        default: () => [],
    },
})

const { t } = useI18n()

const specialisationOptions = computed(() =>
    (props.specialisations || []).map((s) => ({ value: s.id, label: s.name }))
)

const expertiseOptions = computed(() =>
    (props.expertises || []).map((s) => ({ value: s.id, label: s.name }))
)

const psychologistLanguageOptions = computed(() => [
    { value: 'english', label: t('auth.register.languageEnglish') },
    { value: 'french', label: t('auth.register.languageFrench') },
    { value: 'arabic', label: t('auth.register.languageArabic') },
])

const form = useForm({
    role: 'PATIENT',

    name: '',
    email: '',
    password: '',
    password_confirmation: '',

    // Patient profile
    patient_first_name: '',
    patient_last_name: '',
    patient_date_of_birth: '',
    patient_gender: '',
    patient_country: '',
    patient_city: '',
    patient_phone: '',
    patient_country_code: '',
    patient_profile_image: null,

    // Psychologist profile
    first_name: '',
    last_name: '',
    languages: [],
    specialisation_ids: [],
    expertise_ids: [],
    date_of_birth: '',
    gender: '',
    country: '',
    city: '',
    phone: '',
    country_code: '',
    address: '',
    bio: '',
    price_per_session: '',
    profile_image: null,
    diploma_files: [],
    cv_file: null,
});

const isPatient = computed(() => String(form.role || '').toUpperCase() === 'PATIENT')
const isPsychologist = computed(() => String(form.role || '').toUpperCase() === 'PSYCHOLOGIST')

const countriesList = ref(getCountries())

// Patient geo + phone
const patientCountryCode = ref('')
const patientDialCode = ref('')
const patientNationalNumber = ref('')

function syncPatientPhoneToForm() {
    form.patient_country_code = patientDialCode.value || ''
    form.patient_phone = patientNationalNumber.value || ''
}

watch(patientCountryCode, (code) => {
    const c = countriesList.value.find(x => x.isoCode === code)
    form.patient_country = c?.name || ''
    form.patient_city = ''
    patientDialCode.value = c?.dialCode || ''
    syncPatientPhoneToForm()
})

watch([patientDialCode, patientNationalNumber], () => syncPatientPhoneToForm())

const patientCities = computed(() => {
    if (!form.patient_country) return []
    return getCitiesByCountryName(form.patient_country).map(c => c.name)
})

// Psychologist geo + phone
const countryCode = ref('')
const dialCode = ref('')
const nationalNumber = ref('')

function syncPhoneToForm() {
    form.country_code = dialCode.value || ''
    form.phone = nationalNumber.value || ''
}

watch(countryCode, (code) => {
    const c = countriesList.value.find(x => x.isoCode === code)
    form.country = c?.name || ''
    form.city = ''
    dialCode.value = c?.dialCode || ''
    syncPhoneToForm()
})

watch([dialCode, nationalNumber], () => syncPhoneToForm())

const cities = computed(() => {
    if (!form.country) return []
    return getCitiesByCountryName(form.country).map(c => c.name)
})

const daysOfWeek = computed(() => [
    { value: 0, label: t('auth.register.days.sunday') },
    { value: 1, label: t('auth.register.days.monday') },
    { value: 2, label: t('auth.register.days.tuesday') },
    { value: 3, label: t('auth.register.days.wednesday') },
    { value: 4, label: t('auth.register.days.thursday') },
    { value: 5, label: t('auth.register.days.friday') },
    { value: 6, label: t('auth.register.days.saturday') },
])

const emptyWeeklyAvailability = () => ({
    0: [],
    1: [],
    2: [],
    3: [],
    4: [],
    5: [],
    6: [],
})

const availabilityByDay = ref(emptyWeeklyAvailability())
const availabilityErrorsByDay = ref({ 0: '', 1: '', 2: '', 3: '', 4: '', 5: '', 6: '' })
const availabilityRequiredError = ref('')

function clearAvailabilityErrors() {
    availabilityErrorsByDay.value = { 0: '', 1: '', 2: '', 3: '', 4: '', 5: '', 6: '' }
    availabilityRequiredError.value = ''
}

function timeToMinutes(t) {
    if (!t || typeof t !== 'string') return null
    const m = t.match(/^(\d{2}):(\d{2})$/)
    if (!m) return null
    const hh = Number(m[1])
    const mm = Number(m[2])
    if (Number.isNaN(hh) || Number.isNaN(mm)) return null
    return hh * 60 + mm
}

function sortSlots(day) {
    availabilityByDay.value[day].sort((a, b) => {
        const am = timeToMinutes(a.start_time) ?? 0
        const bm = timeToMinutes(b.start_time) ?? 0
        return am - bm
    })
}

function validateDaySlots(day) {
    availabilityErrorsByDay.value[day] = ''
    const slots = availabilityByDay.value[day]
    if (!slots.length) return true

    const normalized = slots.map((s) => ({
        start: timeToMinutes(s.start_time),
        end: timeToMinutes(s.end_time),
    }))

    for (const s of normalized) {
        if (s.start === null || s.end === null) {
            availabilityErrorsByDay.value[day] = 'Please set start and end time for all slots.'
            return false
        }
        if (s.end <= s.start) {
            availabilityErrorsByDay.value[day] = 'End time must be after start time.'
            return false
        }
    }

    const sorted = [...normalized].sort((a, b) => a.start - b.start)
    for (let i = 1; i < sorted.length; i++) {
        const prev = sorted[i - 1]
        const cur = sorted[i]
        if (cur.start < prev.end) {
            availabilityErrorsByDay.value[day] = 'Slots overlap. Please adjust times.'
            return false
        }
    }

    return true
}

function onSlotChanged(day) {
    availabilityRequiredError.value = ''
    sortSlots(day)
    validateDaySlots(day)
}

function addSlotForDay(day) {
    availabilityErrorsByDay.value[day] = ''
    availabilityRequiredError.value = ''
    availabilityByDay.value[day].push({ start_time: '09:00', end_time: '12:00' })
    onSlotChanged(day)
}

function removeSlotForDay(day, index) {
    availabilityByDay.value[day].splice(index, 1)
    onSlotChanged(day)
}

const flattenedAvailabilities = computed(() => {
    const out = []
    for (const d of daysOfWeek.value) {
        for (const slot of availabilityByDay.value[d.value] || []) {
            out.push({
                day_of_week: d.value,
                start_time: slot.start_time,
                end_time: slot.end_time,
            })
        }
    }
    return out
})

const step = ref(1)
const stepError = ref('')

const patientProfileInput = ref(null)
const diplomaInput = ref(null)
const cvInput = ref(null)
const profileInput = ref(null)

const patientImagePreview = computed(() => {
    if (form.patient_profile_image) return URL.createObjectURL(form.patient_profile_image)
    return ''
})

const imagePreview = computed(() => {
    if (form.profile_image) return URL.createObjectURL(form.profile_image)
    return ''
})

const diplomaLabel = computed(() => {
  if (Array.isArray(form.diploma_files) && form.diploma_files.length) {
    if (form.diploma_files.length === 1) return form.diploma_files[0].name
    return `${form.diploma_files.length} files selected`
  }
  return 'Drag & drop or click'
})
const cvLabel = computed(() => form.cv_file?.name || 'Drag & drop or click')

function goNext() {
    stepError.value = ''
    clearAvailabilityErrors()

    if (!form.role) {
        stepError.value = 'Please choose a role.'
        return
    }
    if (!form.name || !String(form.name).trim()) {
        stepError.value = 'Username is required.'
        return
    }
    if (!form.email || !String(form.email).trim()) {
        stepError.value = 'Email is required.'
        return
    }
    if (!form.password) {
        stepError.value = 'Password is required.'
        return
    }
    if (form.password !== form.password_confirmation) {
        stepError.value = 'Passwords do not match.'
        return
    }

    step.value = 2
}

function goPrevious() {
    stepError.value = ''
    step.value = 1
}

function goNextFromProfile() {
    stepError.value = ''
    clearAvailabilityErrors()

    // Patient submits directly from step 2.
    if (isPatient.value) return

    // Psychologists go to availability step.
    // Keep checks minimal (HTML required + backend validation are still the source of truth).
    if (!form.first_name || !String(form.first_name).trim()) {
        stepError.value = 'First name is required.'
        return
    }
    if (!form.last_name || !String(form.last_name).trim()) {
        stepError.value = 'Last name is required.'
        return
    }
    if (!Array.isArray(form.specialisation_ids) || form.specialisation_ids.length < 1) {
        stepError.value = 'Please select at least one specialisation.'
        return
    }
    if (!Array.isArray(form.languages) || form.languages.length < 1) {
        stepError.value = 'Please select at least one language.'
        return
    }
    if (!form.date_of_birth) {
        stepError.value = 'Date of birth is required.'
        return
    }
    if (!form.country) {
        stepError.value = 'Country is required.'
        return
    }
    if (!form.city) {
        stepError.value = 'City is required.'
        return
    }
    if (!nationalNumber.value || !String(nationalNumber.value).trim()) {
        stepError.value = 'Phone is required.'
        return
    }
    if (!form.price_per_session && String(form.price_per_session) !== '0') {
        stepError.value = 'Price per session is required.'
        return
    }
    if (!Array.isArray(form.diploma_files) || form.diploma_files.length === 0) {
        stepError.value = 'Diploma (PDF) is required.'
        return
    }
    if (!form.cv_file) {
        stepError.value = 'CV (PDF) is required.'
        return
    }

    step.value = 3
}

function goPreviousFromAvailability() {
    stepError.value = ''
    clearAvailabilityErrors()
    step.value = 2
}

function onFileChange(field, e) {
  const files = e?.target?.files || null
  if (!files) return
  if (field === 'diploma_files') {
    form[field] = Array.from(files)
    return
  }
  const file = files[0] || null
  form[field] = file
}

function onDrop(field, e) {
    const files = e?.dataTransfer?.files
    if (!files || !files.length) return
    if (field === 'patient_profile_image') {
        const file = files[0]
        if (!file.type.startsWith('image/')) return
        form[field] = file
        return
    }
    if (field === 'profile_image') {
        const file = files[0]
        if (!file.type.startsWith('image/')) return
        form[field] = file
        return
    }
    // diplomas/cv expect PDFs; allow multiple for diplomas
    if (field === 'diploma_files') {
        const arr = Array.from(files).filter(f => f.type === 'application/pdf')
        if (!arr.length) return
        form[field] = arr
        return
    }
    const file = files[0]
    if (field === 'cv_file' && file.type !== 'application/pdf') return
    form[field] = file
}

const submit = () => {
    stepError.value = ''

    if (isPsychologist.value) {
        clearAvailabilityErrors()

        if (!flattenedAvailabilities.value.length) {
            availabilityRequiredError.value = 'Please add at least one availability slot.'
            return
        }

        let availabilityOk = true
        for (const d of daysOfWeek.value) {
            if ((availabilityByDay.value[d.value] || []).length) {
                if (!validateDaySlots(d.value)) availabilityOk = false
            }
        }
        if (!availabilityOk) {
            return
        }
    }

    form.transform((data) => ({
        ...data,
        availabilities: isPsychologist.value ? JSON.stringify(flattenedAvailabilities.value) : null,
    }))

    form.post(route('register'), {
        forceFormData: true,
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head :title="t('register')" />

    <div class="min-h-screen bg-gradient-to-br from-[#af5166] via-[#af5166] to-[#5997ac] flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-3xl">
            <div class="flex items-center justify-center mb-6">
                <Link
                    :href="route('home')"
                    class="inline-flex items-center gap-3 rounded-2xl bg-white/95 px-4 py-3 shadow-xl ring-1 ring-white/40 backdrop-blur hover:bg-white transition"
                    aria-label="Go to home"
                    title="Home"
                >
                    <img src="/storage/aenhance.svg" alt="AEfhance" class="h-14 sm:h-16 w-auto object-contain" />
                </Link>
            </div>

            <div class="bg-white/95 backdrop-blur rounded-2xl shadow-2xl border border-white/30 overflow-hidden">
                <div class="px-6 py-6 sm:px-8">
                    <h1 class="text-2xl font-semibold text-gray-900">{{ t('auth.register.title') }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ t('auth.register.subtitle') }}</p>

                    <form @submit.prevent="submit" class="mt-6 space-y-6">
                        <!-- Step 1: Account -->
                        <div v-if="step === 1" class="space-y-6">
                            <!-- Role switch (Step 1 only) -->
                            <div class="rounded-xl border border-gray-200 bg-white p-5">
                                <div class="inline-flex rounded-xl bg-gray-100 p-1">
                                    <button
                                        type="button"
                                        @click="form.role = 'PATIENT'"
                                        class="px-4 py-2 text-sm font-semibold rounded-lg transition"
                                        :class="isPatient ? 'bg-white text-[#af5166] shadow' : 'text-gray-600 hover:text-gray-800'"
                                    >
                                        {{ t('auth.register.patientRole') }}
                                    </button>
                                    <button
                                        type="button"
                                        @click="form.role = 'PSYCHOLOGIST'"
                                        class="px-4 py-2 text-sm font-semibold rounded-lg transition"
                                        :class="isPsychologist ? 'bg-white text-[#5997ac] shadow' : 'text-gray-600 hover:text-gray-800'"
                                    >
                                        {{ t('auth.register.psychologistRole') }}
                                    </button>
                                </div>

                                <InputError class="mt-2" :message="form.errors.role" />
                                <div v-if="stepError" class="mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                                    {{ stepError }}
                                </div>
                            </div>

                            <!-- Account section -->
                            <div class="rounded-xl border border-gray-200 bg-white p-5">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ t('auth.register.accountTitle') }}</div>
                                        <div class="text-xs text-gray-500">{{ t('auth.register.accountSubtitle') }}</div>
                                    </div>
                                    <div class="h-9 w-9 rounded-lg bg-gradient-to-br from-[#af5166]/15 to-[#5997ac]/15 flex items-center justify-center text-xs font-semibold text-gray-700">
                                        I
                                    </div>
                                </div>

                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="name" :value="t('auth.register.username')" />
                                        <TextInput
                                            id="name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.name"
                                            required
                                            autofocus
                                            autocomplete="name"
                                        />
                                        <InputError class="mt-2" :message="form.errors.name" />
                                    </div>

                                    <div>
                                        <InputLabel for="email" :value="t('auth.register.email')" />
                                        <TextInput
                                            id="email"
                                            type="email"
                                            class="mt-1 block w-full"
                                            v-model="form.email"
                                            required
                                            autocomplete="username"
                                        />
                                        <InputError class="mt-2" :message="form.errors.email" />
                                    </div>

                                    <div>
                                        <InputLabel for="password" :value="t('auth.register.password')" />
                                        <TextInput
                                            id="password"
                                            type="password"
                                            class="mt-1 block w-full"
                                            v-model="form.password"
                                            required
                                            autocomplete="new-password"
                                        />
                                        <InputError class="mt-2" :message="form.errors.password" />
                                    </div>

                                    <div>
                                        <InputLabel for="password_confirmation" :value="t('auth.register.confirmPassword')" />
                                        <TextInput
                                            id="password_confirmation"
                                            type="password"
                                            class="mt-1 block w-full"
                                            v-model="form.password_confirmation"
                                            required
                                            autocomplete="new-password"
                                        />
                                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <Link :href="route('login')" class="text-sm text-[#af5166] font-medium hover:underline">{{ t('auth.register.alreadyRegistered') }}</Link>
                                <PrimaryButton type="button" @click="goNext">{{ t('auth.register.next') }}</PrimaryButton>
                            </div>
                        </div>

                        <!-- Step 2: Profile -->
                        <div v-else-if="step === 2" class="space-y-6">
                            <!-- Profile section (patient) -->
                            <div v-if="isPatient" class="rounded-xl border border-gray-200 bg-white p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ t('auth.register.patientProfileTitle') }}</div>
                                    <div class="text-xs text-gray-500">{{ t('auth.register.patientProfileSubtitle') }}</div>
                                </div>
                                <div class="h-9 w-9 rounded-lg bg-[#af5166]/10 flex items-center justify-center text-xs font-semibold text-gray-700">
                                    II
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="patient_first_name" :value="t('auth.register.firstName')" />
                                    <TextInput id="patient_first_name" type="text" class="mt-1 block w-full" v-model="form.patient_first_name" required />
                                    <InputError class="mt-2" :message="form.errors.patient_first_name" />
                                </div>

                                <div>
                                    <InputLabel for="patient_last_name" :value="t('auth.register.lastName')" />
                                    <TextInput id="patient_last_name" type="text" class="mt-1 block w-full" v-model="form.patient_last_name" required />
                                    <InputError class="mt-2" :message="form.errors.patient_last_name" />
                                </div>

                                <div>
                                    <InputLabel for="patient_date_of_birth" :value="t('auth.register.dateOfBirth')" />
                                    <TextInput id="patient_date_of_birth" type="date" class="mt-1 block w-full" v-model="form.patient_date_of_birth" required />
                                    <InputError class="mt-2" :message="form.errors.patient_date_of_birth" />
                                </div>

                                <div>
                                    <InputLabel for="patient_gender" :value="t('auth.register.genderOptional')" />
                                    <select
                                        id="patient_gender"
                                        v-model="form.patient_gender"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac]"
                                    >
                                        <option value="">{{ t('auth.register.selectGender') }}</option>
                                        <option value="MALE">Male</option>
                                        <option value="FEMALE">Female</option>
                                        <option value="OTHER">Other</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.patient_gender" />
                                </div>

                                <div>
                                    <InputLabel for="patient_country" :value="t('auth.register.countryOptional')" />
                                    <select
                                        id="patient_country"
                                        v-model="patientCountryCode"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac]"
                                    >
                                        <option value="">{{ t('auth.register.selectCountry') }}</option>
                                        <option v-for="c in countriesList" :key="c.isoCode" :value="c.isoCode">{{ c.name }}</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.patient_country" />
                                </div>

                                <div>
                                    <InputLabel for="patient_city" :value="t('auth.register.cityOptional')" />
                                    <select
                                        id="patient_city"
                                        v-model="form.patient_city"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac]"
                                        :disabled="!form.patient_country"
                                    >
                                        <option value="">{{ t('auth.register.selectCity') }}</option>
                                        <option v-for="ct in patientCities" :key="ct" :value="ct">{{ ct }}</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.patient_city" />
                                </div>

                                <div>
                                    <InputLabel for="patient_country_code" :value="t('auth.register.phoneOptional')" />
                                    <div class="mt-1 flex">
                                        <input
                                            v-model="patientDialCode"
                                            readonly
                                            class="w-24 rounded-l-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700"
                                            placeholder="+___"
                                        />
                                        <input
                                            v-model="patientNationalNumber"
                                            inputmode="tel"
                                            class="block w-full rounded-r-md border border-l-0 border-gray-300 px-3 py-2 text-sm focus:border-[#5997ac] focus:ring-[#5997ac]"
                                            placeholder="Enter phone number"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.patient_country_code" />
                                    <InputError class="mt-2" :message="form.errors.patient_phone" />
                                </div>

                                <div class="md:col-span-2">
                                    <InputLabel for="patient_profile_image" :value="t('auth.register.profileImageOptional')" />
                                    <div
                                        @click="patientProfileInput?.click()"
                                        @drop.prevent="onDrop('patient_profile_image', $event)"
                                        @dragover.prevent
                                        class="mt-1 border-2 border-dashed border-gray-300 rounded-lg p-3 flex items-center justify-center hover:border-[rgb(89,151,172)] hover:bg-gray-50 transition cursor-pointer"
                                    >
                                        <img v-if="patientImagePreview" :src="patientImagePreview" class="h-16 w-16 rounded-full object-cover" />
                                        <span v-else class="text-sm text-gray-600">{{ t('auth.register.dragDropOrClick') }}</span>
                                    </div>
                                    <input ref="patientProfileInput" id="patient_profile_image" type="file" accept="image/*" class="hidden" @change="(e) => onFileChange('patient_profile_image', e)" />
                                    <InputError class="mt-2" :message="form.errors.patient_profile_image" />
                                </div>
                            </div>
                            </div>

                            <!-- Profile section (psychologist) -->
                            <div v-if="isPsychologist" class="rounded-xl border border-gray-200 bg-white p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">{{ t('auth.register.psychologistProfileTitle') }}</div>
                                    <div class="text-xs text-gray-500">{{ t('auth.register.psychologistProfileSubtitle') }}</div>
                                </div>
                                <div class="h-9 w-9 rounded-lg bg-[#5997ac]/10 flex items-center justify-center text-xs font-semibold text-gray-700">
                                    III
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="first_name" :value="t('auth.register.firstName')" />
                                    <TextInput id="first_name" type="text" class="mt-1 block w-full" v-model="form.first_name" required />
                                    <InputError class="mt-2" :message="form.errors.first_name" />
                                </div>

                                <div>
                                    <InputLabel for="last_name" :value="t('auth.register.lastName')" />
                                    <TextInput id="last_name" type="text" class="mt-1 block w-full" v-model="form.last_name" required />
                                    <InputError class="mt-2" :message="form.errors.last_name" />
                                </div>

                                <div class="md:col-span-2">
                                    <InputLabel for="specialisation_ids" :value="t('auth.register.specialisations')" />
                                    <div class="mt-1">
                                        <Multiselect
                                            id="specialisation_ids"
                                            v-model="form.specialisation_ids"
                                            :options="specialisationOptions"
                                            mode="tags"
                                            :close-on-select="false"
                                            :searchable="true"
                                            :placeholder="t('auth.register.searchAndSelect')"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.specialisation_ids" />
                                </div>

                                    <div class="md:col-span-2">
                                        <InputLabel for="expertise_ids" :value="t('auth.register.expertisesOptional')" />
                                        <div class="mt-1">
                                            <Multiselect
                                                id="expertise_ids"
                                                v-model="form.expertise_ids"
                                                :options="expertiseOptions"
                                                mode="tags"
                                                :close-on-select="false"
                                                :searchable="true"
                                                :placeholder="t('auth.register.searchAndSelectOptional')"
                                            />
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.expertise_ids" />
                                    </div>

                                <div class="md:col-span-2">
                                    <InputLabel for="languages" :value="t('auth.register.languages')" />
                                    <div class="mt-1">
                                        <Multiselect
                                            id="languages"
                                            v-model="form.languages"
                                            :options="psychologistLanguageOptions"
                                            mode="tags"
                                            :close-on-select="false"
                                            :searchable="true"
                                            :placeholder="t('auth.register.selectOneOrMore')"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.languages" />
                                </div>

                                <div>
                                    <InputLabel for="date_of_birth" :value="t('auth.register.dateOfBirth')" />
                                    <TextInput id="date_of_birth" type="date" class="mt-1 block w-full" v-model="form.date_of_birth" required />
                                    <InputError class="mt-2" :message="form.errors.date_of_birth" />
                                </div>

                                <div>
                                    <InputLabel for="gender" :value="t('auth.register.genderOptional')" />
                                    <select
                                        id="gender"
                                        v-model="form.gender"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac]"
                                    >
                                        <option value="">{{ t('auth.register.selectGender') }}</option>
                                        <option value="MALE">Male</option>
                                        <option value="FEMALE">Female</option>
                                        <option value="OTHER">Other</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.gender" />
                                </div>

                                <div>
                                    <InputLabel for="country" :value="t('auth.register.country')" />
                                    <select
                                        id="country"
                                        v-model="countryCode"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac]"
                                        required
                                    >
                                        <option value="">{{ t('auth.register.selectCountry') }}</option>
                                        <option v-for="c in countriesList" :key="c.isoCode" :value="c.isoCode">{{ c.name }}</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.country" />
                                </div>

                                <div>
                                    <InputLabel for="city" :value="t('auth.register.city')" />
                                    <select
                                        id="city"
                                        v-model="form.city"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac]"
                                        :disabled="!form.country"
                                        required
                                    >
                                        <option value="">{{ t('auth.register.selectCity') }}</option>
                                        <option v-for="ct in cities" :key="ct" :value="ct">{{ ct }}</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.city" />
                                </div>

                                <div>
                                    <InputLabel for="country_code" :value="t('auth.register.phone')" />
                                    <div class="mt-1 flex">
                                        <input
                                            v-model="dialCode"
                                            readonly
                                            class="w-24 rounded-l-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700"
                                            placeholder="+___"
                                        />
                                        <input
                                            v-model="nationalNumber"
                                            inputmode="tel"
                                            class="block w-full rounded-r-md border border-l-0 border-gray-300 px-3 py-2 text-sm focus:border-[#5997ac] focus:ring-[#5997ac]"
                                            placeholder="Enter phone number"
                                            required
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.country_code" />
                                    <InputError class="mt-2" :message="form.errors.phone" />
                                </div>

                                <div>
                                    <InputLabel for="price_per_session" :value="t('auth.register.pricePerSession')" />
                                    <div class="mt-1 flex">
                                        <TextInput id="price_per_session" type="number" class="block w-full rounded-r-none" v-model="form.price_per_session" required min="0" step="0.01" />
                                        <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-sm text-gray-700">Dt</span>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.price_per_session" />
                                </div>

                                <div class="md:col-span-2">
                                    <InputLabel for="address" :value="t('auth.register.addressOptional')" />
                                    <TextInput id="address" type="text" class="mt-1 block w-full" v-model="form.address" />
                                    <InputError class="mt-2" :message="form.errors.address" />
                                </div>

                                <div class="md:col-span-2">
                                    <InputLabel for="bio" :value="t('auth.register.bioOptional')" />
                                    <textarea id="bio" v-model="form.bio" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac]"></textarea>
                                    <InputError class="mt-2" :message="form.errors.bio" />
                                </div>

                                <div class="md:col-span-2">
                                    <InputLabel for="profile_image" :value="t('auth.register.profileImageOptional')" />
                                    <div
                                        @click="profileInput?.click()"
                                        @drop.prevent="onDrop('profile_image', $event)"
                                        @dragover.prevent
                                        class="mt-1 border-2 border-dashed border-gray-300 rounded-lg p-3 flex items-center justify-center hover:border-[rgb(89,151,172)] hover:bg-gray-50 transition cursor-pointer"
                                    >
                                        <img v-if="imagePreview" :src="imagePreview" class="h-16 w-16 rounded-full object-cover" />
                                        <span v-else class="text-sm text-gray-600">{{ t('auth.register.dragDropOrClick') }}</span>
                                    </div>
                                    <input ref="profileInput" id="profile_image" type="file" accept="image/*" class="hidden" @change="(e) => onFileChange('profile_image', e)" />
                                    <InputError class="mt-2" :message="form.errors.profile_image" />
                                </div>

                                <div>
                                    <InputLabel for="diploma_files" :value="t('auth.register.diplomaPdf')" />
                                    <div
                                        @click="diplomaInput?.click()"
                                        @drop.prevent="onDrop('diploma_files', $event)"
                                        @dragover.prevent
                                        class="mt-1 border-2 border-dashed border-gray-300 rounded-lg p-3 text-center text-sm text-gray-600 hover:border-[rgb(89,151,172)] hover:bg-gray-50 transition cursor-pointer"
                                    >
                                        {{ diplomaLabel }}
                                    </div>
                                    <input ref="diplomaInput" id="diploma_files" type="file" accept="application/pdf" multiple class="hidden" @change="(e) => onFileChange('diploma_files', e)" required />
                                    <InputError class="mt-2" :message="form.errors.diploma_files" />
                                </div>

                                <!-- CIN removed -->

                                <div>
                                    <InputLabel for="cv_file" :value="t('auth.register.cvPdf')" />
                                    <div
                                        @click="cvInput?.click()"
                                        @drop.prevent="onDrop('cv_file', $event)"
                                        @dragover.prevent
                                        class="mt-1 border-2 border-dashed border-gray-300 rounded-lg p-3 text-center text-sm text-gray-600 hover:border-[rgb(89,151,172)] hover:bg-gray-50 transition cursor-pointer"
                                    >
                                        {{ cvLabel }}
                                    </div>
                                    <input ref="cvInput" id="cv_file" type="file" accept="application/pdf" class="hidden" @change="(e) => onFileChange('cv_file', e)" required />
                                    <InputError class="mt-2" :message="form.errors.cv_file" />
                                </div>
                            </div>

                            <div class="mt-4 rounded-lg border border-[#5997ac]/30 bg-[#5997ac]/10 px-4 py-3 text-sm text-gray-700">
                                {{ t('auth.register.reviewNote') }}
                            </div>
                            </div>

                            <div v-if="stepError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                                {{ stepError }}
                            </div>

                            <div class="flex items-center justify-between">
                                <button type="button" class="text-sm text-[#5997ac] hover:underline" @click="goPrevious">{{ t('auth.register.previous') }}</button>

                                <PrimaryButton
                                    v-if="isPatient"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    {{ t('auth.register.createAccount') }}
                                </PrimaryButton>

                                <PrimaryButton
                                    v-else
                                    type="button"
                                    @click="goNextFromProfile"
                                >
                                    {{ t('auth.register.next') }}
                                </PrimaryButton>
                            </div>
                        </div>

                        <!-- Step 3: Availability (psychologist only) -->
                        <div v-else class="space-y-6">
                            <div class="rounded-xl border border-gray-200 bg-white p-5">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ t('auth.register.availabilityTitle') }}</div>
                                        <div class="text-xs text-gray-500">{{ t('auth.register.availabilitySubtitle') }}</div>
                                    </div>
                                    <div class="h-9 w-9 rounded-lg bg-[#5997ac]/10 flex items-center justify-center text-xs font-semibold text-gray-700">
                                        3
                                    </div>
                                </div>

                                <InputError class="mt-2" :message="form.errors.availabilities" />
                                <p v-if="availabilityRequiredError" class="mt-2 text-sm text-red-600">{{ availabilityRequiredError }}</p>

                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div
                                        v-for="d in daysOfWeek"
                                        :key="d.value"
                                        class="rounded-xl border border-gray-200 bg-white p-4"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div class="text-sm font-semibold text-gray-900">{{ d.label }}</div>
                                            <button
                                                type="button"
                                                class="text-sm text-[#5997ac] hover:underline"
                                                @click="addSlotForDay(d.value)"
                                            >
                                                + Add slot
                                            </button>
                                        </div>

                                        <p v-if="availabilityErrorsByDay[d.value]" class="mt-2 text-sm text-red-600">
                                            {{ availabilityErrorsByDay[d.value] }}
                                        </p>

                                        <div class="mt-3 space-y-2">
                                            <div
                                                v-for="(slot, idx) in availabilityByDay[d.value]"
                                                :key="idx"
                                                class="flex items-center gap-2"
                                            >
                                                <input
                                                    type="time"
                                                    v-model="slot.start_time"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac]"
                                                    @change="onSlotChanged(d.value)"
                                                />
                                                <span class="text-xs text-gray-500">to</span>
                                                <input
                                                    type="time"
                                                    v-model="slot.end_time"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac]"
                                                    @change="onSlotChanged(d.value)"
                                                />

                                                <button
                                                    type="button"
                                                    class="text-sm text-gray-500 hover:text-gray-800"
                                                    @click="removeSlotForDay(d.value, idx)"
                                                >
                                                    Remove
                                                </button>
                                            </div>

                                            <div v-if="!availabilityByDay[d.value]?.length" class="text-xs text-gray-500">
                                                No slots yet.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <button type="button" class="text-sm text-[#5997ac] hover:underline" @click="goPreviousFromAvailability">{{ t('auth.register.previous') }}</button>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    {{ t('auth.register.createAccount') }}
                                </PrimaryButton>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="px-6 py-4 bg-gradient-to-r from-[#af5166]/10 to-[#5997ac]/10 border-t border-gray-100">
                    <div class="text-xs text-gray-600">{{ t('auth.register.footer') }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

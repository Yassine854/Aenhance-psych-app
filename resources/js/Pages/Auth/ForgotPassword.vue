<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const isStatusVisible = ref(Boolean(props.status));
let statusTimeoutId = null;

function queueStatusDismiss(status) {
    if (statusTimeoutId) {
        clearTimeout(statusTimeoutId);
        statusTimeoutId = null;
    }

    isStatusVisible.value = Boolean(status);

    if (!status) {
        return;
    }

    statusTimeoutId = window.setTimeout(() => {
        isStatusVisible.value = false;
        statusTimeoutId = null;
    }, 5000);
}

watch(() => props.status, (status) => {
    queueStatusDismiss(status);
}, { immediate: true });

onBeforeUnmount(() => {
    if (statusTimeoutId) {
        clearTimeout(statusTimeoutId);
    }
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password" />

    <div class="min-h-screen bg-gradient-to-br from-[#af5166] via-[#af5166] to-[#5997ac] flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
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
                <div class="px-6 py-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Forgot password?</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Enter your email address and we will send you a password reset link.
                    </p>

                    <div v-if="status && isStatusVisible" class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="mt-6 space-y-4">
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div class="pt-2">
                            <PrimaryButton
                                class="w-full justify-center"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Send reset link
                            </PrimaryButton>
                        </div>

                        <div class="pt-2 text-center text-sm text-gray-600">
                            <span>Remember your password?</span>
                            <Link :href="route('login')" class="text-[#af5166] font-medium hover:underline">Back to login</Link>
                        </div>
                    </form>
                </div>

                <div class="px-6 py-4 bg-gradient-to-r from-[#af5166]/10 to-[#5997ac]/10 border-t border-gray-100">
                    <div class="text-xs text-gray-600">We will email a secure reset link if the address exists in our system.</div>
                </div>
            </div>
        </div>
    </div>
</template>

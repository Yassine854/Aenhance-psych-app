<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const { t } = useI18n();

const statusMessage = computed(() => {
    if (!props.status) {
        return '';
    }

    const statusMap = {
        'passwords.sent': 'auth.status.passwords.sent',
        'passwords.reset': 'auth.status.passwords.reset',
        'verification-link-sent': 'auth.status.verificationLinkSent',
    };

    const key = statusMap[props.status];
    return key ? t(key) : props.status;
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: Boolean(data.remember),
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
        onSuccess: (page) => {
            const role = page?.props?.auth?.user?.role;
            if (typeof role === 'string' && role.trim().toUpperCase() === 'ADMIN') {
                setTimeout(() => {
                    window.location.reload();
                }, 120);
            }
        },
    });
};
</script>

<template>
    <Head :title="t('login')" />

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
                    <h1 class="text-2xl font-semibold text-gray-900">{{ t('auth.login.title') }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ t('auth.login.subtitle') }}</p>

                    <div v-if="statusMessage" class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                        {{ statusMessage }}
                    </div>

                    <form @submit.prevent="submit" class="mt-6 space-y-4">
                        <div>
                            <InputLabel for="email" :value="t('auth.register.email')" />
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

                        <div>
                            <InputLabel for="password" :value="t('auth.register.password')" />
                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <label class="flex items-start gap-2">
                                <Checkbox name="remember" v-model:checked="form.remember" class="mt-0.5" />
                                <span>
                                    <span class="block text-sm text-gray-600">{{ t('auth.login.rememberMe') }}</span>
                                </span>
                            </label>

                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm text-[#5997ac] hover:underline"
                            >
                                {{ t('auth.login.forgotPassword') }}
                            </Link>
                        </div>

                        <div class="pt-2">
                            <PrimaryButton
                                class="w-full justify-center"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                {{ t('auth.login.submit') }}
                            </PrimaryButton>
                        </div>

                        <div class="pt-2 text-center text-sm text-gray-600">
                            <span>{{ t('auth.login.newHere') }}</span>
                            <Link :href="route('register')" class="text-[#af5166] font-medium hover:underline">{{ t('auth.login.createAccount') }}</Link>
                        </div>
                    </form>
                </div>

                <div class="px-6 py-4 bg-gradient-to-r from-[#af5166]/10 to-[#5997ac]/10 border-t border-gray-100">
                    <div class="text-xs text-gray-600">{{ t('auth.login.footer') }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

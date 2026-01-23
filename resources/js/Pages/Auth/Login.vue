<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
        onSuccess: () => {
            window.location.reload()
        },
    });
};
</script>

<template>
    <Head title="Log in" />

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
                    <h1 class="text-2xl font-semibold text-gray-900">Welcome back</h1>
                    <p class="mt-1 text-sm text-gray-600">Log in to continue.</p>

                    <div v-if="status" class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
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

                        <div>
                            <InputLabel for="password" value="Password" />
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
                            <label class="flex items-center">
                                <Checkbox name="remember" v-model:checked="form.remember" />
                                <span class="ms-2 text-sm text-gray-600">Remember me</span>
                            </label>

                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm text-[#5997ac] hover:underline"
                            >
                                Forgot?
                            </Link>
                        </div>

                        <div class="pt-2">
                            <PrimaryButton
                                class="w-full justify-center"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Log in
                            </PrimaryButton>
                        </div>

                        <div class="pt-2 text-center text-sm text-gray-600">
                            <span>New here?</span>
                            <Link :href="route('register')" class="text-[#af5166] font-medium hover:underline">Create an account</Link>
                        </div>
                    </form>
                </div>

                <div class="px-6 py-4 bg-gradient-to-r from-[#af5166]/10 to-[#5997ac]/10 border-t border-gray-100">
                    <div class="text-xs text-gray-600">By continuing, you agree to our Terms & Privacy Policy.</div>
                </div>
            </div>
        </div>
    </div>
</template>

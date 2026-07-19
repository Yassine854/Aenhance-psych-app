<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const { t } = useI18n();

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');

const statusMessage = computed(() => {
    if (!verificationLinkSent.value) {
        return '';
    }

    return t('auth.verifyEmail.linkSent');
});
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.verifyEmail.title')" />

        <div class="mb-4 text-sm text-gray-600">
            {{ t('auth.verifyEmail.subtitle') }}
        </div>

        <div class="mb-4 font-medium text-sm text-green-600" v-if="verificationLinkSent">
            {{ statusMessage }}
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ t('auth.verifyEmail.resend') }}
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >{{ t('auth.verifyEmail.logout') }}</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>

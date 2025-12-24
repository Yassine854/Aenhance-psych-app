<script setup>
import { computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();
const isAdmin = computed(() => {
    const role = page.props?.value?.auth?.user?.role ?? page.props?.auth?.user?.role;
    return role === 'ADMIN';
});
</script>

<template>
    <Head title="Profile" />

    <AdminLayout>
        <div class="pt-2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Profile</h2>
                </div>
                <div class="space-y-6">
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="max-w-xl"
                        />
                    </div>

                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <UpdatePasswordForm class="max-w-xl" />
                    </div>

                    <div v-if="!isAdmin" class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <DeleteUserForm class="max-w-xl" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

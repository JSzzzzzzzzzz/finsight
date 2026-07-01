<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ChangePasswordModal from '@/Components/ChangePasswordModal.vue';
import EditProfileModal from '@/Components/EditProfileModal.vue';
import { ref, computed } from 'vue';
import { usePage, useForm } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);

const showPasswordModal = ref(false);
const showEditProfileModal = ref(false);
const showDeleteModal = ref(false);

const deletePasswordInput = ref(null);

const deleteForm = useForm({
    password: '',
});

const getInitials = (name) => {
    return name
        ? name
            .split(' ')
            .map(n => n[0])
            .join('')
            .substring(0, 2)
            .toUpperCase()
        : 'USER';
};

const openDeleteModal = () => {
    deleteForm.clearErrors();
    showDeleteModal.value = true;

    setTimeout(() => {
        deletePasswordInput.value?.focus();
    }, 250);
};

const closeDeleteModal = () => {
    if (deleteForm.processing) {
        return;
    }

    showDeleteModal.value = false;
    deleteForm.reset();
    deleteForm.clearErrors();
};

const deleteAccount = () => {
    deleteForm.delete(route('current-user.destroy'), {
        preserveScroll: true,

        onError: () => {
            setTimeout(() => {
                deletePasswordInput.value?.focus();
            }, 0);
        },

        onFinish: () => {
            deleteForm.reset();
        },
    });
};
</script>

<template>
    <AppLayout title="Profile">

        <div class="h-48 bg-gradient-to-br from-teal-700 via-cyan-600 to-teal-400"></div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 -mt-20 relative z-10 pb-12">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="md:col-span-1">
                    <div
                        class="bg-gray-800 shadow-xl rounded-lg overflow-hidden text-center pb-6 border border-gray-700">

                        <div class="flex justify-center pt-8 pb-4">
                            <div class="h-24 w-24 rounded-full bg-gray-800 p-1 shadow-lg border-2 border-teal-500">
                                <div v-if="user.profile_photo_url" class="h-full w-full rounded-full bg-cover bg-center"
                                    :style="'background-image: url(\'' + user.profile_photo_url + '\');'"></div>
                                <div v-else
                                    class="h-full w-full rounded-full bg-gray-700 flex items-center justify-center text-white font-bold text-xl">
                                    {{ getInitials(user.name) }}
                                </div>
                            </div>
                        </div>

                        <h2 class="text-xl font-bold text-white">{{ user.name }}</h2>
                        <p class="text-sm text-gray-400 mb-6">{{ user.email }}</p>

                        <div class="px-6 space-y-3">
                            <button @click="showEditProfileModal = true"
                                class="w-full py-2 px-4 border border-gray-600 rounded-md shadow-sm text-sm font-bold text-gray-300 bg-gray-700 hover:bg-gray-600 hover:text-white transition">
                                Edit Profile Info
                            </button>

                            <button @click="showPasswordModal = true"
                                class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-gradient-to-r from-teal-500 to-cyan-500 hover:from-teal-600 hover:to-cyan-600 transition">
                                Change Password
                            </button>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 space-y-6">

                    <div class="bg-gray-800 shadow rounded-lg p-6 border border-gray-700">
                        <h3 class="text-lg font-bold text-white mb-4">Profile Details</h3>

                        <div class="space-y-4">
                            <div class="flex justify-between border-b border-gray-700 pb-3">
                                <span class="text-sm text-gray-400">Name</span>
                                <span class="text-sm font-bold text-white">{{ user.name }}</span>
                            </div>

                            <div class="flex justify-between border-b border-gray-700 pb-3">
                                <span class="text-sm text-gray-400">Email</span>
                                <span class="text-sm font-bold text-white">{{ user.email }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-sm text-gray-400">Account Role</span>
                                <span
                                    class="text-xs bg-teal-500/20 text-teal-400 px-2 py-0.5 rounded-full font-bold border border-teal-500/30">
                                    {{ user.is_admin ? 'Administrator' : 'User' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800 shadow rounded-lg p-6 border border-red-900/50">
                        <h3 class="text-lg font-bold text-red-500 mb-2">Danger Zone</h3>
                        <p class="text-sm text-gray-400 mb-4">
                            Once you delete your account, there is no going back. Please be certain.
                        </p>
                        <button type="button"
                            class="bg-red-500/10 text-red-500 border border-red-500/50 hover:bg-red-500 hover:text-white px-4 py-2 rounded text-sm font-bold transition"
                            @click="openDeleteModal">
                            Delete My Account
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <ChangePasswordModal :show="showPasswordModal" @close="showPasswordModal = false" />
        <EditProfileModal :show="showEditProfileModal" :user="user" @close="showEditProfileModal = false" />
        <!-- Delete Account Confirmation Modal -->
        <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showDeleteModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm"
                @click="closeDeleteModal">
                <div class="bg-gray-800 rounded-lg shadow-2xl overflow-hidden w-full max-w-md m-4 border border-red-900"
                    @click.stop>
                    <form @submit.prevent="deleteAccount">
                        <!-- Header -->
                        <div
                            class="bg-gradient-to-r from-red-700 to-red-500 px-4 py-3 flex justify-between items-center">
                            <h3 class="text-base font-bold text-white">
                                Delete Account
                            </h3>

                            <button type="button" class="text-red-100 hover:text-white transition"
                                :disabled="deleteForm.processing" @click="closeDeleteModal">
                                ✕
                            </button>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <p class="text-sm text-gray-300">
                                Are you sure you want to permanently delete your
                                FinSight account? Your profile, exchange credentials,
                                portfolio records and related data will be removed.
                                This action cannot be undone.
                            </p>

                            <div class="mt-5">
                                <label for="delete-password" class="block text-sm font-medium text-gray-300">
                                    Enter your current password to confirm
                                </label>

                                <input id="delete-password" ref="deletePasswordInput" v-model="deleteForm.password"
                                    type="password" required autocomplete="current-password"
                                    placeholder="Current password"
                                    class="mt-2 block w-full bg-gray-900 border-gray-600 rounded-md shadow-sm text-white focus:ring-red-500 focus:border-red-500 sm:text-sm" />

                                <p v-if="deleteForm.errors.password" class="mt-2 text-xs text-red-400">
                                    {{ deleteForm.errors.password }}
                                </p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="bg-gray-900 px-4 py-3 sm:px-6 flex justify-end border-t border-gray-700">
                            <button type="button"
                                class="mr-3 px-4 py-2 text-sm font-bold text-gray-300 hover:text-white transition disabled:opacity-50"
                                :disabled="deleteForm.processing" @click="closeDeleteModal">
                                Cancel
                            </button>

                            <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white text-sm font-bold rounded shadow hover:bg-red-700 transition disabled:opacity-50"
                                :disabled="deleteForm.processing">
                                {{
                                    deleteForm.processing
                                        ? 'Deleting...'
                                        : 'Permanently Delete'
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>

    </AppLayout>
</template>
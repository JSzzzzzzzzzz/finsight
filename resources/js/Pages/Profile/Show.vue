<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ChangePasswordModal from '@/Components/ChangePasswordModal.vue';
import EditProfileModal from '@/Components/EditProfileModal.vue';
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;

const showPasswordModal = ref(false);
const showEditProfileModal = ref(false);

const getInitials = (name) => {
    return name ? name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : 'USER';
};

const triggerFeature = (featureName) => {
    alert(`${featureName} is disabled in this prototype.\n(Backend logic required)`);
};

const confirmDelete = () => {
    if (confirm("ARE YOU SURE?\n\nThis will permanently delete your account and all data. This action cannot be undone.")) {
        alert("Account deletion simulation complete.");
    }
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

                    <div class="bg-gray-800 shadow rounded-lg mt-6 p-4 border border-gray-700">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Account Stats</h4>
                        <div class="flex justify-between items-center border-b border-gray-700 pb-2 mb-2">
                            <span class="text-sm text-gray-400">Member Since</span>
                            <span class="text-sm font-bold text-white">Dec 2025</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-400">Account Type</span>
                            <span
                                class="text-xs bg-teal-500/20 text-teal-400 px-2 py-0.5 rounded-full font-bold border border-teal-500/30">Pro
                                User</span>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 space-y-6">

                    <div class="bg-gray-800 shadow rounded-lg p-6 border border-gray-700">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-white">Security Status</h3>
                            <span
                                class="text-sm text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-full border border-emerald-500/20 shadow-[0_0_10px_rgba(16,185,129,0.1)]">●
                                Good Standing</span>
                        </div>

                        <div class="space-y-4">
                            <div
                                class="flex items-center justify-between p-4 bg-gray-700/50 rounded border border-gray-600">
                                <div class="flex items-center">
                                    <div class="p-2 bg-gray-700 rounded text-teal-400 mr-3">🛡️</div>
                                    <div>
                                        <p class="text-sm font-bold text-white">Two-Factor Authentication</p>
                                        <p class="text-xs text-gray-400">Add an extra layer of security.</p>
                                    </div>
                                </div>
                                <button @click="triggerFeature('Two-Factor Auth')"
                                    class="text-sm text-teal-400 font-bold hover:text-teal-300 underline">Enable</button>
                            </div>

                            <div
                                class="flex items-center justify-between p-4 bg-gray-700/50 rounded border border-gray-600">
                                <div class="flex items-center">
                                    <div class="p-2 bg-gray-700 rounded text-cyan-400 mr-3">💻</div>
                                    <div>
                                        <p class="text-sm font-bold text-white">Browser Sessions</p>
                                        <p class="text-xs text-gray-400">Manage devices logged into your account.</p>
                                    </div>
                                </div>
                                <button @click="triggerFeature('Session Manager')"
                                    class="text-sm text-teal-400 font-bold hover:text-teal-300 underline">Manage</button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800 shadow rounded-lg p-6 border border-red-900/50">
                        <h3 class="text-lg font-bold text-red-500 mb-2">Danger Zone</h3>
                        <p class="text-sm text-gray-400 mb-4">
                            Once you delete your account, there is no going back. Please be certain.
                        </p>
                        <button @click="confirmDelete"
                            class="bg-red-500/10 text-red-500 border border-red-500/50 hover:bg-red-500 hover:text-white px-4 py-2 rounded text-sm font-bold transition">
                            Delete My Account
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <ChangePasswordModal :show="showPasswordModal" @close="showPasswordModal = false" />
        <EditProfileModal :show="showEditProfileModal" :user="user" @close="showEditProfileModal = false" />

    </AppLayout>
</template>
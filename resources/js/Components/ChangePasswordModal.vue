<script setup>
import { ref } from 'vue';

defineProps({
    show: Boolean,
});

const emit = defineEmits(['close']);

const currentPassword = ref('');
const newPassword = ref('');
const confirmPassword = ref('');

const save = () => {
    if (newPassword.value && newPassword.value === confirmPassword.value) {
        alert("Password updated successfully!");
        emit('close');
        currentPassword.value = '';
        newPassword.value = '';
        confirmPassword.value = '';
    } else {
        alert("Passwords do not match!");
    }
};
</script>

<template>
    <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm transition-opacity" @click="$emit('close')">
            
            <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-4 sm:scale-95" enter-to-class="opacity-100 translate-y-0 sm:scale-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0 sm:scale-100" leave-to-class="opacity-0 translate-y-4 sm:scale-95">
                <div class="bg-gray-800 rounded-lg shadow-2xl overflow-hidden transform transition-all w-full max-w-md m-4 border border-gray-700" @click.stop>
                    
                    <div class="bg-gradient-to-r from-teal-500 to-cyan-600 px-4 py-3 flex justify-between items-center">
                        <h3 class="text-base font-bold text-white">Change Password</h3>
                        <button @click="$emit('close')" class="text-gray-400 hover:text-white transition">✕</button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300">Current Password</label>
                            <input v-model="currentPassword" type="password" class="mt-1 block w-full bg-gray-900 border-gray-600 rounded-md shadow-sm text-white focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300">New Password</label>
                            <input v-model="newPassword" type="password" class="mt-1 block w-full bg-gray-900 border-gray-600 rounded-md shadow-sm text-white focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300">Confirm New Password</label>
                            <input v-model="confirmPassword" type="password" class="mt-1 block w-full bg-gray-900 border-gray-600 rounded-md shadow-sm text-white focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="bg-gray-900 px-4 py-3 sm:px-6 flex justify-end border-t border-gray-700">
                        <button @click="$emit('close')" class="mr-3 px-4 py-2 text-sm font-bold text-gray-300 hover:text-white transition">Cancel</button>
                        <button @click="save" class="px-4 py-2 bg-gradient-to-r from-teal-500 to-cyan-500 text-white text-sm font-bold rounded shadow hover:from-teal-600 hover:to-cyan-600 transition">Update Password</button>
                    </div>
                </div>
            </transition>
        </div>
    </transition>
</template>
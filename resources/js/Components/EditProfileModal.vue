<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    user: Object,
});

const emit = defineEmits(['close']);

const form = ref({
    name: '',
    email: '',
});

watch(() => props.show, () => {
    if (props.user) {
        form.value.name = props.user.name;
        form.value.email = props.user.email;
    }
});

const save = () => {
    alert(`Profile Updated for ${form.value.name}! (Prototype Mode)`);
    emit('close');
};
</script>

<template>
    <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm transition-opacity" @click="$emit('close')">
            
            <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-y-4 sm:scale-95" enter-to-class="opacity-100 translate-y-0 sm:scale-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100 translate-y-0 sm:scale-100" leave-to-class="opacity-0 translate-y-4 sm:scale-95">
                <div class="bg-gray-800 rounded-lg shadow-2xl overflow-hidden transform transition-all w-full max-w-md m-4 border border-gray-700" @click.stop>
                    
                    <div class="bg-gradient-to-r from-teal-500 to-cyan-600 px-4 py-3 flex justify-between items-center">
                        <h3 class="text-base font-bold text-white">Edit Profile Information</h3>
                        <button @click="$emit('close')" class="text-teal-100 hover:text-white transition">✕</button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300">Full Name</label>
                            <input v-model="form.name" type="text" class="mt-1 block w-full bg-gray-900 border-gray-600 rounded-md shadow-sm text-white focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300">Email Address</label>
                            <input v-model="form.email" type="email" class="mt-1 block w-full bg-gray-900 border-gray-600 rounded-md shadow-sm text-white focus:ring-teal-500 focus:border-teal-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="bg-gray-900 px-4 py-3 sm:px-6 flex justify-end border-t border-gray-700">
                        <button @click="$emit('close')" class="mr-3 px-4 py-2 text-sm font-bold text-gray-300 hover:text-white transition">Cancel</button>
                        <button @click="save" class="px-4 py-2 bg-gradient-to-r from-teal-500 to-cyan-500 text-white text-sm font-bold rounded shadow hover:from-teal-600 hover:to-cyan-600 transition">Save Changes</button>
                    </div>
                </div>
            </transition>
        </div>
    </transition>
</template>
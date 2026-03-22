<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    existingApiKey: String,
});

const emit = defineEmits(['close', 'save']);

const apiKey = ref('');
const secretKey = ref('');
const isEditing = ref(true);
const showSecret = ref(false);

watch(() => props.show, () => {
    if (props.existingApiKey) {
        isEditing.value = false;
        apiKey.value = props.existingApiKey; 
        secretKey.value = '••••••••••••••••••••••••';
    } else {
        resetForm();
    }
});

const resetForm = () => {
    isEditing.value = true;
    apiKey.value = '';
    secretKey.value = '';
    showSecret.value = false;
};

const save = () => {
    if (apiKey.value.length > 5) {
        const first4 = apiKey.value.substring(0, 4);
        const last4 = apiKey.value.substring(apiKey.value.length - 4);
        const masked = `${first4}••••••••${last4}`;
        emit('save', masked); 
        emit('close');
    } else {
        alert("Please enter a valid API Key");
    }
};
</script>

<template>
    <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm transition-opacity" @click="$emit('close')">
            
            <transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 translate-y-4 sm:scale-95"
                enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                leave-to-class="opacity-0 translate-y-4 sm:scale-95"
            >
                <div class="bg-gray-800 rounded-lg shadow-2xl overflow-hidden transform transition-all w-full max-w-lg m-4 border border-gray-700" @click.stop>
                    
                    <div class="bg-gradient-to-r from-teal-500 to-cyan-600 px-4 py-3 flex justify-between items-center">
                        <h3 class="text-base font-bold text-white">Exchange Connection</h3>
                        <button @click="$emit('close')" class="text-gray-200 hover:text-white transition">✕</button>
                    </div>

                    <div class="p-6">
                        
                        <div v-if="!isEditing" class="space-y-4">
                            <div class="flex items-center p-3 bg-emerald-900/20 rounded-lg border border-emerald-500/30">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-emerald-800/50 flex items-center justify-center text-emerald-400 mr-4 border border-emerald-500/50">
                                    ✓
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-emerald-400">Connection Active</p>
                                    <p class="text-xs text-emerald-200/70">Your keys are encrypted and secure.</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide">Active API Key</label>
                                <div class="mt-1 font-mono text-sm font-bold text-white bg-gray-900 p-2 rounded border border-gray-600 tracking-widest">
                                    {{ apiKey }}
                                </div>
                            </div>

                            <button @click="resetForm" class="text-xs text-teal-400 hover:text-teal-300 font-bold underline">
                                Update with new keys
                            </button>
                        </div>

                        <div v-else class="space-y-4">
                            <p class="text-sm text-gray-400 mb-4">
                                Paste your API keys below.
                            </p>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-300">API Key</label>
                                <input v-model="apiKey" type="text" class="mt-1 block w-full bg-gray-900 border-gray-600 rounded-md shadow-sm text-white placeholder-gray-500 focus:border-teal-500 focus:ring-teal-500 sm:text-sm" placeholder="Paste API Key here...">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-300">Secret Key</label>
                                <div class="relative mt-1">
                                    <input 
                                        v-model="secretKey" 
                                        :type="showSecret ? 'text' : 'password'" 
                                        class="block w-full bg-gray-900 border-gray-600 rounded-md shadow-sm text-white placeholder-gray-500 focus:border-teal-500 focus:ring-teal-500 sm:text-sm pr-10" 
                                        placeholder="Paste Secret Key here..."
                                    >
                                    
                                    <button 
                                        v-if="secretKey"
                                        type="button" 
                                        @click="showSecret = !showSecret"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-200 hover:text-white transition cursor-pointer"
                                    >
                                        <svg v-if="!showSecret" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-700">
                        <button v-if="isEditing" @click="save" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-lg px-4 py-2 bg-gradient-to-r from-teal-500 to-cyan-500 text-base font-bold text-white hover:from-teal-600 hover:to-cyan-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Save Securely
                        </button>
                        <button v-else @click="$emit('close')" class="w-full inline-flex justify-center rounded-md border border-gray-600 shadow-sm px-4 py-2 bg-gray-800 text-base font-medium text-gray-300 hover:bg-gray-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Done
                        </button>
                        <button @click="$emit('close')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-600 shadow-sm px-4 py-2 bg-gray-800 text-base font-medium text-gray-300 hover:text-white hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </transition>
        </div>
    </transition>
</template>

<style scoped>
/* This CSS hides the native browser "reveal password" eye icon (mostly for Edge/IE/Windows).
   This prevents the "Double Eye" issue.
*/
input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear {
    display: none;
}
</style>
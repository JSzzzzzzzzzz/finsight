<script setup>
import { ref } from 'vue';

const props = defineProps({
    show: Boolean,
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity">
        
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            
            <div class="bg-red-600 px-4 py-3 flex items-center">
                <svg class="h-6 w-6 text-white mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h3 class="text-lg leading-6 font-medium text-white">
                    Risk Warning: High Volatility
                </h3>
            </div>

            <div class="px-6 py-4">
                <p class="text-sm text-gray-600">
                    FinSight AI has detected abnormal market movement. 
                    <br><br>
                    <strong>Predicted Impact:</strong> <span class="text-red-600 font-bold">-12.5% Loss</span> if you hold for next 4 hours.
                </p>
                
                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded text-xs text-red-700">
                    Suggestion: Consider placing a Stop-Loss at <strong>$27,500</strong>.
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" @click="close" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    View Details
                </button>
                <button type="button" @click="close" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Dismiss
                </button>
            </div>
        </div>
    </div>
</template>
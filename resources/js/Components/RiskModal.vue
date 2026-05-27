<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    risk: {
        type: Object,
        default: () => ({
            title: 'Risk Simulation',
            message: 'No simulation data available.',
            impact: 'Unable to calculate portfolio impact.',
            suggestion: 'Please run a risk simulation first.',
            currentValue: 0,
            dropPercent: 0,
            estimatedLoss: 0,
            projectedValue: 0,
            scenario: 'N/A',
        }),
    },
});

const emit = defineEmits(['close', 'simulate']);

const showDetails = ref(false);

const dropPercent = ref(15);

const riskLevel = computed(() => {
    if (dropPercent.value <= 10) return 'Mild';
    if (dropPercent.value <= 30) return 'Moderate';
    if (dropPercent.value <= 60) return 'Severe';
    return 'Extreme';
});

watch(dropPercent, (value) => {
    emit('simulate', riskLevel.value, Number(value));
});

const close = () => {
    showDetails.value = false;
    emit('close');
};

</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm">
        <div class="bg-gray-800 rounded-lg overflow-hidden shadow-2xl sm:max-w-lg sm:w-full border border-gray-700">
            <div class="bg-gradient-to-r from-teal-500 to-cyan-500 px-5 py-4 flex items-center">
                <svg class="h-7 w-7 text-yellow-300 mr-3" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M10.29 3.86c.77-1.33 2.65-1.33 3.42 0l8.03 13.9c.77 1.33-.19 2.99-1.71 2.99H3.97c-1.52 0-2.48-1.66-1.71-2.99l8.03-13.9ZM12 8c.55 0 1 .45 1 1v4c0 .55-.45 1-1 1s-1-.45-1-1V9c0-.55.45-1 1-1Zm0 8.25a1.25 1.25 0 1 0 0 2.5 1.25 1.25 0 0 0 0-2.5Z" />
                </svg>

                <h3 class="text-lg font-bold text-white">
                    {{ risk.title }}
                </h3>
            </div>

            <div class="px-6 py-5">
                <p class="text-sm text-gray-100 mb-4 leading-relaxed font-bold">
                    {{ risk.message }}
                </p>

                <div class="mt-5 mb-5 bg-gray-900/70 border border-gray-700 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-3">
                        <div>
                            <div class="text-sm font-extrabold text-white">
                                Market Drop Severity
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                Adjust the hypothetical market crash percentage.
                            </div>
                        </div>

                        <span class="text-lg font-black text-cyan-300">
                            {{ dropPercent }}%
                        </span>
                    </div>

                    <input v-model="dropPercent" type="range" min="1" max="99" step="1"
                        class="w-full h-2 rounded-lg cursor-pointer accent-teal-400" />

                    <div class="mt-4 flex items-center justify-between">
                        <span class="px-3 py-1 rounded-full text-xs font-bold" :class="{
                            'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30': riskLevel === 'Mild',
                            'bg-orange-500/20 text-orange-300 border border-orange-500/30': riskLevel === 'Moderate',
                            'bg-red-500/20 text-red-300 border border-red-500/30': riskLevel === 'Severe',
                            'bg-red-700/30 text-red-200 border border-red-700/40': riskLevel === 'Extreme',
                        }">
                            {{ riskLevel }} Scenario
                        </span>

                        <span class="text-xs text-gray-400">
                            1% - 99% stress test
                        </span>
                    </div>
                </div>

                <p class="text-sm text-gray-100 leading-relaxed">
                    <strong class="text-white font-extrabold">Predicted Impact:</strong>
                    <span class="text-red-400 font-bold">
                        {{ risk.impact }}
                    </span>
                </p>



                <div class="mt-4 p-3 bg-red-500/10 border border-red-500/30 rounded text-xs text-red-200">
                    {{ risk.suggestion }}
                </div>

                <div v-if="showDetails"
                    class="mt-4 bg-gray-900 border border-gray-700 rounded p-4 text-sm text-gray-300 space-y-3">
                    <div class="flex justify-between">
                        <span>Current Portfolio Value</span>
                        <strong class="text-white">RM {{ Number(risk.currentValue).toFixed(2) }}</strong>
                    </div>

                    <div class="flex justify-between">
                        <span>Selected Scenario</span>
                        <strong class="text-white">{{ risk.scenario }} Drop</strong>
                    </div>

                    <div class="flex justify-between">
                        <span>Drop Percentage</span>
                        <strong class="text-white">{{ risk.dropPercent }}%</strong>
                    </div>

                    <div class="flex justify-between text-red-400">
                        <span>Estimated Loss</span>
                        <strong>RM {{ Number(risk.estimatedLoss).toFixed(2) }}</strong>
                    </div>

                    <div class="flex justify-between">
                        <span>Projected Portfolio Value</span>
                        <strong class="text-white">RM {{ Number(risk.projectedValue).toFixed(2) }}</strong>
                    </div>
                </div>
            </div>

            <div class="bg-gray-900 px-6 py-4 border-t border-gray-700 flex justify-end gap-3">
                <button type="button" @click="close"
                    class="px-4 py-2 rounded border border-gray-600 bg-gray-800 text-gray-200 hover:bg-gray-700 text-sm font-bold">
                    Dismiss
                </button>

                <button type="button" @click="showDetails = !showDetails"
                    class="px-4 py-2 rounded bg-red-600 text-white font-bold hover:bg-red-700 text-sm">
                    {{ showDetails ? 'Hide Details' : 'View Details' }}
                </button>
            </div>
        </div>
    </div>
</template>
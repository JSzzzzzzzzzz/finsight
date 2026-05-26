<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: Boolean,
    coin: Object,
    analysis: Object,
    loading: Boolean,
});

const sentiment = computed(() => props.analysis?.sentiment || {})

const average = computed(() => sentiment.value.average || {})

const riskScore = computed(() => sentiment.value.risk_score || 0)

const riskLevel = computed(() => sentiment.value.risk_level || 'Unknown')

const marketSignal = computed(() => {
    if (average.value.positive > average.value.negative) {
        return 'Bullish'
    }

    if (average.value.negative > average.value.positive) {
        return 'Bearish'
    }

    return 'Neutral'
})

const prediction = computed(() => {
    if (marketSignal.value === 'Bullish') {
        return '↗ Uptrend'
    }

    if (marketSignal.value === 'Bearish') {
        return '↘ Downtrend'
    }

    return '→ Sideways'
})

const emit = defineEmits(['close']);



</script>

<template>
    <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80 backdrop-blur-sm transition-opacity"
            @click="$emit('close')">

            <transition enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 translate-y-4 sm:scale-95"
                enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                leave-to-class="opacity-0 translate-y-4 sm:scale-95">
                <div class="bg-gray-800 rounded-lg shadow-2xl overflow-hidden w-full max-w-lg m-4 border border-gray-700"
                    @click.stop>

                    <div class="bg-gray-900 px-6 py-4 border-b border-gray-700 flex justify-between items-center">
                        <div class="flex items-center">
                            <div
                                class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold mr-3">
                                AI</div>
                            <h3 class="text-lg font-bold text-white">FinSight Deep Dive</h3>
                        </div>
                        <button @click="$emit('close')" class="text-gray-400 hover:text-white">✕</button>
                    </div>

                    <div class="p-6">

                        <div v-if="loading" class="text-center py-8">
                            <div
                                class="inline-block animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-teal-400 mb-4">
                            </div>
                            <p class="text-teal-400 font-mono animate-pulse">
                                Fetching latest {{ coin?.symbol }} market news...
                            </p>
                            <p class="text-xs text-gray-500 mt-2">
                                Preparing news input for FinBERT sentiment analysis
                            </p>
                        </div>

                        <div v-else>
                            <div class="flex items-center mb-4">
                                <h2 class="text-2xl font-bold text-white mr-2">
                                    {{ coin?.display_pair ?? coin?.symbol }}
                                </h2>
                                <span
                                    class="px-2 py-1 bg-teal-500/20 text-teal-400 text-xs font-bold rounded border border-teal-500/30">
                                    {{ marketSignal }} Structure
                                </span>
                            </div>

                            <div class="bg-gray-900 p-4 rounded-lg border border-gray-700 mb-4">
                                <h4 class="text-xs font-bold text-gray-400 uppercase mb-2">
                                    Key Driver
                                </h4>

                                <p class="text-sm text-gray-200 leading-relaxed">
                                    {{ analysis?.explanation ?? 'AI explanation is currently unavailable.' }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-900 p-3 rounded border border-gray-700">
                                    <div class="text-xs text-gray-500">Short-Term Prediction</div>

                                    <div class="font-bold text-lg" :class="{
                                        'text-green-400': marketSignal === 'Bullish',
                                        'text-red-400': marketSignal === 'Bearish',
                                        'text-yellow-400': marketSignal === 'Neutral',
                                    }">
                                        {{ prediction }}
                                    </div>
                                </div>

                                <div class="bg-gray-900 p-3 rounded border border-gray-700">
                                    <div class="text-xs text-gray-500">Risk Score</div>

                                    <div class="font-bold text-lg" :class="{
                                        'text-green-400': riskLevel === 'Low',
                                        'text-yellow-400': riskLevel === 'Medium',
                                        'text-red-400': riskLevel === 'High',
                                    }">
                                        {{ riskLevel }} ({{ riskScore }}/100)
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="bg-gray-900 px-6 py-4 border-t border-gray-700 flex justify-end">
                        <button @click="$emit('close')"
                            class="px-4 py-2 bg-gray-800 text-white text-sm font-bold rounded hover:bg-gray-700 border border-gray-600">Close
                            Analysis</button>
                    </div>
                </div>
            </transition>
        </div>
    </transition>
</template>
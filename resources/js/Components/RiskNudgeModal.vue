<script setup>
const props = defineProps({
    show: Boolean,
    nudge: {
        type: Object,
        default: () => ({
            title: 'AI Risk Nudge',
            message: 'FinSight detected elevated market risk.',
            riskScore: 0,
            riskLevel: 'Unknown',
            suggestion: 'Review your portfolio exposure and monitor market updates.',
        }),
    },
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm"
    >
        <div class="bg-gray-800 rounded-lg overflow-hidden shadow-2xl sm:max-w-lg sm:w-full border border-gray-700">
            <div class="bg-gradient-to-r from-red-600 to-orange-500 px-5 py-4 flex items-center">
                <svg class="h-7 w-7 text-yellow-200 mr-3" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M10.29 3.86c.77-1.33 2.65-1.33 3.42 0l8.03 13.9c.77 1.33-.19 2.99-1.71 2.99H3.97c-1.52 0-2.48-1.66-1.71-2.99l8.03-13.9ZM12 8c.55 0 1 .45 1 1v4c0 .55-.45 1-1 1s-1-.45-1-1V9c0-.55.45-1 1-1Zm0 8.25a1.25 1.25 0 1 0 0 2.5 1.25 1.25 0 0 0 0-2.5Z"
                    />
                </svg>

                <h3 class="text-lg font-bold text-white">
                    {{ nudge.title }}
                </h3>
            </div>

            <div class="px-6 py-5 space-y-4">
                <p class="text-sm text-gray-100 font-bold leading-relaxed">
                    {{ nudge.message }}
                </p>

                <div class="bg-gray-900/70 border border-gray-700 rounded-lg p-4">
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-gray-300">AI Risk Score</span>
                        <span class="font-black text-red-400">
                            {{ nudge.riskScore }}/100
                        </span>
                    </div>

                    <div class="w-full bg-gray-700 rounded-full h-2">
                        <div
                            class="h-2 rounded-full bg-red-500"
                            :style="{ width: nudge.riskScore + '%' }"
                        ></div>
                    </div>

                    <div class="mt-3 text-xs text-gray-400">
                        Risk Level:
                        <span class="font-bold text-red-300">
                            {{ nudge.riskLevel }}
                        </span>
                    </div>
                </div>

                <div class="p-3 bg-red-500/10 border border-red-500/30 rounded text-sm text-red-200">
                    {{ nudge.suggestion }}
                </div>
            </div>

            <div class="bg-gray-900 px-6 py-4 border-t border-gray-700 flex justify-end">
                <button
                    type="button"
                    @click="close"
                    class="px-4 py-2 rounded border border-gray-600 bg-gray-800 text-gray-200 hover:bg-gray-700 text-sm font-bold"
                >
                    Dismiss
                </button>
            </div>
        </div>
    </div>
</template>
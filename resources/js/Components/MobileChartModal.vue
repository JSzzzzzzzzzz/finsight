<script setup>
import { watch, nextTick } from 'vue';

const props = defineProps({
    show: Boolean,
    symbol: String,
});

const emit = defineEmits(['close']);

// Initialize TradingView when modal opens
watch(() => props.show, (newVal) => {
    if (newVal && props.symbol) {
        nextTick(() => {
            if (document.getElementById('mobile_tv_chart')) {
                new TradingView.widget({
                    "width": "100%",
                    "height": "100%",
                    "symbol": "BINANCE:" + props.symbol + "USDT",
                    "interval": "D",
                    "timezone": "Etc/UTC",
                    "theme": "dark",
                    "style": "1",
                    "locale": "en",
                    "toolbar_bg": "#1f2937",
                    "enable_publishing": false,
                    "hide_top_toolbar": false,
                    "save_image": false,
                    "container_id": "mobile_tv_chart"
                });
            }
        });
    }
});
</script>

<template>
    <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-90 backdrop-blur-sm" @click="$emit('close')">
            
            <div class="relative w-full h-full max-w-2xl md:h-3/4 md:rounded-lg bg-gray-900 flex flex-col" @click.stop>
                
                <div class="flex justify-between items-center p-4 border-b border-gray-800 bg-gray-900">
                    <h3 class="text-lg font-bold text-white">{{ symbol }} Chart</h3>
                    <button @click="$emit('close')" class="text-gray-400 hover:text-white">✕</button>
                </div>

                <div class="flex-1 bg-black relative">
                     <div id="mobile_tv_chart" class="absolute inset-0 w-full h-full"></div>
                </div>

                <div class="p-4 bg-gray-900 border-t border-gray-800">
                    <button @click="$emit('close')" class="w-full py-3 bg-red-600 hover:bg-red-500 text-white font-bold rounded-lg shadow-lg transition uppercase tracking-widest text-sm">
                        Close Chart
                    </button>
                </div>

            </div>
        </div>
    </transition>
</template>
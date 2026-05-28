<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CoinAnalysisModal from '@/Components/CoinAnalysisModal.vue';
import MobileChartModal from '@/Components/MobileChartModal.vue';
import { onMounted, onUnmounted, ref } from 'vue';
const showAnalysis = ref(false);
const showMobileChart = ref(false);
const selectedCoin = ref(null);


let scannerInterval = null;

const lastUpdated = ref(null);

const fetchScannerData = async () => {
    try {
        console.log('Refreshing market scanner...');

        const response = await fetch(route('market.scanner') + '?t=' + Date.now(), {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            console.error('Scanner API error:', response.status);
            return;
        }

        const data = await response.json();

        console.log('Updated market data:', data);

        marketData.value = [...data];
        lastUpdated.value = new Date().toLocaleTimeString();

    } catch (error) {
        console.error('Failed to refresh market scanner:', error);
    }
};

const props = defineProps({
    marketData: Array
});

const marketData = ref(props.marketData ?? []);

const aiSummary = ref({
    sentiment: 'Loading',
    score: 0,
    text: 'Generating latest market abstract...'
});

const generatedAt = ref('');

const fetchMarketSummary = async () => {
    try {
        const response = await fetch(route('market.summary'), {
            headers: {
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            console.error('Market summary API error:', response.status);
            return;
        }

        const data = await response.json();
        generatedAt.value = data.generated_at;

        const average = data.sentiment?.average ?? {};
        const positive = average.positive ?? 0;
        const negative = average.negative ?? 0;
        const neutral = average.neutral ?? 0;

        const moodIndex = Math.round(((positive - negative + 1) / 2) * 100);

        let sentiment = 'Neutral';

        if (moodIndex >= 61) {
            sentiment = 'Bullish';
        } else if (moodIndex <= 39) {
            sentiment = 'Bearish';
        }

        aiSummary.value = {
            sentiment,
            score: moodIndex,
            text: data.summary ?? 'Unable to generate market summary.'
        };



        console.log('Market summary:', data);

    } catch (error) {
        console.error('Failed to fetch market summary:', error);
    }
};

const analysisLoading = ref(false);
const analysisResult = ref(null);

const analyzeCoin = async (coin) => {
    selectedCoin.value = coin;
    showAnalysis.value = true;
    analysisLoading.value = true;
    analysisResult.value = null;

    try {
        const response = await fetch(route('market.analyze'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: JSON.stringify({
                symbol: coin.symbol,
                pair: coin.pair,
                display_pair: coin.display_pair,
            }),
        });

        const data = await response.json();

        console.log('AI analysis result:', data);

        analysisResult.value = data;
    } catch (error) {
        console.error('AI analysis failed:', error);
    } finally {
        analysisLoading.value = false;
    }
};
const openChart = (coin) => {
    selectedCoin.value = {
        ...coin,
        tvSymbol: coin.symbol
    };

    showMobileChart.value = true;
};

onMounted(() => {
    // Load TradingView chart
    const script = document.createElement('script');
    script.src = 'https://s3.tradingview.com/tv.js';
    script.async = true;
    script.onload = () => {
        new TradingView.widget({
            width: "100%",
            height: "100%",
            symbol: "BINANCE:BTCUSDT",
            interval: "D",
            timezone: "Etc/UTC",
            theme: "dark",
            style: "1",
            locale: "en",
            toolbar_bg: "#1f2937",
            enable_publishing: false,
            allow_symbol_change: true,
            container_id: "tradingview_chart"
        });
    };
    document.head.appendChild(script);

    // Refresh market scanner every 3 seconds
    fetchScannerData();
    fetchMarketSummary();

    scannerInterval = setInterval(() => {
        fetchScannerData();
    }, 3000);
});

onUnmounted(() => {
    if (scannerInterval) {
        clearInterval(scannerInterval);
    }
});
</script>

<template>
    <AppLayout title="Market Intelligence">

        <div class="py-4 lg:py-6">
            <div class="max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 space-y-4 lg:space-y-6">

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 lg:gap-6">

                    <div class="bg-gray-800 p-4 lg:p-6 shadow-xl rounded-lg border-t-4 col-span-1 flex flex-row lg:flex-col items-center lg:items-stretch justify-between lg:justify-center"
                        :class="{
                            'border-green-400': aiSummary.sentiment === 'Bullish',
                            'border-red-500': aiSummary.sentiment === 'Bearish',
                            'border-yellow-400': aiSummary.sentiment === 'Neutral',
                            'border-gray-500': aiSummary.sentiment === 'Loading',
                        }">

                        <div>
                            <h3
                                class="text-gray-400 font-bold uppercase text-[10px] lg:text-xs tracking-wider mb-1 lg:mb-2">
                                Market Sentiment</h3>
                            <span class="text-2xl lg:text-4xl font-black block" :class="{
                                'text-green-400': aiSummary.sentiment === 'Bullish',
                                'text-red-500': aiSummary.sentiment === 'Bearish',
                                'text-yellow-400': aiSummary.sentiment === 'Neutral',
                                'text-gray-400': aiSummary.sentiment === 'Loading',
                            }">
                                {{ aiSummary.sentiment }}
                            </span>
                        </div>

                        <div class="text-right lg:text-left lg:mt-4 w-1/2 lg:w-full">
                            <div class="flex items-baseline justify-end lg:justify-between mb-1">
                                <span class="hidden lg:inline text-xs text-gray-500">Market Mood Index</span>
                                <div>
                                    <span class="text-xl lg:text-2xl font-bold text-white">{{ aiSummary.score }}</span>
                                    <span class="text-xs text-gray-500">/100</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-1.5 lg:h-2">
                                <div class="h-1.5 lg:h-2 rounded-full transition-all duration-500" :class="{
                                    'bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.5)]': aiSummary.sentiment === 'Bullish',
                                    'bg-red-600 shadow-[0_0_10px_rgba(220,38,38,0.5)]': aiSummary.sentiment === 'Bearish',
                                    'bg-yellow-400 shadow-[0_0_10px_rgba(250,204,21,0.5)]': aiSummary.sentiment === 'Neutral',
                                    'bg-gray-500': aiSummary.sentiment === 'Loading',
                                }" :style="{ width: aiSummary.score + '%' }"></div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-black p-4 lg:p-6 shadow-xl rounded-lg text-white border border-gray-700 col-span-1 lg:col-span-3">
                        <div class="flex justify-between items-center mb-2 lg:mb-3">
                            <h3 class="font-bold text-teal-400 flex items-center text-sm lg:text-lg">
                                <span class="mr-2">✨</span> FinSight Market Abstract
                            </h3>
                            <span
                                class="text-[10px] lg:text-xs bg-gray-900 text-gray-400 px-2 lg:px-3 py-1 rounded border border-gray-700">Generated
                                at {{ generatedAt || '--:--' }}</span>
                        </div>
                        <p class="text-gray-300 leading-relaxed text-xs lg:text-base line-clamp-3 lg:line-clamp-none">
                            {{ aiSummary.text }}
                        </p>
                        <div class="mt-4 hidden lg:flex space-x-2">
                            <span
                                class="text-xs border border-gray-600 bg-gray-700/50 px-3 py-1 rounded-full text-gray-300">#MacroEconomics</span>
                            <span
                                class="text-xs border border-gray-600 bg-gray-700/50 px-3 py-1 rounded-full text-gray-300">#VolatilityAlert</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-auto lg:h-[600px]">

                    <div
                        class="hidden lg:block lg:col-span-9 bg-gray-800 shadow-xl rounded-lg overflow-hidden border border-gray-700 h-full">
                        <div id="tradingview_chart" class="h-full bg-gray-900"></div>
                    </div>

                    <div
                        class="col-span-1 lg:col-span-3 bg-gray-800 shadow-xl rounded-lg border border-gray-700 flex flex-col h-[600px] lg:h-full">

                        <div
                            class="p-4 bg-gray-900 border-b border-gray-700 flex justify-between items-center shrink-0">
                            <h3 class="font-bold text-white text-sm">Market Scanner</h3>
                            <span class="text-xs text-teal-400">
                                Live Prices • {{ lastUpdated ?? 'Loading...' }}
                            </span>
                        </div>

                        <div class="overflow-y-auto flex-1 custom-scrollbar">
                            <table class="min-w-full divide-y divide-gray-700">
                                <thead class="bg-gray-800 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Asset
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-400 uppercase">Price
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-400 uppercase">
                                            Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-gray-800 divide-y divide-gray-700">
                                    <tr v-for="coin in marketData" :key="coin.symbol"
                                        class="hover:bg-gray-700/50 transition duration-150">

                                        <td class="px-4 py-3 whitespace-nowrap cursor-pointer lg:cursor-default"
                                            @click="openChart(coin)">
                                            <div class="flex items-center">
                                                <div class="font-bold text-white text-sm mr-2">{{ coin.symbol }}</div>
                                                <div class="text-xs hidden sm:block text-teal-400">
                                                    {{ coin.display_pair }}
                                                </div>
                                            </div>
                                            <div class="text-xs sm:hidden mt-1 text-teal-400">
                                                {{ coin.display_pair }}
                                            </div>
                                        </td>

                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium text-gray-200">
                                            RM {{ Number(coin.price).toFixed(2) }}
                                        </td>

                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <div class="flex justify-end space-x-2">
                                                <button @click="openChart(coin)"
                                                    class="lg:hidden p-2 rounded-lg bg-gray-700 text-gray-300 hover:bg-gray-600 border border-gray-600">
                                                    📊
                                                </button>

                                                <button @click="analyzeCoin(coin)"
                                                    class="group p-2 rounded-lg bg-teal-500/10 text-teal-400 hover:bg-teal-500 hover:text-white transition border border-teal-500/30 flex items-center justify-center">

                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                    </svg>

                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <CoinAnalysisModal :show="showAnalysis" :coin="selectedCoin" :analysis="analysisResult"
            :loading="analysisLoading" @close="showAnalysis = false" />
        <MobileChartModal :show="showMobileChart" :symbol="selectedCoin?.tvSymbol" @close="showMobileChart = false" />
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #1f2937;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #4b5563;
    border-radius: 3px;
}
</style>
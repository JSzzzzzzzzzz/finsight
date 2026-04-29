<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PortfolioChart from '@/Components/PortfolioChart.vue';
import LeaderboardModal from '@/Components/LeaderboardModal.vue';
import RiskModal from '@/Components/RiskModal.vue';
import { ref } from 'vue';

const showLeaderboard = ref(false);
const showRiskModal = ref(false);

const props = defineProps({
    portfolios: Array,
    cash: Number,
    totalBalance: Number
})

const portfolio = {
    balance: '12,450.00',
    profit: '450.00',
    profitPercent: '3.5',
    realized: '50.00',
    unrealized: '400.00',
    riskLevel: 'High',
};

</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="flex flex-row flex-wrap justify-between items-center gap-4">
                <h2
                    class="font-bold text-lg md:text-xl leading-tight text-transparent bg-clip-text bg-gradient-to-r from-teal-400 to-cyan-400">
                    My Portfolio Dashboard
                </h2>

                <div class="flex space-x-2 md:space-x-4">
                    <button @click="showRiskModal = true"
                        class="inline-flex justify-center items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-bold text-[10px] md:text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none transition shadow-lg whitespace-nowrap">
                        Simulate Risk
                    </button>

                    <button @click="showLeaderboard = true"
                        class="inline-flex justify-center items-center px-3 py-2 bg-gradient-to-r from-teal-500 to-cyan-500 border border-transparent rounded-md font-bold text-[10px] md:text-xs text-white uppercase tracking-widest hover:from-teal-600 hover:to-cyan-600 active:scale-95 focus:outline-none transition shadow-lg whitespace-nowrap">
                        Leaderboard
                    </button>
                </div>
            </div>
        </template>

        <div class="py-4 md:py-8 lg:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <div class="grid grid-cols-2 lg:grid-cols-2 gap-4 lg:gap-6">

                    <div
                        class="col-span-2 lg:col-span-1 lg:row-span-2 bg-gradient-to-br from-teal-700 via-cyan-600 to-teal-400 overflow-hidden shadow-xl rounded-xl p-5 text-white relative flex flex-col justify-between min-h-[180px] lg:min-h-[260px]">

                        <div>
                            <div class="text-teal-100 font-bold uppercase tracking-wider text-xs mb-1">Total Profit
                                (PnL)</div>
                            <div class="text-4xl lg:text-5xl font-extrabold text-white mt-1 mb-2">+ RM {{
                                portfolio.profit }}
                            </div>

                            <div class="flex items-center text-sm lg:text-base text-teal-50 font-bold">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                </svg>
                                {{ portfolio.profitPercent }}% this week
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 border-t border-teal-400/30 pt-3 mt-4">
                            <div>
                                <span
                                    class="block text-[10px] uppercase tracking-wide text-teal-200 opacity-90 mb-0.5">Unrealized</span>
                                <span class="font-bold text-base lg:text-lg text-white">+{{ portfolio.unrealized
                                }}</span>
                            </div>
                            <div class="text-right lg:text-left">
                                <span
                                    class="block text-[10px] uppercase tracking-wide text-teal-200 opacity-90 mb-0.5">Realized</span>
                                <span class="font-bold text-base lg:text-lg text-white">+{{ portfolio.realized }}</span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="col-span-1 bg-gray-800 overflow-hidden shadow-xl rounded-xl p-4 lg:p-5 border-l-4 border-teal-500 flex flex-col justify-center">
                        <div class="text-gray-400 font-bold uppercase tracking-wider text-[10px] lg:text-xs truncate">
                            Total
                            Balance</div>
                        <div class="text-xl lg:text-3xl font-bold text-white mt-1 lg:mt-2 truncate">RM {{
                            props.totalBalance.toFixed(2) }}
                        </div>
                        <div class="text-[10px] lg:text-xs text-gray-500 mt-1 truncate">≈ $2,950.00 USD</div>
                    </div>

                    <div
                        class="col-span-1 bg-gray-800 overflow-hidden shadow-xl rounded-xl p-4 lg:p-5 border-l-4 border-red-500 flex flex-col justify-center">
                        <div class="text-gray-400 font-bold uppercase tracking-wider text-[10px] lg:text-xs truncate">
                            Risk Level
                        </div>
                        <div class="text-xl lg:text-3xl font-bold text-red-400 mt-1 lg:mt-2">{{ portfolio.riskLevel }}
                        </div>
                        <div class="text-[10px] lg:text-xs text-red-400/80 mt-1 flex items-center truncate">
                            <span class="animate-pulse mr-1">⚠️</span> Volatility Alert
                        </div>
                    </div>

                </div>

                <div class="bg-gray-800 overflow-hidden shadow-xl rounded-xl p-4 lg:p-6 border border-gray-700">
                    <h3 class="text-base lg:text-lg font-bold text-white mb-4">Performance History</h3>
                    <PortfolioChart />
                </div>

                <div class="bg-gray-800 overflow-hidden shadow-xl rounded-xl border border-gray-700">
                    <div class="p-5 border-b border-gray-700">
                        <h3 class="text-base lg:text-lg font-bold text-white">Your Assets</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-900">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                        Asset</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                        Amount</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                        AVG Price</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                        24h Change</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">
                                        Value (RM)</th>
                                </tr>
                            </thead>
                            <!-- Fiat cash MYR from wallet -->
                            <tr class="hover:bg-gray-700 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="h-8 w-8 rounded-full bg-yellow-500 flex items-center justify-center text-white font-bold text-xs mr-3">
                                            M
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-white">Malaysian Ringgit</div>
                                            <div class="text-sm text-gray-400">MYR</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                    --
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                    --
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                    --
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-white">
                                    RM {{ props.cash.toFixed(2) }}
                                </td>
                            </tr>
                            <!-- Crypto assets -->
                            <tbody class="bg-gray-800 divide-y divide-gray-700">
                                <tr v-for="item in props.portfolios" :key="item.id"
                                    class="hover:bg-gray-700 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="h-8 w-8 rounded-full bg-gradient-to-br from-teal-400 to-cyan-600 flex items-center justify-center text-white font-bold text-xs mr-3">
                                                {{ item.asset.symbol[0] }}
                                            </div>
                                            <div>
                                                <!-- fallback if name is null -->
                                                <div class="text-sm font-bold text-white">
                                                    {{ item.asset.name || item.asset.symbol }}
                                                </div>
                                                <div class="text-sm text-gray-400">
                                                    {{ item.asset.symbol }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        {{ item.amount }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        RM {{ item.avg_buy_price ? item.avg_buy_price.toFixed(2) : '--' }}
                                    </td>

                                    <!-- 24H change will be done later -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                        --
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-white">
                                        RM {{ item.value ? item.value.toFixed(2) : '--' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <LeaderboardModal :show="showLeaderboard" @close="showLeaderboard = false" />
        <RiskModal :show="showRiskModal" @close="showRiskModal = false" />

    </AppLayout>
</template>
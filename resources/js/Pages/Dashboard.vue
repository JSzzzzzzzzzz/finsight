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
    totalBalance: Number,
    snapshots: Array
});

</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="flex flex-row flex-wrap justify-between items-center gap-4">
                <h2
                    class="font-bold text-lg md:text-xl text-transparent bg-clip-text bg-gradient-to-r from-teal-400 to-cyan-400">
                    My Portfolio Dashboard
                </h2>

                <div class="flex space-x-2 md:space-x-4">
                    <button @click="showRiskModal = true"
                        class="px-3 py-2 bg-red-600 rounded-md text-white text-xs font-bold hover:bg-red-500">
                        Simulate Risk
                    </button>

                    <button @click="showLeaderboard = true"
                        class="px-3 py-2 bg-gradient-to-r from-teal-500 to-cyan-500 rounded-md text-white text-xs font-bold hover:from-teal-600 hover:to-cyan-600">
                        Leaderboard
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 space-y-6">
                <!-- TOP CARDS -->
                <div class="grid grid-cols-2 gap-6">

                    <!-- TOTAL PORTFOLIO VALUE -->
                    <div class="bg-gray-800 rounded-xl p-5 border-l-4 border-teal-500">
                        <div class="text-gray-400 text-xs uppercase">
                            Total Portfolio Value
                        </div>
                        <div class="text-3xl font-bold text-white mt-2">
                            RM {{ props.totalBalance.toFixed(2) }}
                        </div>
                    </div>

                    <!-- RISK -->
                    <div class="bg-gray-800 rounded-xl p-5 border-l-4 border-red-500">
                        <div class="text-gray-400 text-xs uppercase">
                            Risk Level
                        </div>
                        <div class="text-3xl font-bold text-red-400 mt-2">
                            High
                        </div>
                        <div class="text-xs text-red-400 mt-1">
                            ⚠️ Volatility Alert
                        </div>
                    </div>

                </div>

                <!-- CHART -->
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
                    <h3 class="text-lg font-bold text-white mb-4">
                        Portfolio Value Trend
                    </h3>
                    <PortfolioChart :data="props.snapshots" />
                </div>

                <!-- ASSETS TABLE -->
                <div class="bg-gray-800 rounded-xl border border-gray-700">
                    <div class="p-5 border-b border-gray-700">
                        <h3 class="text-lg font-bold text-white">
                            Your Assets
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs text-gray-400 uppercase">Asset</th>
                                    <th class="px-6 py-3 text-left text-xs text-gray-400 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-right text-xs text-gray-400 uppercase">Value (RM)</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-700">

                                <!-- MYR CASH -->
                                <tr>
                                    <td class="px-6 py-4 flex items-center">
                                        <div
                                            class="h-8 w-8 bg-yellow-500 rounded-full flex items-center justify-center text-white mr-3">
                                            M
                                        </div>
                                        <div>
                                            <div class="text-white font-bold">Malaysian Ringgit</div>
                                            <div class="text-gray-400 text-sm">MYR</div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-gray-300">--</td>

                                    <td class="px-6 py-4 text-right text-white font-bold">
                                        RM {{ props.cash.toFixed(2) }}
                                    </td>
                                </tr>

                                <!-- CRYPTO -->
                                <tr v-for="item in props.portfolios" :key="item.id">
                                    <td class="px-6 py-4 flex items-center">
                                        <div
                                            class="h-8 w-8 bg-gradient-to-br from-teal-400 to-cyan-600 rounded-full flex items-center justify-center text-white mr-3">
                                            {{ item.asset.symbol[0] }}
                                        </div>
                                        <div>
                                            <div class="text-white font-bold">
                                                {{ item.asset.name || item.asset.symbol }}
                                            </div>
                                            <div class="text-gray-400 text-sm">
                                                {{ item.asset.symbol }}
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-gray-300">
                                        {{ item.amount }}
                                    </td>

                                    <td class="px-6 py-4 text-right text-white font-bold">
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
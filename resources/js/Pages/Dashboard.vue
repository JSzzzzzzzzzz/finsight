<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PortfolioChart from '@/Components/PortfolioChart.vue';
import LeaderboardModal from '@/Components/LeaderboardModal.vue';
import RiskModal from '@/Components/RiskModal.vue';
import RiskNudgeModal from '@/Components/RiskNudgeModal.vue';
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const showLeaderboard = ref(false);
const showRiskModal = ref(false);

const showRiskNudge = ref(false);

const riskNudge = ref({
    title: '',
    message: '',
    riskScore: 0,
    riskLevel: '',
    suggestion: '',
});

const riskSimulation = ref({
    title: '',
    message: '',
    impact: '',
    suggestion: '',
    currentValue: 0,
    dropPercent: 12.5,
    estimatedLoss: 0,
    projectedValue: 0,
    scenario: 'Moderate',
});

const checkRiskNudge = async () => {
    try {
        const response = await fetch(route('market.summary'), {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) return;

        const data = await response.json();

        const riskScore = data.sentiment?.risk_score ?? 0;
        const riskLevel = data.sentiment?.risk_level ?? 'Unknown';

        console.log('Risk nudge check:', {
            riskScore,
            riskLevel,
            changeAmount: props.changeAmount,
        });

        if (riskScore >= 65 && props.changeAmount < 0) {
            riskNudge.value = {
                title: 'AI Risk Nudge: Elevated Portfolio Risk',
                message: 'FinSight detected negative market sentiment while your portfolio value is declining.',
                riskScore,
                riskLevel,
                suggestion: 'Consider reviewing your current exposure and monitoring market updates before making further decisions.',
            };

            showRiskNudge.value = true;
        }
    } catch (error) {
        console.error('Failed to check risk nudge:', error);
    }
};

const triggerRiskSimulation = () => {
    calculateRiskSimulation('Moderate', 15);
    showRiskModal.value = true;
};


const calculateRiskSimulation = (scenario, dropPercent) => {
    const currentValue = props.totalBalance ?? 0;
    const estimatedLoss = currentValue * (dropPercent / 100);
    const projectedValue = currentValue - estimatedLoss;

    riskSimulation.value = {
        title: 'Risk Simulation: Market Drop Scenario',
        message: `FinSight simulated a ${dropPercent}% market drop scenario based on your current portfolio value.`,
        impact: `Estimated loss: RM ${estimatedLoss.toFixed(2)}. Projected portfolio value: RM ${projectedValue.toFixed(2)}.`,
        suggestion: 'This simulation is for educational purposes only. Review your portfolio exposure and monitor volatile assets carefully.',
        currentValue,
        dropPercent,
        estimatedLoss,
        projectedValue,
        scenario,
    };
};

const syncLuno = () => {
    router.post(route('sync.luno'));
};

const props = defineProps({
    portfolios: Array,
    cash: Number,
    totalBalance: Number,
    snapshots: Array,
    percentChange: Number,
    changeAmount: Number
});

onMounted(() => {
    checkRiskNudge();
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
                    <button @click="triggerRiskSimulation"
                        class="px-3 py-2 bg-red-600 rounded-md text-white text-xs font-bold hover:bg-red-500">
                        Simulate Risk
                    </button>

                    <button @click="showLeaderboard = true"
                        class="px-3 py-2 bg-gradient-to-r from-teal-500 to-cyan-500 rounded-md text-white text-xs font-bold hover:from-teal-600 hover:to-cyan-600">
                        Leaderboard
                    </button>

                    <button @click="syncLuno"
                        class="px-3 py-2 bg-gradient-to-r from-teal-500 to-cyan-500 rounded-md text-white text-xs font-bold hover:from-teal-600 hover:to-cyan-600">
                        Sync Portfolio
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 space-y-6">
                <!-- TOP CARDS -->
                <div class="overflow-x-auto lg:overflow-visible pb-2">
                    <div
                        class="flex lg:grid lg:grid-cols-[2fr_1fr] lg:grid-rows-2 gap-4 lg:gap-6 w-max lg:w-auto snap-x snap-mandatory">

                        <!-- LEFT BIG PORTFOLIO CHANGE CARD -->
                        <div
                            class="snap-start w-[320px] lg:w-auto lg:row-span-2 bg-gradient-to-br from-teal-700 via-cyan-600 to-teal-400 rounded-xl p-5 text-white shadow-xl flex flex-col justify-between min-h-[190px] lg:min-h-[240px]">
                            <div>
                                <div class="text-teal-100 font-bold uppercase tracking-wider text-xs mb-1">
                                    Portfolio Change
                                </div>

                                <div class="text-4xl lg:text-5xl font-extrabold text-white mt-2 mb-2">
                                    {{ props.changeAmount >= 0 ? '+' : '-' }} RM {{
                                        Math.abs(props.changeAmount).toFixed(2) }}
                                </div>

                                <div class="flex items-center text-sm lg:text-base font-bold"
                                    :class="props.percentChange >= 0 ? 'text-green-100' : 'text-red-100'">
                                    <span class="mr-1">
                                        {{ props.percentChange >= 0 ? '↑' : '↓' }}
                                    </span>
                                    {{ Math.abs(props.percentChange).toFixed(2) }}% since previous snapshot
                                </div>
                            </div>

                            <div class="border-t border-teal-400/30 pt-3 mt-4">
                                <span class="block text-[10px] uppercase tracking-wide text-teal-100 opacity-90">
                                    Insight
                                </span>
                                <span class="font-bold text-sm text-white">
                                    Based on daily portfolio value snapshots.
                                </span>
                            </div>
                        </div>

                        <!-- RIGHT TOP CARD -->
                        <div
                            class="snap-start w-[320px] lg:w-auto bg-gray-800 rounded-xl p-5 border-l-4 border-teal-500 shadow-xl flex flex-col justify-center min-h-[115px]">
                            <div class="text-gray-400 text-xs uppercase">
                                Total Portfolio Value
                            </div>

                            <div class="text-3xl font-bold text-white mt-2">
                                RM {{ props.totalBalance.toFixed(2) }}
                            </div>
                        </div>

                        <!-- RIGHT BOTTOM CARD -->
                        <div
                            class="snap-start w-[320px] lg:w-auto bg-gray-800 rounded-xl p-5 border-l-4 border-red-500 shadow-xl flex flex-col justify-center min-h-[115px]">
                            <div class="text-gray-400 text-xs uppercase">
                                Risk Level
                            </div>

                            <div class="text-3xl font-bold text-red-400 mt-2">
                                High
                            </div>

                            <div class="text-xs text-red-400 mt-2">
                                ⚠️ Volatility Alert
                            </div>
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
        <RiskModal :show="showRiskModal" :risk="riskSimulation" @simulate="calculateRiskSimulation"
            @close="showRiskModal = false" />
        <RiskNudgeModal :show="showRiskNudge" :nudge="riskNudge" @close="showRiskNudge = false" />
    </AppLayout>
</template>
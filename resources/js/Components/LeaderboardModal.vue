<script setup>
import { ref, watch, onUnmounted } from 'vue';

const props = defineProps({
    show: Boolean,
});

defineEmits(['close']);

// Scroll Lock Logic
watch(() => props.show, (show) => {
    if (show) {
        document.body.style.overflow = 'hidden';
        fetchLeaderboard();
    } else {
        document.body.style.overflow = null;
    }
});

onUnmounted(() => {
    document.body.style.overflow = null;
});

// Leaderboard data from backend
const leaders = ref([]);
const loading = ref(false);

const fetchLeaderboard = async () => {
    loading.value = true;

    try {
        const response = await fetch(route('leaderboard.data'), {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) return;

        leaders.value = await response.json();
    } catch (error) {
        console.error('Failed to fetch leaderboard:', error);
    } finally {
        loading.value = false;
    }
};

const getRankColor = (rank) => {
    if (rank === 1) return 'text-yellow-400 drop-shadow-md';
    if (rank === 2) return 'text-gray-300';
    if (rank === 3) return 'text-amber-600';
    return 'text-gray-500';
};
</script>

<template v-else>
    <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0"
        enter-to-class="opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm transition-opacity"
            @click="$emit('close')">

            <transition enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 translate-y-4 sm:scale-95"
                enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                leave-to-class="opacity-0 translate-y-4 sm:scale-95">
                <div class="bg-gray-800 rounded-lg shadow-2xl overflow-hidden transform transition-all w-full max-w-md m-4 border border-gray-700"
                    @click.stop>

                    <div class="bg-gradient-to-r from-teal-500 to-cyan-600 px-4 py-3 flex justify-between items-center">
                        <div class="flex items-center">
                            <span class="text-2xl mr-2">🏆</span>
                            <h3 class="text-lg font-bold text-white">Top Performers</h3>
                        </div>
                        <button @click="$emit('close')" class="text-gray-400 hover:text-white transition">✕</button>
                    </div>

                    <div class="p-0">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Rank
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Trader
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">
                                        Portfolio Value</th>
                                </tr>
                            </thead>
                            <tr v-if="loading">
                                <td colspan="3" class="px-4 py-6 text-center text-gray-400">
                                    Loading leaderboard...
                                </td>
                            </tr>

                            <tr v-else-if="leaders.length === 0">
                                <td colspan="3" class="px-4 py-6 text-center text-gray-400">
                                    No leaderboard data available.
                                </td>
                            </tr>
                            <tbody class="bg-gray-800 divide-y divide-gray-700">
                                <tr v-for="leader in leaders" :key="leader.rank"
                                    :class="{ 'bg-teal-900/20': leader.name.includes('You') }">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="font-bold text-lg" :class="getRankColor(leader.rank)">#{{
                                            leader.rank }}</span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-white">{{ leader.name }}</div>
                                        <div class="text-xs text-gray-400">{{ leader.amount }} total value</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right">
                                        <span
                                            class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-teal-500/10 text-teal-400 border border-teal-500/20">
                                            RM {{ Number(leader.total_value).toFixed(2) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-gray-900 px-4 py-3 text-right sm:px-6 border-t border-gray-700">
                        <p class="text-xs text-gray-500">Rankings based on latest portfolio snapshot.</p>
                    </div>
                </div>
            </transition>
        </div>
    </transition>
</template>
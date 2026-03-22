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
    } else {
        document.body.style.overflow = null;
    }
});

onUnmounted(() => {
    document.body.style.overflow = null;
});

// FAKE DATA
const leaders = ref([
    { rank: 1, name: 'CryptoKing_99', profit: '+145.2%', amount: '$12,450', trend: 'up' },
    { rank: 2, name: 'Sarah Ventures', profit: '+98.5%', amount: '$8,320', trend: 'up' },
    { rank: 3, name: 'Mike D.', profit: '+45.2%', amount: '$3,100', trend: 'up' },
    { rank: 4, name: 'Alex T.', profit: '+22.1%', amount: '$1,200', trend: 'down' },
    { rank: 5, name: 'You (Admin)', profit: '+3.5%', amount: '$450', trend: 'up' },
]);

const getRankColor = (rank) => {
    if (rank === 1) return 'text-yellow-400 drop-shadow-md'; 
    if (rank === 2) return 'text-gray-300';   
    if (rank === 3) return 'text-amber-600';  
    return 'text-gray-500';
};
</script>

<template>
    <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm transition-opacity" @click="$emit('close')">
            
            <transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 translate-y-4 sm:scale-95"
                enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                leave-to-class="opacity-0 translate-y-4 sm:scale-95"
            >
                <div class="bg-gray-800 rounded-lg shadow-2xl overflow-hidden transform transition-all w-full max-w-md m-4 border border-gray-700" @click.stop>
                    
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
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Rank</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Trader</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">7D Profit</th>
                                </tr>
                            </thead>
                            <tbody class="bg-gray-800 divide-y divide-gray-700">
                                <tr v-for="leader in leaders" :key="leader.rank" 
                                    :class="{'bg-teal-900/20': leader.name.includes('You')}"> <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="font-bold text-lg" :class="getRankColor(leader.rank)">#{{ leader.rank }}</span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-white">{{ leader.name }}</div>
                                        <div class="text-xs text-gray-400">{{ leader.amount }} profit</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right">
                                        <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full"
                                            :class="leader.trend === 'up' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'">
                                            <span v-if="leader.trend === 'up'" class="mr-1">↑</span>
                                            <span v-else class="mr-1">↓</span>
                                            {{ leader.profit }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="bg-gray-900 px-4 py-3 text-right sm:px-6 border-t border-gray-700">
                        <p class="text-xs text-gray-500">Rankings updated every hour.</p>
                    </div>
                </div>
            </transition>
        </div>
    </transition>
</template>
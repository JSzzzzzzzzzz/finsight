<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ApiModal from '@/Components/ApiModal.vue';
import { ref } from 'vue';

const watchlist = ref([
    { symbol: 'BTCMYR', exchange: 'Luno', status: 'Active' },
    { symbol: 'ETHMYR', exchange: 'Luno', status: 'Active' },
    { symbol: 'XRPMYR', exchange: 'Luno', status: 'Active' },
]);

const newPair = ref('');
const showApiModal = ref(false);
const savedApiKey = ref(null); 

const addPair = () => {
    if (newPair.value) {
        watchlist.value.push({ 
            symbol: newPair.value.toUpperCase(), 
            exchange: 'Manual', 
            status: 'Pending' 
        });
        newPair.value = ''; 
    }
};

const handleApiSave = (maskedKey) => {
    savedApiKey.value = maskedKey;
    alert("Success! Keys encrypted and saved.");
};
</script>

<template>
    <AppLayout title="Settings">
        
        <template #header>
            <div class="flex flex-row justify-between items-center gap-3 relative">
                
                <h2 class="font-bold text-base md:text-xl leading-tight text-transparent bg-clip-text bg-gradient-to-r from-teal-400 to-cyan-400 text-left">
                    System Configuration
                </h2>

                <button @click="showApiModal = !showApiModal" 
                     class="w-auto inline-flex justify-center items-center px-4 py-2 md:px-6 md:py-3 bg-gradient-to-r from-teal-500 to-cyan-600 border border-transparent rounded-lg font-bold text-[10px] md:text-xs text-white uppercase tracking-widest hover:from-teal-600 hover:to-cyan-700 active:scale-95 focus:outline-none transition shadow-lg hover:shadow-teal-500/30 whitespace-nowrap">
                    
                    <span v-if="!savedApiKey" class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 md:h-4 md:w-4 mr-1 md:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        API Connection
                    </span>
                    <span v-else class="text-white flex items-center font-bold drop-shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 md:h-4 md:w-4 mr-1 md:mr-2" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Exchange Connected
                    </span>
                </button>

                <ApiModal 
                    :show="showApiModal" 
                    :existingApiKey="savedApiKey" 
                    @close="showApiModal = false"
                    @save="handleApiSave"
                />
            </div>
        </template>

        <div class="py-6 md:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-gray-800 shadow-2xl rounded-xl p-5 md:p-8 border border-gray-700">
                    
                    <div class="mb-8 border-b border-gray-700 pb-5">
                        <h3 class="text-xl font-bold text-white">Manage Trading Pairs</h3>
                        <p class="text-sm text-gray-400 mt-2 leading-relaxed max-w-2xl">
                            Add the cryptocurrency pairs you want FinSight to monitor. The AI will prioritize analysis for these assets.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 mb-8">
                        <div class="relative w-full">
                             <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm"></span>
                              </div>
                            <input v-model="newPair" type="text" placeholder="Enter pair symbol (e.g. BTCUSDT)" 
                                class="bg-gray-900/50 border border-gray-600 text-white text-sm placeholder-gray-500 focus:border-teal-500 focus:ring-teal-500 rounded-lg shadow-inner w-full py-4 pl-10 transition-all"
                                @keyup.enter="addPair"
                            >
                        </div>
                        <button @click="addPair" class="w-full sm:w-auto bg-teal-500 hover:bg-teal-400 text-white font-bold py-4 px-8 rounded-lg shadow-lg hover:shadow-teal-500/30 transition duration-200 ease-in-out whitespace-nowrap flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Add Pair
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-700 bg-gray-900/30">
                        <table class="min-w-full divide-y divide-gray-700/50">
                            <thead class="bg-gray-900/80">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Asset Symbol</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider hidden md:table-cell">Source</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/50">
                                <tr v-for="item in watchlist" :key="item.symbol" class="group hover:bg-gray-800/50 transition duration-150 ease-in-out">
                                    
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 rounded-xl bg-gray-800 border border-gray-700 flex items-center justify-center text-teal-400 font-bold text-sm mr-4 shadow-sm group-hover:border-teal-500/50 group-hover:shadow-teal-500/20 transition">
                                                {{ item.symbol.substring(0,1) }}
                                            </div>
                                            <div>
                                                <div class="text-base font-bold text-white group-hover:text-teal-50 transition">{{ item.symbol }}</div>
                                                <div class="text-xs text-gray-500 md:hidden mt-1">{{ item.exchange }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-5 whitespace-nowrap hidden md:table-cell">
                                        <div class="text-sm font-medium text-gray-300 flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                            {{ item.exchange }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <span v-if="item.status === 'Active'" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-[0_0_10px_rgba(16,185,129,0.1)]">
                                            <span class="relative flex h-2 w-2 mr-2">
                                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                            </span>
                                            Monitoring
                                        </span>
                                        <span v-else class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Pending
                                        </span>
                                    </td>

                                    <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-red-500 hover:text-red-400 transition-colors flex items-center justify-end gap-1 ml-auto font-bold group/btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover/btn:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span class="hidden md:inline">Remove</span>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </AppLayout>
</template>
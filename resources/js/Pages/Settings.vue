<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ApiModal from '@/Components/ApiModal.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    availablePairs: Array,
    selectedPairs: Array,
    selectedPairIds: Array,
});

const showApiModal = ref(false);
const showPairModal = ref(false);
const savedApiKey = ref(null);

const addPair = (pairId) => {
    router.post(route('settings.pairs.store'), {
        trading_pair_id: pairId
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showPairModal.value = false;
        }
    });
};

const removePair = (id) => {
    if (!confirm('Remove this pair from your watchlist?')) return;

    router.delete(route('settings.pairs.destroy', id), {
        preserveScroll: true
    });
};

const handleApiSave = (maskedKey) => {
    savedApiKey.value = maskedKey;
    alert("Success! Keys encrypted and saved.");
};

const displayPair = (symbol) => {
    return symbol === 'XBTMYR' ? 'BTCMYR' : symbol;
};

const displaySymbolInitial = (symbol) => {
    return symbol === 'XBTMYR' ? 'B' : symbol.substring(0, 1);
};

</script>

<template>
    <AppLayout title="Settings">

        <template #header>
            <div class="flex flex-row justify-between items-center gap-3 relative">
                <h2
                    class="font-bold text-base md:text-xl leading-tight text-transparent bg-clip-text bg-gradient-to-r from-teal-400 to-cyan-400 text-left">
                    System Configuration
                </h2>

                <button @click="showApiModal = !showApiModal"
                    class="w-auto inline-flex justify-center items-center px-4 py-2 md:px-6 md:py-3 bg-gradient-to-r from-teal-500 to-cyan-600 border border-transparent rounded-lg font-bold text-[10px] md:text-xs text-white uppercase tracking-widest hover:from-teal-600 hover:to-cyan-700 active:scale-95 focus:outline-none transition shadow-lg hover:shadow-teal-500/30 whitespace-nowrap">
                    API Connection
                </button>

                <ApiModal :show="showApiModal" :existingApiKey="savedApiKey" @close="showApiModal = false"
                    @save="handleApiSave" />
            </div>
        </template>

        <div class="py-6 md:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <div class="bg-gray-800 shadow-2xl rounded-xl p-5 md:p-8 border border-gray-700">

                    <div
                        class="mb-8 border-b border-gray-700 pb-5 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">

                        <div>
                            <h3 class="text-xl font-bold text-white">
                                Manage Trading Pairs
                            </h3>

                            <p class="text-sm text-gray-400 mt-2 leading-relaxed max-w-2xl">
                                Select the cryptocurrency pairs you want FinSight to monitor.
                            </p>
                        </div>

                        <button @click="showPairModal = true"
                            class="w-full sm:w-auto bg-teal-500 hover:bg-teal-400 text-white font-bold py-3 px-6 rounded-lg shadow-lg hover:shadow-teal-500/30 transition duration-200 ease-in-out whitespace-nowrap flex items-center justify-center">
                            + Add Pair
                        </button>

                    </div>

                    <!-- SELECTED PAIRS TABLE -->
                    <div class="overflow-x-auto rounded-lg border border-gray-700 bg-gray-900/30">
                        <table class="min-w-full divide-y divide-gray-700/50">
                            <thead class="bg-gray-900/80">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">
                                        Asset Symbol</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider hidden md:table-cell">
                                        Source</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">
                                        Action</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-700/50">
                                <tr v-for="item in props.selectedPairs" :key="item.id"
                                    class="group hover:bg-gray-800/50 transition duration-150 ease-in-out">
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="h-10 w-10 rounded-xl bg-gray-800 border border-gray-700 flex items-center justify-center text-teal-400 font-bold text-sm mr-4">
                                               {{ displaySymbolInitial(item.trading_pair.symbol) }}
                                            </div>
                                            <div>
                                                <div class="text-base font-bold text-white">
                                                    {{ displayPair(item.trading_pair.symbol) }} </div>
                                                <div class="text-xs text-gray-500 md:hidden mt-1">
                                                    {{ item.trading_pair.source }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-5 whitespace-nowrap hidden md:table-cell">
                                        <div class="text-sm font-medium text-gray-300">
                                            {{ item.trading_pair.source }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <span class="relative flex h-2 w-2 mr-2">
                                                <span
                                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                <span
                                                    class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                            </span>
                                            Monitoring
                                        </span>
                                    </td>

                                    <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="removePair(item.id)"
                                            class="text-red-500 hover:text-red-400 transition-colors font-bold">
                                            Remove
                                        </button>
                                    </td>
                                </tr>

                                <tr v-if="props.selectedPairs.length === 0">
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                        No trading pairs selected yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        <!-- ADD PAIR MODAL -->
        <div v-if="showPairModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4">
            <div class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg p-6">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-lg font-bold text-white">
                        Add Trading Pair
                    </h3>

                    <button @click="showPairModal = false" class="text-gray-400 hover:text-white">
                        ✕
                    </button>
                </div>

                <p class="text-sm text-gray-400 mb-5">
                    Choose from admin-approved active trading pairs.
                </p>

                <div class="space-y-3 max-h-80 overflow-y-auto">
                    <button v-for="pair in props.availablePairs" :key="pair.id" @click="addPair(pair.id)"
                        class="w-full flex justify-between items-center bg-gray-900 hover:bg-gray-700 border border-gray-700 rounded-lg px-4 py-3 text-left transition">
                        <div>
                            <div class="text-white font-bold">
                                {{ pair.symbol }}
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ pair.source }}
                            </div>
                        </div>

                        <span class="text-teal-400 text-sm font-bold">
                            Add
                        </span>
                    </button>

                    <div v-if="props.availablePairs.length === 0" class="text-center text-gray-400 py-8">
                        No available pairs to add.
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
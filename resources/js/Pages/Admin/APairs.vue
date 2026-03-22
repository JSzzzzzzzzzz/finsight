<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

const newAsset = ref('');
const supportedAssets = ref([
    { symbol: 'BTCMYR', source: 'Luno', status: 'Live' },
    { symbol: 'ETHMYR', source: 'Luno', status: 'Live' },
    { symbol: 'XRPMYR', source: 'Luno', status: 'Maintenance' },
]);

const addAsset = () => {
    if(newAsset.value) {
        supportedAssets.value.push({ symbol: newAsset.value.toUpperCase(), source: 'Manual', status: 'Pending' });
        newAsset.value = '';
    }
};

const removeAsset = (index) => supportedAssets.value.splice(index, 1);
</script>

<template>
    <AppLayout title="Manage Pairs">
        <template #header>
            <h2 class="font-bold text-xl text-white leading-tight">Platform Trading Pairs</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700 flex gap-4">
                    <input v-model="newAsset" type="text" placeholder="e.g. SOLMYR" class="flex-1 bg-gray-900 border border-gray-600 text-white rounded-lg p-3">
                    <button @click="addAsset" class="bg-teal-500 hover:bg-teal-400 text-white font-bold px-6 rounded-lg transition">
                        + Add Pair
                    </button>
                </div>

                <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase">Pair</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase">Source</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-400 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            <tr v-for="(asset, index) in supportedAssets" :key="index">
                                <td class="px-6 py-4 font-bold text-white">{{ asset.symbol }}</td>
                                <td class="px-6 py-4 text-gray-400 text-sm">{{ asset.source }}</td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="removeAsset(index)" class="text-red-400 hover:text-red-300 font-bold text-xs uppercase">Remove</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
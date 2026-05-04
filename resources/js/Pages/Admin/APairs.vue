<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    pairs: Array
});

const form = useForm({
    symbol: ''
});

const addPair = () => {
    if (!form.symbol) return;

    form.symbol = form.symbol.toUpperCase();

    form.post(route('admin.pairs.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset()
    });
};

const removePair = (id) => {
    if (!confirm('Remove this trading pair?')) return;

    router.delete(route('admin.pairs.destroy', id), {
        preserveScroll: true
    });
};
</script>

<template>
    <AppLayout title="Manage Pairs">
        <template #header>
            <h2 class="font-bold text-xl text-white leading-tight">
                Platform Trading Pairs
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- ADD PAIR -->
                <div class="bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
                    <div class="flex flex-col md:flex-row gap-4">
                        <input v-model="form.symbol" type="text" placeholder="e.g. BTCMYR"
                            class="flex-1 bg-gray-900 border border-gray-600 text-white rounded-lg p-3 uppercase">

                        <button @click="addPair" :disabled="form.processing"
                            class="bg-teal-500 hover:bg-teal-400 disabled:opacity-50 text-white font-bold px-6 py-3 rounded-lg transition">
                            + Add Pair
                        </button>
                    </div>

                    <div v-if="form.errors.symbol" class="text-red-400 text-sm mt-3">
                        {{ form.errors.symbol }}
                    </div>
                </div>

                <!-- PAIRS TABLE -->
                <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase">
                                    Pair
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase">
                                    Source
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-400 uppercase">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-700">
                            <tr v-for="pair in props.pairs" :key="pair.id">
                                <td class="px-6 py-4 font-bold text-white">
                                    {{ pair.symbol }}
                                </td>

                                <td class="px-6 py-4 text-gray-400 text-sm">
                                    {{ pair.source }}
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-bold bg-teal-500/10 text-teal-400 border border-teal-500/30">
                                        {{ pair.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <button @click="removePair(pair.id)"
                                        class="text-red-400 hover:text-red-300 font-bold text-xs uppercase">
                                        Remove
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="props.pairs.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                    No trading pairs added yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
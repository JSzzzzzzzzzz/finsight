<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    pairs: Array
});

const form = useForm({
    symbol: ''
});

const importLunoPairs = () => {
    router.post(route('admin.pairs.import-luno'), {}, {
        preserveScroll: true
    });
};

const togglePair = (id) => {
    router.patch(route('admin.pairs.toggle', id), {}, {
        preserveScroll: true
    });
};

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

                <!-- IMPORT LUNO PAIRS -->
                <div class="bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <h3 class="text-white font-bold text-lg">
                                Import Exchange Trading Pairs
                            </h3>
                            <p class="text-gray-400 text-sm mt-1">
                                 Existing pairs will be skipped.
                            </p>
                        </div>

                        <button @click="importLunoPairs"
                            class="bg-teal-500 hover:bg-teal-400 text-white font-bold px-6 py-3 rounded-lg transition">
                            Import Pairs
                        </button>
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
                                    <span class="px-3 py-1 rounded-full text-xs font-bold border" :class="pair.is_active
                                        ? 'bg-teal-500/10 text-teal-400 border-teal-500/30'
                                        : 'bg-red-500/10 text-red-400 border-red-500/30'">
                                        {{ pair.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right space-x-4">
                                    <button @click="togglePair(pair.id)"
                                        class="text-yellow-400 hover:text-yellow-300 font-bold text-xs uppercase">
                                        {{ pair.is_active ? 'Deactivate' : 'Activate' }}
                                    </button>

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
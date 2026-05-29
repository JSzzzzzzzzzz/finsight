<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted, computed } from 'vue';

const services = ref([]);
const totalUsers = ref(0);
const newUsersToday = ref(0);
const lastCheckedAt = ref('--');

const overallStatus = ref('Checking...');

const operationalCount = computed(() =>
    services.value.filter(s => s.status === 'Online').length
);

const totalServices = computed(() => services.value.length);

const averageLatency = computed(() => {
    const validLatencies = services.value
        .map(s => parseInt(s.latency))
        .filter(v => !isNaN(v));

    if (validLatencies.length === 0) return 0;

    return Math.round(
        validLatencies.reduce((sum, value) => sum + value, 0) / validLatencies.length
    );
});

const activePercentage = computed(() => {
    if (totalServices.value === 0) return 0;
    return Math.round((operationalCount.value / totalServices.value) * 100);
});

const runChecks = async () => {
    overallStatus.value = 'Checking...';

    services.value = services.value.map(service => ({
        ...service,
        status: 'checking',
        latency: '---',
    }));

    try {
        const response = await fetch(route('admin.system.health.data'), {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            overallStatus.value = 'Degraded';
            return;
        }

        const data = await response.json();

        overallStatus.value = data.system_status;

        services.value = data.services.map(service => ({
            name: service.name,
            url: service.url,
            status: service.status === 'ONLINE' ? 'Online' : service.status,
            latency: service.latency ? `${service.latency}ms` : '---',
        }));

        totalUsers.value = data.total_users ?? 0;
        newUsersToday.value = data.new_users_today ?? 0;
        lastCheckedAt.value = data.checked_at ?? '--';

    } catch (error) {
        console.error('System health check failed:', error);
        overallStatus.value = 'Degraded';
    }
};

onMounted(() => {
    runChecks();
});
</script>

<template>
    <AppLayout title="System Health">

        <template #header>
            <div class="flex flex-row justify-between items-center gap-4">
                <h2 class="font-bold text-lg md:text-xl text-white leading-tight truncate">
                    System Health Monitor
                </h2>
                <div
                    class="flex-shrink-0 flex items-center gap-2 bg-gray-800 px-3 py-1.5 rounded-full border border-gray-700 shadow-sm">
                    <span class="relative flex h-2.5 w-2.5">
                        <span v-if="overallStatus === 'Operational'"
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5"
                            :class="overallStatus === 'Operational' ? 'bg-emerald-500' : 'bg-yellow-500'"></span>
                    </span>
                    <span class="text-[10px] md:text-xs font-bold text-gray-300 uppercase tracking-wide">{{
                        overallStatus }}</span>
                </div>
            </div>
        </template>

        <div class="py-6 md:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <div
                    class="flex overflow-x-auto pb-4 gap-4 snap-x snap-mandatory md:grid md:grid-cols-2 lg:grid-cols-4 md:overflow-visible md:pb-0 hide-scrollbar">

                    <div
                        class="min-w-[85vw] md:min-w-0 snap-center bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg relative overflow-hidden group">
                        <div
                            class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-teal-500/10 rounded-full blur-2xl group-hover:bg-teal-500/20 transition duration-500">
                        </div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">System Status</h3>
                        <div class="mt-3 flex items-center">
                            <svg v-if="overallStatus === 'Operational'" class="w-8 h-8 text-emerald-400 mr-3"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <svg v-else class="w-8 h-8 text-yellow-400 mr-3 animate-spin" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            <span class="text-2xl font-bold text-white">{{ overallStatus }}</span>
                        </div>
                    </div>

                    <div
                        class="min-w-[85vw] md:min-w-0 snap-center bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg relative overflow-hidden">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Active Services</h3>
                        <div class="mt-3 flex items-end">
                            <span class="text-3xl font-bold text-white">{{ operationalCount }}</span>
                            <span class="text-sm text-gray-500 mb-1 ml-1 font-medium">/ {{ totalServices }}</span>
                        </div>
                        <div class="w-full bg-gray-700 rounded-full h-1.5 mt-4">
                            <div class="bg-gradient-to-r from-teal-500 to-cyan-500 h-1.5 rounded-full transition-all duration-1000 ease-out"
                                :style="`width: ${activePercentage}%`"
                            ></div>
                        </div>
                    </div>

                    <div
                        class="min-w-[85vw] md:min-w-0 snap-center bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Users</h3>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-3xl font-bold text-white">{{ totalUsers }}</span>
                            <span
                                class="text-[10px] font-bold bg-emerald-500/10 text-emerald-400 px-2 py-1 rounded-full border border-emerald-500/20">
                                +{{ newUsersToday }} today
                            </span>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-2">Registered user accounts</p>
                    </div>

                    <div
                        class="min-w-[85vw] md:min-w-0 snap-center bg-gray-800 rounded-xl p-6 border border-gray-700 shadow-lg">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Average Latency</h3>
                        <div class="mt-3">
                            <span class="text-2xl font-bold text-white">{{ averageLatency }}ms</span>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-2">
                            Based on latest service checks
                        </p>
                    </div>
                </div>

                <div class="bg-gray-800 rounded-xl shadow-2xl border border-gray-700 overflow-hidden">

                    <div
                        class="bg-gray-900/50 px-6 py-4 border-b border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <h3 class="text-lg font-bold text-white">Service Connectivity Checks</h3>
                        <button @click="runChecks"
                            class="text-xs text-teal-400 hover:text-white transition font-bold uppercase tracking-wider flex items-center gap-1 bg-teal-900/30 px-3 py-1.5 rounded hover:bg-teal-500 hover:text-white border border-teal-500/30">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            Refresh Status
                        </button>
                    </div>

                    <div class="divide-y divide-gray-700">
                        <div v-for="service in services" :key="service.name"
                            class="p-5 hover:bg-gray-700/30 transition duration-150">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex flex-col">
                                    <span class="text-white font-bold text-sm sm:text-base flex items-center gap-2">
                                        {{ service.name }}
                                    </span>
                                    <span
                                        class="text-[10px] sm:text-xs text-gray-500 uppercase tracking-wider mt-1 font-mono">{{
                                            service.url }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between sm:justify-end gap-4 sm:gap-12 w-full sm:w-auto mt-2 sm:mt-0">
                                    <div class="text-left sm:text-right w-1/2 sm:w-24">
                                        <span
                                            class="block text-[10px] text-gray-500 uppercase tracking-wider mb-0.5">Latency</span>
                                        <span class="font-mono text-sm font-bold text-gray-300">{{ service.latency
                                            }}</span>
                                    </div>
                                    <div class="text-right w-1/2 sm:w-32">
                                        <span
                                            class="block text-[10px] text-gray-500 uppercase tracking-wider mb-1">Status</span>
                                        <span
                                            class="inline-flex items-center justify-center font-bold text-[10px] sm:text-xs px-3 py-1 rounded-full uppercase tracking-wide w-full sm:w-auto"
                                            :class="service.status === 'Online' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : (service.status === 'checking' ? 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20')">
                                            <span v-if="service.status === 'checking'"
                                                class="w-1.5 h-1.5 rounded-full bg-yellow-400 mr-2 animate-pulse"></span>
                                            <span v-if="service.status === 'Online'"
                                                class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-2"></span>
                                            {{ service.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-gray-900/50 px-6 py-3 text-[10px] sm:text-xs text-gray-500 border-t border-gray-700 flex justify-between items-center">
                        <span>Last checked: {{ lastCheckedAt }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
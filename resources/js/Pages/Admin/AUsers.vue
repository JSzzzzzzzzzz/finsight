<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';

const searchQuery = ref('');

// 1. DATA: "Active" or "Banned" only
const users = ref([
    { id: 1, name: 'Alice Johnson', email: 'alice@example.com', role: 'User', status: 'Active', joined: '2 days ago' },
    { id: 2, name: 'Bob Smith', email: 'bob@trader.com', role: 'User', status: 'Banned', joined: '1 week ago' },
    { id: 3, name: 'Charlie Dave', email: 'charlie@crypto.net', role: 'User', status: 'Active', joined: '3 weeks ago' },
    { id: 4, name: 'Diana Prince', email: 'diana@fintech.io', role: 'User', status: 'Active', joined: '1 month ago' },
    { id: 5, name: 'Eve Hacker', email: 'eve@malicious.com', role: 'User', status: 'Banned', joined: '2 months ago' },
]);

const filteredUsers = computed(() => {
    if (!searchQuery.value) return users.value;
    const lower = searchQuery.value.toLowerCase();
    return users.value.filter(u => 
        u.name.toLowerCase().includes(lower) || 
        u.email.toLowerCase().includes(lower)
    );
});

const toggleBan = (user) => {
    if (user.status === 'Active') {
        if(confirm(`Are you sure you want to BAN ${user.name}? They will lose access immediately.`)) {
            user.status = 'Banned';
        }
    } else {
        user.status = 'Active';
    }
};

// 2. STYLING
const getStatusClasses = (status) => {
    if (status === 'Active') return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 shadow-emerald-500/10';
    return 'bg-red-500/10 text-red-400 border-red-500/20 shadow-red-500/10';
};

const getStatusDot = (status) => {
    if (status === 'Active') return 'bg-emerald-500 animate-pulse';
    return 'bg-red-500';
};
</script>

<template>
    <AppLayout title="Manage Users">
        <template #header>
            <h2 class="font-bold text-xl text-white leading-tight">
                User Management
            </h2>
        </template>

        <div class="py-6 md:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="bg-gray-800 shadow-2xl rounded-xl border border-gray-700 overflow-hidden">
                    
                    <div class="p-5 border-b border-gray-700 flex flex-col md:flex-row justify-between md:items-center gap-4 bg-gray-900/50">
                        <div class="flex items-center gap-2">
                            <div class="bg-gray-700 p-2 rounded-lg text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500 uppercase tracking-wider font-bold">Total Users</span>
                                <span class="text-white font-bold text-lg">{{ users.length }}</span>
                            </div>
                        </div>

                        <div class="relative w-full md:w-64">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </span>
                            <input 
                                v-model="searchQuery" 
                                type="text" 
                                placeholder="Search user..." 
                                class="bg-gray-900 border border-gray-600 text-white text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full pl-10 p-2.5 shadow-inner transition-colors"
                            >
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-900">
                                <tr>
                                    <th class="px-3 sm:px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">User Details</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider hidden sm:table-cell">Role</th>
                                    <th class="px-3 sm:px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider hidden sm:table-cell">Joined</th>
                                    <th class="px-3 sm:px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700 bg-gray-800">
                                <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-700/40 transition duration-150">
                                    
                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-white">{{ user.name }}</span>
                                            <span class="text-xs text-gray-400 font-mono">{{ user.email }}</span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-700 text-gray-300 border border-gray-600">
                                            {{ user.role }}
                                        </span>
                                    </td>

                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 sm:px-3 py-1 rounded-full text-[10px] sm:text-xs font-bold border shadow-sm" :class="getStatusClasses(user.status)">
                                            <span class="w-1.5 h-1.5 rounded-full mr-2" :class="getStatusDot(user.status)"></span>
                                            {{ user.status }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 hidden sm:table-cell">
                                        {{ user.joined }}
                                    </td>

                                    <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button 
                                            @click="toggleBan(user)"
                                            class="w-20 sm:w-24 text-[10px] sm:text-xs font-bold uppercase tracking-wider py-1.5 rounded transition duration-200 border flex justify-center ml-auto"
                                            :class="user.status === 'Active' 
                                                ? 'border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white' 
                                                : 'border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-white'"
                                        >
                                            {{ user.status === 'Active' ? 'Ban' : 'Activate' }}
                                        </button>
                                    </td>
                                </tr>
                                
                                <tr v-if="filteredUsers.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">
                                        No users found matching "{{ searchQuery }}"
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="bg-gray-900/50 px-6 py-3 border-t border-gray-700 flex items-center justify-between">
                        <div class="text-xs text-gray-500">
                            Showing <span class="font-bold text-white">{{ filteredUsers.length }}</span> results
                        </div>
                        <div class="flex gap-2">
                            <button class="px-3 py-1 border border-gray-700 bg-gray-800 rounded text-xs text-gray-500 hover:text-white hover:border-gray-500 transition disabled:opacity-50 cursor-not-allowed">
                                Previous
                            </button>
                            <button class="px-3 py-1 border border-gray-700 bg-gray-800 rounded text-xs text-gray-500 hover:text-white hover:border-gray-500 transition disabled:opacity-50 cursor-not-allowed">
                                Next
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AppLayout>
</template>
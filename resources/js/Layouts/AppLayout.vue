<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>

        <Head :title="title" />
        <Banner />

        <div class="min-h-screen bg-gray-900 text-gray-100">
            <nav class="bg-gray-800 border-b border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">

                                <template v-if="$page.props.auth.user.is_admin">
                                    <NavLink :href="route('admin.dashboard')"
                                        :active="route().current('admin.dashboard')">
                                        System Health
                                    </NavLink>
                                    <NavLink :href="route('admin.users')" :active="route().current('admin.users')">
                                        Manage Users
                                    </NavLink>
                                    <NavLink :href="route('admin.pairs')" :active="route().current('admin.pairs')">
                                        Manage Pairs
                                    </NavLink>
                                </template>

                                <template v-if="!$page.props.auth.user.is_admin">
                                    <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                        Dashboard
                                    </NavLink>
                                    <NavLink :href="route('market')" :active="route().current('market')">
                                        Market AI
                                    </NavLink>
                                    <NavLink :href="route('settings')" :active="route().current('settings')">
                                        Settings
                                    </NavLink>
                                </template>

                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos"
                                            class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-teal-400 transition">
                                            <img class="h-8 w-8 rounded-full object-cover"
                                                :src="$page.props.auth.user.profile_photo_url"
                                                :alt="$page.props.auth.user.name">
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-bold rounded-md text-teal-400 bg-gray-800 hover:text-teal-200 focus:outline-none transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}
                                                <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Manage Account
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            Profile
                                        </DropdownLink>

                                        <div class="border-t border-gray-700" />

                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:bg-gray-700 focus:text-white transition duration-150 ease-in-out"
                                @click="showingNavigationDropdown = !showingNavigationDropdown">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path
                                        :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
                    class="sm:hidden bg-gray-800">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink v-if="$page.props.auth.user.is_admin" :href="route('admin.dashboard')"
                            :active="route().current('admin.dashboard')"
                            class="text-gray-300 hover:text-white hover:bg-gray-700 hover:border-teal-400 active:border-teal-400">
                            Admin Panel
                        </ResponsiveNavLink>

                        <template v-if="!$page.props.auth.user.is_admin">
                            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')"
                                class="text-gray-300 hover:text-white hover:bg-gray-700 hover:border-teal-400 active:border-teal-400">
                                Dashboard
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('market')" :active="route().current('market')"
                                class="text-gray-300 hover:text-white hover:bg-gray-700 hover:border-teal-400 active:border-teal-400">
                                Market AI
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('settings')" :active="route().current('settings')"
                                class="text-gray-300 hover:text-white hover:bg-gray-700 hover:border-teal-400 active:border-teal-400">
                                Settings
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <div class="pt-4 pb-1 border-t border-gray-700">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                <img class="h-10 w-10 rounded-full object-cover"
                                    :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            </div>
                            <div>
                                <div class="font-medium text-base text-gray-200">{{ $page.props.auth.user.name }}</div>
                                <div class="font-medium text-sm text-gray-400">{{ $page.props.auth.user.email }}</div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')"
                                class="text-gray-300 hover:text-white hover:bg-gray-700">
                                Profile
                            </ResponsiveNavLink>

                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button" class="text-gray-300 hover:text-white hover:bg-gray-700">
                                    Log Out
                                </ResponsiveNavLink>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <header v-if="$slots.header" class="bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
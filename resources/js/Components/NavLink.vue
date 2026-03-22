<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    active: Boolean,
});

const classes = computed(() => {
    return props.active
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-teal-400 text-sm font-bold leading-5 text-white focus:outline-none transition duration-150 ease-in-out shadow-[0_4px_10px_-4px_rgba(45,212,191,0.5)]'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-400 hover:text-teal-200 hover:border-teal-400/30 focus:outline-none focus:text-gray-300 transition duration-150 ease-in-out';
});
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
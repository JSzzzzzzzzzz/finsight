<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
});

const classes = computed(() => {
    return props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-teal-400 text-start text-base font-bold text-teal-400 bg-teal-900/20 focus:outline-none transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-400 hover:text-teal-200 hover:bg-gray-800 hover:border-teal-400/30 focus:outline-none transition duration-150 ease-in-out';
});
</script>

<template>
    <div v-if="as == 'button'">
        <button :class="classes" class="w-full text-start">
            <slot />
        </button>
    </div>
    
    <Link v-else :href="href" :class="classes">
        <slot />
    </Link>
</template>
<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>

    <Head title="Reset Password" />

    <div
        class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-900 text-white w-full overflow-hidden selection:bg-teal-500 selection:text-white">
        <div
            class="absolute top-0 left-0 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-teal-500/20 rounded-full blur-[100px] pointer-events-none">
        </div>
        <div
            class="absolute bottom-0 right-0 translate-x-1/2 translate-y-1/2 w-[500px] h-[500px] bg-cyan-600/20 rounded-full blur-[100px] pointer-events-none">
        </div>

        <div
            class="relative z-10 w-full sm:max-w-md mt-6 px-6 py-8 bg-gray-800 shadow-2xl overflow-hidden sm:rounded-lg border border-gray-700">
            <div class="flex justify-center mb-8">
                <Link href="/" class="flex items-center gap-2 group">
                    <div
                        class="h-10 w-10 bg-gradient-to-br from-teal-400 to-cyan-600 rounded-lg flex items-center justify-center shadow-lg group-hover:scale-110 transition">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>

                    <span class="text-2xl font-bold text-white tracking-tight">
                        FinSight<span class="text-teal-400">.AI</span>
                    </span>
                </Link>
            </div>

            <div class="mb-6 text-sm text-gray-400 leading-relaxed text-center">
                Create a new secure password for your FinSight account.
            </div>

            <form @submit.prevent="submit">
                <div>
                    <label for="email" class="block font-medium text-sm text-gray-300">
                        Email
                    </label>

                    <input id="email" v-model="form.email" type="email"
                        class="mt-1 block w-full bg-gray-900 border-gray-600 text-white rounded-md shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-500/20 focus:ring-opacity-50 transition"
                        required autofocus autocomplete="username" />

                    <p v-if="form.errors.email" class="text-sm text-red-400 mt-2">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="mt-4">
                    <label for="password" class="block font-medium text-sm text-gray-300">
                        Password
                    </label>

                    <div class="relative mt-1">
                        <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                            class="block w-full bg-gray-900 border-gray-600 text-white rounded-md shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-500/20 focus:ring-opacity-50 transition pr-12"
                            required autocomplete="new-password" />

                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-3 flex items-center text-white hover:text-teal-300 transition">
                            <svg v-if="!showPassword" class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>

                            <svg v-else class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.978 9.978 0 012.293-3.95m3.25-2.568A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.973 9.973 0 01-4.043 5.197M15 12a3 3 0 00-3-3m0 0a3 3 0 00-3 3m3-3L3 3m18 18l-6-6" />
                            </svg>
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="text-sm text-red-400 mt-2">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="mt-4">
                    <label for="password_confirmation" class="block font-medium text-sm text-gray-300">
                        Confirm Password
                    </label>

                    <div class="relative mt-1">
                        <input id="password_confirmation" v-model="form.password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            class="block w-full bg-gray-900 border-gray-600 text-white rounded-md shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-500/20 focus:ring-opacity-50 transition pr-12"
                            required autocomplete="new-password" />

                        <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute inset-y-0 right-3 flex items-center text-white hover:text-teal-300 transition">
                            <svg v-if="!showConfirmPassword" class="w-5 h-5 text-white" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>

                            <svg v-else class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.978 9.978 0 012.293-3.95m3.25-2.568A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.973 9.973 0 01-4.043 5.197M15 12a3 3 0 00-3-3m0 0a3 3 0 00-3 3m3-3L3 3m18 18l-6-6" />
                            </svg>
                        </button>
                    </div>

                    <p v-if="form.errors.password_confirmation" class="text-sm text-red-400 mt-2">
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

                <div class="flex items-center justify-between mt-6">
                    <Link :href="route('login')" class="underline text-sm text-gray-400 hover:text-teal-400 transition">
                        Back to Login
                    </Link>

                    <button
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-500 to-cyan-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:from-teal-600 hover:to-cyan-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition ease-in-out duration-150"
                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
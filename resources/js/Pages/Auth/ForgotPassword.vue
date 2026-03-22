<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password" />

    <div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-900 text-white w-full overflow-hidden selection:bg-teal-500 selection:text-white">
        
        <div class="absolute top-0 left-0 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-teal-500/20 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 translate-x-1/2 translate-y-1/2 w-[500px] h-[500px] bg-cyan-600/20 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="relative z-10 w-full sm:max-w-md mt-6 px-6 py-8 bg-gray-800 shadow-2xl overflow-hidden sm:rounded-lg border border-gray-700">
            
            <div class="flex justify-center mb-8">
                <Link href="/" class="flex items-center gap-2 group">
                    <div class="h-10 w-10 bg-gradient-to-br from-teal-400 to-cyan-600 rounded-lg flex items-center justify-center shadow-lg group-hover:scale-110 transition">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-white tracking-tight">FinSight<span class="text-teal-400">.AI</span></span>
                </Link>
            </div>

            <div class="mb-4 text-sm text-gray-400 leading-relaxed text-center">
                Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
            </div>

            <div v-if="status" class="mb-4 font-medium text-sm text-center text-green-400 bg-green-900/20 p-3 rounded-md border border-green-500/30">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div>
                    <label class="block font-medium text-sm text-gray-300">Email</label>
                    <input 
                        id="email" 
                        v-model="form.email" 
                        type="email" 
                        class="mt-1 block w-full bg-gray-900 border-gray-600 text-white rounded-md shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-500/20 focus:ring-opacity-50 transition" 
                        required 
                        autofocus 
                        autocomplete="username" 
                    />
                    <p v-if="form.errors.email" class="text-sm text-red-400 mt-2">{{ form.errors.email }}</p>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <Link :href="route('login')" class="underline text-sm text-gray-400 hover:text-teal-400 mr-4 transition">
                        Back to Login
                    </Link>

                    <button class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-500 to-cyan-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:from-teal-600 hover:to-cyan-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition ease-in-out duration-150" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Email Password Reset Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
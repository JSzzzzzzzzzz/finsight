<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

// State for toggles
const showPassword = ref(false);
const showConfirmPassword = ref(false); // New state for confirm field

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
            showPassword.value = false;
            showConfirmPassword.value = false;
        },
    });
};
</script>

<template>
    <Head title="Register" />

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

            <form @submit.prevent="submit">
                <div>
                    <label class="block font-medium text-sm text-gray-300">Name</label>
                    <input 
                        id="name" 
                        v-model="form.name" 
                        type="text" 
                        class="mt-1 block w-full bg-gray-900 border-gray-600 text-white rounded-md shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-500/20 focus:ring-opacity-50 transition" 
                        required 
                        autofocus 
                        autocomplete="name" 
                    />
                    <p v-if="form.errors.name" class="text-sm text-red-400 mt-2">{{ form.errors.name }}</p>
                </div>

                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-300">Email</label>
                    <input 
                        id="email" 
                        v-model="form.email" 
                        type="email" 
                        class="mt-1 block w-full bg-gray-900 border-gray-600 text-white rounded-md shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-500/20 focus:ring-opacity-50 transition" 
                        required 
                        autocomplete="username" 
                    />
                    <p v-if="form.errors.email" class="text-sm text-red-400 mt-2">{{ form.errors.email }}</p>
                </div>

                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-300">Password</label>
                    <div class="relative mt-1">
                        <input 
                            id="password" 
                            v-model="form.password" 
                            :type="showPassword ? 'text' : 'password'" 
                            class="block w-full bg-gray-900 border-gray-600 text-white rounded-md shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-500/20 focus:ring-opacity-50 transition pr-10" 
                            required 
                            autocomplete="new-password" 
                        />
                        <button 
                            type="button" 
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-white transition cursor-pointer focus:outline-none"
                        >
                            <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="text-sm text-red-400 mt-2">{{ form.errors.password }}</p>
                </div>

                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-300">Confirm Password</label>
                    <div class="relative mt-1">
                        <input 
                            id="password_confirmation" 
                            v-model="form.password_confirmation" 
                            :type="showConfirmPassword ? 'text' : 'password'" 
                            class="block w-full bg-gray-900 border-gray-600 text-white rounded-md shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-500/20 focus:ring-opacity-50 transition pr-10" 
                            required 
                            autocomplete="new-password" 
                        />
                        <button 
                            type="button" 
                            @click="showConfirmPassword = !showConfirmPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-white transition cursor-pointer focus:outline-none"
                        >
                            <svg v-if="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    <p v-if="form.errors.password_confirmation" class="text-sm text-red-400 mt-2">{{ form.errors.password_confirmation }}</p>
                </div>

                <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                    <label class="flex items-center">
                        <Checkbox v-model:checked="form.terms" name="terms" class="text-teal-500 focus:ring-teal-500 bg-gray-900 border-gray-600" />
                        <div class="ms-2 text-sm text-gray-400">
                            I agree to the <a target="_blank" :href="route('terms.show')" class="underline text-sm text-gray-400 hover:text-teal-400 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 focus:ring-offset-gray-800">Terms of Service</a> and <a target="_blank" :href="route('policy.show')" class="underline text-sm text-gray-400 hover:text-teal-400 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 focus:ring-offset-gray-800">Privacy Policy</a>
                        </div>
                    </label>
                    <p v-if="form.errors.terms" class="text-sm text-red-400 mt-2">{{ form.errors.terms }}</p>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <Link :href="route('login')" class="underline text-sm text-gray-400 hover:text-teal-400 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 focus:ring-offset-gray-800">
                        Already registered?
                    </Link>

                    <button class="ms-4 inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-500 to-cyan-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:from-teal-600 hover:to-cyan-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition ease-in-out duration-150" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
/* Remove default browser eye icon */
input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear {
    display: none;
}
</style>
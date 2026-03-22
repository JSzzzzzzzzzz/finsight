<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="Welcome to FinSight" />

    <div class="relative min-h-screen bg-gray-900 text-white overflow-hidden flex flex-col selection:bg-teal-500 selection:text-white">
        
        <div class="absolute top-0 left-0 -translate-x-1/4 -translate-y-1/4 w-[800px] h-[800px] bg-teal-500/30 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 translate-x-1/4 translate-y-1/4 w-[800px] h-[800px] bg-cyan-600/30 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[1000px] h-[1000px] bg-emerald-500/10 rounded-full blur-[150px] pointer-events-none"></div>

        <div v-if="canLogin" class="p-6 text-end relative z-10">
            <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="font-semibold text-gray-300 hover:text-white border border-transparent hover:border-teal-500/50 px-4 py-2 rounded-full transition">
                Dashboard
            </Link>

            <template v-else>
                <Link :href="route('login')" class="font-semibold text-gray-300 hover:text-white border border-transparent hover:border-teal-500/50 px-4 py-2 rounded-full transition">
                    Log in
                </Link>

                <Link v-if="canRegister" :href="route('register')" class="ms-4 font-semibold text-white bg-teal-600/20 hover:bg-teal-600/40 border border-teal-500/50 px-4 py-2 rounded-full transition">
                    Register
                </Link>
            </template>
        </div>

        <main class="flex-grow flex flex-col items-center justify-center w-full max-w-7xl mx-auto px-4 lg:px-8 z-10 py-8 lg:py-12">
            
            <div class="text-center space-y-6 lg:space-y-8 max-w-3xl mx-auto mb-10 lg:mb-20">
                
                <div class="mx-auto h-16 w-16 lg:h-24 lg:w-24 bg-gradient-to-br from-teal-400 to-cyan-600 rounded-2xl lg:rounded-3xl flex items-center justify-center shadow-[0_0_50px_rgba(20,184,166,0.5)] mb-6 animate-pulse">
                    <svg class="w-8 h-8 lg:w-14 lg:h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>

                <h1 class="text-4xl lg:text-7xl font-black tracking-tight text-white drop-shadow-2xl">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-300 via-cyan-400 to-teal-300">FinSight</span>.AI
                </h1>

                <p class="text-sm lg:text-2xl text-gray-300 leading-relaxed px-4">
                    Institutional-grade <span class="text-teal-400 font-bold">Crypto Intelligence</span>. 
                    <span class="hidden lg:inline"><br />Harness the power of AI to predict market volatility.</span>
                </p>

                <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                    <Link :href="route('register')" class="px-8 py-3 bg-gradient-to-r from-teal-500 to-cyan-600 rounded-full font-bold text-white text-base lg:text-lg shadow-[0_0_20px_rgba(20,184,166,0.4)] hover:shadow-[0_0_40px_rgba(20,184,166,0.6)] hover:scale-105 transition transform">
                        Start Free Analysis
                    </Link>
                    <Link :href="route('login')" class="px-8 py-3 bg-gray-800 border border-gray-600 rounded-full font-bold text-gray-300 text-base lg:text-lg hover:bg-gray-700 hover:text-white hover:border-gray-500 transition">
                        View Live Demo
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6 w-full">
                
                <div class="group p-4 lg:p-8 bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-xl lg:rounded-2xl hover:bg-gray-800/60 hover:border-teal-500/30 transition duration-300">
                    <div class="h-8 w-8 lg:h-12 lg:w-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg lg:rounded-xl flex items-center justify-center mb-3 lg:mb-4 shadow-lg">
                        <span class="text-lg lg:text-2xl">🧠</span>
                    </div>
                    <h2 class="text-sm lg:text-xl font-bold text-white mb-1 lg:mb-2">AI Sentiment</h2>
                    <p class="text-xs lg:text-base text-gray-400 leading-tight lg:leading-normal">
                        FinBERT engine analyzes 50k+ sources instantly.
                    </p>
                </div>

                <div class="group p-4 lg:p-8 bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-xl lg:rounded-2xl hover:bg-gray-800/60 hover:border-teal-500/30 transition duration-300">
                    <div class="h-8 w-8 lg:h-12 lg:w-12 bg-gradient-to-br from-red-500 to-orange-600 rounded-lg lg:rounded-xl flex items-center justify-center mb-3 lg:mb-4 shadow-lg">
                        <span class="text-lg lg:text-2xl">🛡️</span>
                    </div>
                    <h2 class="text-sm lg:text-xl font-bold text-white mb-1 lg:mb-2">Risk Alert</h2>
                    <p class="text-xs lg:text-base text-gray-400 leading-tight lg:leading-normal">
                        Real-time volatility protection for your portfolio.
                    </p>
                </div>

                <div class="group p-4 lg:p-8 bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-xl lg:rounded-2xl hover:bg-gray-800/60 hover:border-teal-500/30 transition duration-300">
                    <div class="h-8 w-8 lg:h-12 lg:w-12 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-lg lg:rounded-xl flex items-center justify-center mb-3 lg:mb-4 shadow-lg">
                        <span class="text-lg lg:text-2xl">📊</span>
                    </div>
                    <h2 class="text-sm lg:text-xl font-bold text-white mb-1 lg:mb-2">Portfolio</h2>
                    <p class="text-xs lg:text-base text-gray-400 leading-tight lg:leading-normal">
                        Live sync with Binance & Luno API keys.
                    </p>
                </div>

                <div class="group p-4 lg:p-8 bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-xl lg:rounded-2xl hover:bg-gray-800/60 hover:border-teal-500/30 transition duration-300">
                    <div class="h-8 w-8 lg:h-12 lg:w-12 bg-gradient-to-br from-yellow-500 to-amber-600 rounded-lg lg:rounded-xl flex items-center justify-center mb-3 lg:mb-4 shadow-lg">
                        <span class="text-lg lg:text-2xl">⚡</span>
                    </div>
                    <h2 class="text-sm lg:text-xl font-bold text-white mb-1 lg:mb-2">Scanner</h2>
                    <p class="text-xs lg:text-base text-gray-400 leading-tight lg:leading-normal">
                        Top movers & instant AI deep-dive analysis.
                    </p>
                </div>
            </div>

        </main>

        <footer class="py-6 text-center text-xs lg:text-sm text-gray-500 border-t border-gray-800/50 bg-gray-900/80 backdrop-blur relative z-10">
            &copy; 2026 FinSight Intelligence. Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
        </footer>
    </div>
</template>
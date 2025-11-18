<template>
    <div class="min-h-screen bg-gradient-to-br from-indigo-600 to-purple-700 flex items-center justify-center px-4">
        <div class="max-w-4xl w-full">
            <div class="text-center">
                <h1 class="text-6xl font-bold text-white mb-4">Boxibox</h1>
                <p class="text-2xl text-indigo-100 mb-8">
                    Plateforme SaaS de gestion de self-stockage
                </p>
                <p class="text-lg text-indigo-200 mb-12 max-w-2xl mx-auto">
                    Solution complète multi-tenant pour gérer vos centres de stockage,
                    vos clients, contrats et facturation en toute simplicité.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                    <div class="bg-white/10 backdrop-blur-lg rounded-lg p-6">
                        <div class="text-4xl mb-4">📦</div>
                        <h3 class="text-xl font-semibold text-white mb-2">Gestion des Box</h3>
                        <p class="text-indigo-100">Gérez facilement votre inventaire de boxes et leur disponibilité</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-lg rounded-lg p-6">
                        <div class="text-4xl mb-4">👥</div>
                        <h3 class="text-xl font-semibold text-white mb-2">CRM Client</h3>
                        <p class="text-indigo-100">Suivez vos clients et leurs contrats en temps réel</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-lg rounded-lg p-6">
                        <div class="text-4xl mb-4">💰</div>
                        <h3 class="text-xl font-semibold text-white mb-2">Facturation</h3>
                        <p class="text-indigo-100">Facturation automatique et gestion des paiements</p>
                    </div>
                </div>

                <div class="space-x-4">
                    <Link
                        :href="route('dashboard')"
                        class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-white hover:bg-indigo-50 transition"
                    >
                        Accéder au Dashboard
                        <svg class="ml-2 -mr-1 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                </div>

                <div class="mt-16 text-indigo-200 text-sm">
                    <p>Développé avec Laravel 11 + Vue.js 3 + Tailwind CSS</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

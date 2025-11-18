<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <Link href="/" class="text-2xl font-bold text-indigo-600">
                                Boxibox
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden sm:ml-10 sm:flex sm:space-x-8">
                            <Link
                                :href="route('dashboard')"
                                :class="route().current('dashboard') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            >
                                Dashboard
                            </Link>
                            <Link
                                :href="route('plan.index')"
                                :class="route().current('plan.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            >
                                Plan
                            </Link>
                            <Link
                                :href="route('sites.index')"
                                :class="route().current('sites.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            >
                                Sites
                            </Link>
                            <Link
                                :href="route('boxes.index')"
                                :class="route().current('boxes.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            >
                                Boxes
                            </Link>
                            <Link
                                :href="route('customers.index')"
                                :class="route().current('customers.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            >
                                Clients
                            </Link>
                            <Link
                                :href="route('contracts.index')"
                                :class="route().current('contracts.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            >
                                Contrats
                            </Link>
                            <Link
                                :href="route('invoices.index')"
                                :class="route().current('invoices.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                            >
                                Factures
                            </Link>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center">
                        <div class="ml-3 relative">
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-700">Admin</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

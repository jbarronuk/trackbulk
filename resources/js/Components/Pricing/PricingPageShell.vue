<script setup>
import Header from '@/Components/Header.vue'
import BackgroundPattern from '@/Components/Pricing/BackgroundPattern.vue'

defineProps({
    canLogin: { type: Boolean, default: false },
    canRegister: { type: Boolean, default: false },
})

const currentYear = new Date().getFullYear()
</script>

<template>
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <BackgroundPattern />

        <div
            class="relative flex min-h-screen flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white"
        >
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <Header :can-login="canLogin" :can-register="canRegister" />

                <main class="mt-6">
                    <slot />
                </main>

                <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                    TrackBulk &copy; {{ currentYear }}
                </footer>
            </div>
        </div>
    </div>
</template>

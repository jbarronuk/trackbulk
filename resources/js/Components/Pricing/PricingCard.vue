<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    features: { type: Array, default: () => [] },
    price: { type: Number, default: null },
    period: { type: String, default: 'month' },
})
</script>

<template>
    <div
        class="flex flex-col gap-6 rounded-lg bg-white p-6 shadow-lg ring-1 ring-white/[0.05] transition hover:ring-black/20 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:ring-zinc-700"
    >
        <div>
            <h3 class="text-2xl font-semibold text-black dark:text-white">{{ title }}</h3>
            <p class="mt-2 text-sm text-zinc-500">{{ description }}</p>
        </div>

        <div class="flex items-center">
            <template v-if="price !== null">
                <span class="text-4xl font-bold text-black dark:text-white">&pound;{{ price }}</span>
                <span class="ml-2 text-sm text-zinc-500">/ {{ period }}</span>
            </template>
            <span v-else class="text-4xl font-bold text-black dark:text-white">Custom</span>
        </div>

        <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
            <li v-for="feature in features" :key="feature" class="flex items-start gap-2">
                <span aria-hidden="true">✅</span>
                <span>{{ feature }}</span>
            </li>
        </ul>

        <slot name="cta" />
    </div>
</template>

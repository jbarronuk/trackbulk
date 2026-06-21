<script setup>
const isYearly = defineModel({ type: Boolean, default: false })
</script>

<template>
    <div class="mb-6 flex justify-center">
        <div class="inline-flex rounded-md bg-gray-200 p-1 dark:bg-zinc-800">
            <button
                type="button"
                :class="
                    isYearly
                        ? 'text-gray-500 dark:text-gray-400'
                        : 'bg-white text-black dark:bg-zinc-700 dark:text-white'
                "
                class="rounded-md px-4 py-2 text-sm font-medium"
                @click="isYearly = false"
            >
                Monthly
            </button>
            <button
                type="button"
                :class="
                    isYearly
                        ? 'bg-white text-black dark:bg-zinc-700 dark:text-white'
                        : 'text-gray-500 dark:text-gray-400'
                "
                class="rounded-md px-4 py-2 text-sm font-medium"
                @click="isYearly = true"
            >
                Yearly (Save 16.5%)
            </button>
        </div>
    </div>
</template>

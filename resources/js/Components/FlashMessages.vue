<script setup>
defineProps({
    flash: { type: Object, default: () => ({}) },
})
</script>

<template>
    <div
        v-if="flash?.success"
        class="mb-4 rounded-lg bg-green-100 p-4 text-green-700 dark:bg-green-900/40 dark:text-green-300"
    >
        {{ flash.success }}
    </div>
    <div
        v-if="flash?.error"
        class="mb-4 rounded-lg bg-red-100 p-4 text-red-700 dark:bg-red-900/40 dark:text-red-300"
    >
        {{ flash.error }}
    </div>
</template>

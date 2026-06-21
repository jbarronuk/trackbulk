<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    label: { type: String, required: true },
    variant: { type: String, default: 'primary' }, // 'primary' | 'secondary'
    href: { type: String, default: null },
})

defineEmits(['select'])

const classes = computed(() => {
    const base = 'mt-4 block w-full rounded-md px-4 py-2 text-center transition'
    return props.variant === 'secondary'
        ? `${base} bg-gray-200 text-black hover:bg-gray-300 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700`
        : `${base} bg-[#FF2D20] text-white hover:bg-[#e0261c]`
})
</script>

<template>
    <Link v-if="href" :href="href" :class="classes">{{ label }}</Link>
    <button v-else type="button" :class="classes" @click="$emit('select')">{{ label }}</button>
</template>

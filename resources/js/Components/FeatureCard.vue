<script setup>
defineProps({
    title: { type: String, required: true },
    body: { type: String, required: true },
    iconPath: { type: String, required: true },
    iconViewBox: { type: String, default: '0 0 512 512' },
})
</script>

<template>
    <div
        class="flex items-start gap-4 rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:ring-black/20 lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:ring-zinc-700"
    >
        <div
            class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16"
        >
            <svg
                class="size-5 sm:size-6"
                xmlns="http://www.w3.org/2000/svg"
                :viewBox="iconViewBox"
                aria-hidden="true"
            >
                <path :d="iconPath" />
            </svg>
        </div>

        <div class="pt-3 sm:pt-5">
            <h2 class="text-xl font-semibold text-black dark:text-white">{{ title }}</h2>
            <p class="mt-4 text-sm/relaxed">{{ body }}</p>
        </div>
    </div>
</template>

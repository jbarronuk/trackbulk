<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import MarketingLayout from '@/Layouts/MarketingLayout.vue'

defineProps({
    canLogin: { type: Boolean, default: false },
    canRegister: { type: Boolean, default: false },
})

const sent = ref(false)

const form = useForm({
    name: '',
    email: '',
    message: '',
})

const submit = () => {
    form.post(route('contact.submit'), {
        preserveScroll: true,
        onSuccess: () => {
            sent.value = true
            form.reset()
        },
    })
}
</script>

<template>
    <Head title="Contact" />

    <MarketingLayout :can-login="canLogin" :can-register="canRegister">
        <section class="mt-12 max-w-xl">
            <h2 class="mb-4 text-2xl font-bold text-black dark:text-white">Contact Us</h2>

            <p
                v-if="sent"
                class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-700 dark:border-green-900 dark:bg-green-950 dark:text-green-300"
            >
                Thanks! Your message has been sent.
            </p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="name" class="block font-semibold text-black dark:text-white">Name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full rounded border p-2 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label for="email" class="block font-semibold text-black dark:text-white">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full rounded border p-2 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div>
                    <label for="message" class="block font-semibold text-black dark:text-white">Message</label>
                    <textarea
                        id="message"
                        v-model="form.message"
                        rows="4"
                        required
                        class="w-full rounded border p-2 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                    ></textarea>
                    <p v-if="form.errors.message" class="mt-1 text-sm text-red-500">
                        {{ form.errors.message }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-md bg-[#FF2D20] px-4 py-2 text-white transition hover:bg-[#e0261c] disabled:opacity-50"
                >
                    {{ form.processing ? 'Sending…' : 'Send' }}
                </button>
            </form>
        </section>
    </MarketingLayout>
</template>

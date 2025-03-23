<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import { useForm } from "@inertiajs/vue3";

defineProps({
    canLogin: {},
    canRegister: {},
});
const form = useForm({
    name: "",
    email: "",
    message: "",
});

const submit = () => {
    form.post(route("contact.submit"), {
        onSuccess: () => {
            alert("Your message has been sent!");
            form.reset();
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
};
</script>
<template>
    <Head title="Plans" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <svg id="background" class="absolute -left-20 top-0 max-w-[877px]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 877 968"><g clip-path="url(#a)"><circle cx="391" cy="391" r="390.5" stroke="#ccc" transform="matrix(-1 0 0 1 416 -56)"/><circle cx="468" cy="468" r="467.5" stroke="#ccc" opacity=".3" transform="matrix(-1 0 0 1 493 -133)"/><circle cx="558" cy="558" r="557.5" stroke="#ccc" opacity=".1" transform="matrix(-1 0 0 1 583 -223)"/><g filter="url(#b)"> <ellipse cx="583" cy="229.5" fill="#ccc" rx="583" ry="229.5" transform="matrix(-1 0 0 1 621 -9)"/></g><g filter="url(#c)"><ellipse cx="262" cy="184.5" fill="#fff" rx="262" ry="184.5" transform="matrix(-1 0 0 1 99 42)"/></g></g><defs><filter id="b" width="1614" height="907" x="-769" y="-233" color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feBlend in="SourceGraphic" in2="BackgroundImageFix" result="shape"/><feGaussianBlur result="effect1_foregroundBlur_3089_39042" stdDeviation="112"/></filter><filter id="c" width="972" height="817" x="-649" y="-182" color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse"><feFlood flood-opacity="0" result="BackgroundImageFix"/><feBlend in="SourceGraphic" in2="BackgroundImageFix" result="shape"/><feGaussianBlur result="effect1_foregroundBlur_3089_39042" stdDeviation="112"/></filter><clipPath id="a"><path fill="#fff" d="M877 0H0v968h877z"/></clipPath></defs></svg>
        <div
            class="relative flex min-h-screen flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white"
        >
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <Header
                    canLogin="canLogin"
                    canRegister="canRegister"
                ></Header>

                <main class="mt-6">
                	<section class="mt-12">
                        <h2 class="text-2xl font-bold mb-4">Contact Us</h2>
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <label class="block font-semibold">Name</label>
                                <input v-model="form.name" type="text" class="w-full p-2 border rounded" required>
                                <div v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</div>
                            </div>

                            <div class="mb-4">
                                <label class="block font-semibold">Email</label>
                                <input v-model="form.email" type="email" class="w-full p-2 border rounded" required>
                                <div v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</div>
                            </div>

                            <div class="mb-4">
                                <label class="block font-semibold">Message</label>
                                <textarea v-model="form.message" rows="4" class="w-full p-2 border rounded" required></textarea>
                                <div v-if="form.errors.message" class="text-red-500 text-sm">{{ form.errors.message }}</div>
                            </div>

                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-500 text-white rounded">
                                Send
                            </button>
                        </form>
                    </section>
                </main>

                <footer
                    class="py-16 text-center text-sm text-black dark:text-white/70"
                >
                    TrackBulk &copy {{ new Date().getFullYear() }}
                </footer>
            </div>
        </div>
    </div>
</template>

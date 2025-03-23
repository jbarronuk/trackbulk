<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const packages_remaining = usePage().props.packages_remaining;

const form = useForm({
    packages_remaining: packages_remaining,
});
console.log(form.packages_remaining);
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Packages Remaining This Month
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                The number of packages remaining resets on the first of each month
            </p>
        </header>

        <form
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="packages_remaining" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block"
                    readonly
                    autofocus
                    v-model="form.packages_remaining"
                />
            </div>
        </form>
    </section>
</template>

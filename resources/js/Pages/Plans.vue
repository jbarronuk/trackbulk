<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Header from '@/Components/Header.vue';
import { ref } from 'vue'

defineProps({
    canLogin: {},
    canRegister: {},
});

const isYearly = ref(false)

const plans = [
  {
    title: 'Basic',
    monthlyPrice: 4.99,
    yearlyPrice: 49.99,
    description: 'Basic features for getting started.',
    features: ['1000 packages tracked p/m', 'Basic Support', 'Daily queries'],
    buttonText: 'Get Started'
  },
  {
    title: 'Pro',
    monthlyPrice: 9.99,
    yearlyPrice: 99.99,
    description: 'More features for growing businesses.',
    features: ['3000 packages tracked p/m', 'Basic Support', 'Daily queries'],
    buttonText: 'Get Pro'
  },
  {
    title: 'Enterprise',
    description: 'Best for large-scale projects.',
    features: ['Unlimited packages tracked', 'Priority Support', 'Hourly queries'],
    buttonText: 'Contact Sales'
  }
]

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
    <!-- Toggle Buttons -->
    <div class="flex justify-center mb-6">
      <div class="inline-flex rounded-md bg-gray-200 p-1 dark:bg-zinc-800">
        <button
          :class="[
            isYearly ? 'text-gray-500 dark:text-gray-400' : 'text-black bg-white dark:text-white dark:bg-zinc-700',
          ]"
          class="px-4 py-2 text-sm font-medium rounded-md"
          @click="isYearly = false"
        >
          Monthly
        </button>
        <button
          :class="[
            isYearly ? 'text-black bg-white dark:text-white dark:bg-zinc-700' : 'text-gray-500 dark:text-gray-400',
          ]"
          class="px-4 py-2 text-sm font-medium rounded-md"
          @click="isYearly = true"
        >
          Yearly (Save 16.5%)
        </button>
      </div>
    </div>

    <!-- Pricing Plans -->
    <div class="grid gap-6 lg:grid-cols-4">
      <!-- Free Plan -->
      <div
        class="flex flex-col gap-6 rounded-lg bg-white p-6 shadow-lg ring-1 ring-white/[0.05] transition hover:ring-black/20 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:ring-zinc-700"
      >
        <div>
          <h3 class="text-2xl font-semibold text-black dark:text-white">Free</h3>
          <p class="mt-2 text-sm text-zinc-500">Get started with essential features.</p>
        </div>
        <div class="flex items-center">
          <span class="text-4xl font-bold text-black dark:text-white">&pound;0</span>
          <span class="ml-2 text-sm text-zinc-500">/month</span>
        </div>
        <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
          <li>✅ 20 packages tracked p/m</li>
          <li>✅ Limited Support</li>
          <li>✅ Daily queries</li>
        </ul>
        <button
          class="mt-4 w-full rounded-md bg-gray-200 px-4 py-2 text-black transition hover:bg-gray-300 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700"
        >
          Sign Up for Free
        </button>
      </div>

      <!-- Paid Plans -->
      <div
        v-for="(plan, index) in plans"
        :key="index"
        class="flex flex-col gap-6 rounded-lg bg-white p-6 shadow-lg ring-1 ring-white/[0.05] transition hover:ring-black/20 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:ring-zinc-700"
      >
        <div>
          <h3 class="text-2xl font-semibold text-black dark:text-white">{{ plan.title }}</h3>
          <p class="mt-2 text-sm text-zinc-500">{{ plan.description }}</p>
        </div>
        <div class="flex items-center">
          <span class="text-4xl font-bold text-black dark:text-white">
            &pound;{{ isYearly ? plan.yearlyPrice : plan.monthlyPrice }}
          </span>
          <span class="ml-2 text-sm text-zinc-500">/ {{ isYearly ? 'year' : 'month' }}</span>
        </div>
        <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
          <li v-for="(feature, i) in plan.features" :key="i">✅ {{ feature }}</li>
        </ul>
        <button
          class="mt-4 w-full rounded-md bg-[#FF2D20] px-4 py-2 text-white transition hover:bg-[#e0261c]"
        >
          {{ plan.buttonText }}
        </button>
      </div>
    </div>
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

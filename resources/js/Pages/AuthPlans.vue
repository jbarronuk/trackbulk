<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import MarketingLayout from '@/Layouts/MarketingLayout.vue'
import BillingToggle from '@/Components/Pricing/BillingToggle.vue'
import PricingCard from '@/Components/Pricing/PricingCard.vue'
import PlanCta from '@/Components/Pricing/PlanCta.vue'
import { pricingPlans, priceForPeriod } from '@/Data/pricingPlans'

const props = defineProps({
    canLogin: { type: Boolean, default: false },
    canRegister: { type: Boolean, default: false },
    stripe_prices: {
        type: Object,
        default: () => ({}),
    },
})

const isYearly = ref(false)

const subscribe = (priceId) => {
    window.location.href = route('billing.checkout', priceId)
}

const callsToAction = computed(() => ({
    free: { label: 'Sign Up for Free', href: route('register'), variant: 'secondary' },
    basic: {
        label: 'Get Started',
        variant: 'primary',
        priceIds: props.stripe_prices.basic,
    },
    pro: {
        label: 'Go Pro',
        variant: 'primary',
        priceIds: props.stripe_prices.pro,
    },
    enterprise: { label: 'Contact Sales', href: route('site.contact'), variant: 'primary' },
}))

const plans = computed(() =>
    pricingPlans.map((plan) => ({ ...plan, cta: callsToAction.value[plan.id] }))
)

const priceIdFor = (cta) => (isYearly.value ? cta.priceIds.yearly : cta.priceIds.monthly)
</script>

<template>
    <Head title="Plans" />

    <MarketingLayout :can-login="canLogin" :can-register="canRegister">
        <section class="mt-12">
            <BillingToggle v-model="isYearly" />

            <div class="grid gap-6 lg:grid-cols-4">
                <PricingCard
                    v-for="plan in plans"
                    :key="plan.id"
                    :title="plan.title"
                    :description="plan.description"
                    :features="plan.features"
                    :price="priceForPeriod(plan, isYearly)"
                    :period="isYearly ? 'year' : 'month'"
                >
                    <template #cta>
                        <PlanCta
                            :label="plan.cta.label"
                            :href="plan.cta.href ?? null"
                            :variant="plan.cta.variant"
                            @select="subscribe(priceIdFor(plan.cta))"
                        />
                    </template>
                </PricingCard>
            </div>
        </section>
    </MarketingLayout>
</template>

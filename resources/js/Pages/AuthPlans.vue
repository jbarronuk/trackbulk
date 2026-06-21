<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import PricingPageShell from '@/Components/Pricing/PricingPageShell.vue'
import BillingToggle from '@/Components/Pricing/BillingToggle.vue'
import PricingCard from '@/Components/Pricing/PricingCard.vue'
import PlanCta from '@/Components/Pricing/PlanCta.vue'
import { pricingPlans, priceForPeriod } from '@/Data/pricingPlans'

defineProps({
    canLogin: { type: Boolean, default: false },
    canRegister: { type: Boolean, default: false },
})

const isYearly = ref(false)

const subscribe = (priceId) => {
    window.location.href = route('billing.checkout', priceId)
}

const callsToAction = {
    free: { label: 'Sign Up for Free', href: route('register'), variant: 'secondary' },
    basic: {
        label: 'Get Started',
        variant: 'primary',
        priceIds: {
            monthly: 'price_1R2YyNFbgslx19wSfQxC3p2w',
            yearly: 'price_1R2Z0sFbgslx19wSxoT0X5w0',
        },
    },
    pro: {
        label: 'Go Pro',
        variant: 'primary',
        priceIds: {
            monthly: 'price_1R2Yx6Fbgslx19wSL808seTg',
            yearly: 'price_1R2Z0BFbgslx19wSdUqMrgvc',
        },
    },
    enterprise: { label: 'Contact Sales', href: route('site.contact'), variant: 'primary' },
}

const plans = pricingPlans.map((plan) => ({ ...plan, cta: callsToAction[plan.id] }))

const priceIdFor = (cta) => (isYearly.value ? cta.priceIds.yearly : cta.priceIds.monthly)
</script>

<template>
    <Head title="Plans" />

    <PricingPageShell :can-login="canLogin" :can-register="canRegister">
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
    </PricingPageShell>
</template>

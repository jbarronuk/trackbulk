<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import MarketingLayout from '@/Layouts/MarketingLayout.vue'
import BillingToggle from '@/Components/Pricing/BillingToggle.vue'
import PricingCard from '@/Components/Pricing/PricingCard.vue'
import PlanCta from '@/Components/Pricing/PlanCta.vue'
import { pricingPlans, priceForPeriod } from '@/Data/pricingPlans'

defineProps({
    canLogin: { type: Boolean, default: false },
    canRegister: { type: Boolean, default: false },
})

const isYearly = ref(false)

const callsToAction = {
    free: { label: 'Sign Up for Free', href: route('register'), variant: 'secondary' },
    basic: { label: 'Register Now', href: route('register'), variant: 'primary' },
    pro: { label: 'Register Now', href: route('register'), variant: 'primary' },
    enterprise: { label: 'Contact Sales', href: route('site.contact'), variant: 'primary' },
}

const plans = pricingPlans.map((plan) => ({ ...plan, cta: callsToAction[plan.id] }))
</script>

<template>
    <Head title="Plans" />
>
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
                            :href="plan.cta.href"
                            :variant="plan.cta.variant"
                        />
                    </template>
                </PricingCard>
            </div>
        </section>
    </MarketingLayout>
</template>

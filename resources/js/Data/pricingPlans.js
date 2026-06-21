export const pricingPlans = [
    {
        id: 'free',
        title: 'Free',
        description: 'Get started with essential features.',
        monthlyPrice: 0,
        yearlyPrice: 0,
        features: [
            '20 packages tracked p/m',
            'Limited Support',
            'Daily Queries',
            'Royal Mail Only',
        ],
    },
    {
        id: 'basic',
        title: 'Basic',
        description: 'Basic features for getting started.',
        monthlyPrice: 4.99,
        yearlyPrice: 49.99,
        features: [
            '1000 packages tracked p/m',
            'Basic Support',
            'Daily Queries',
            'Royal Mail Only',
        ],
    },
    {
        id: 'pro',
        title: 'Pro',
        description: 'More features for growing businesses.',
        monthlyPrice: 9.99,
        yearlyPrice: 99.99,
        features: [
            '3000 packages tracked p/m',
            'Basic Support',
            'Daily Queries',
            'Royal Mail Only',
        ],
    },
    {
        id: 'enterprise',
        title: 'Enterprise',
        description: 'Best for large-scale projects.',
        monthlyPrice: null,
        yearlyPrice: null,
        features: [
            'Unlimited packages tracked',
            'Priority Support',
            'Hourly Queries',
            'All Carriers',
        ],
    },
]

export const priceForPeriod = (plan, isYearly) =>
    plan.monthlyPrice === null ? null : isYearly ? plan.yearlyPrice : plan.monthlyPrice

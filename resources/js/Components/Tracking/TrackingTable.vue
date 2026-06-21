<script setup>
defineProps({
    batches: { type: Array, default: () => [] },
    statuses: { type: [Array, Object], default: () => ({}) },
    selectableBatches: { type: Boolean, default: false },
})

defineEmits(['batch-click'])

const cellClass = 'bg-white px-6 py-4 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-200'
const headClass = 'px-6 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300'
</script>

<template>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border dark:divide-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th :class="headClass">Tracking Number</th>
                    <th :class="headClass">Status</th>
                    <th :class="headClass">Detail</th>
                    <th class="px-6 py-3 text-right">
                        <slot name="toolbar" />
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-if="batches.length === 0">
                    <td
                        colspan="4"
                        class="px-3 py-4 text-center text-sm text-gray-600 dark:text-gray-300"
                    >
                        <slot name="empty">Nothing to show yet…</slot>
                    </td>
                </tr>

                <template v-for="batch in batches" :key="batch.id">
                    <tr>
                        <td
                            colspan="4"
                            class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300"
                            :class="{ 'cursor-pointer': selectableBatches }"
                            @click="$emit('batch-click', batch)"
                        >
                            Batch: {{ batch.formatted_created_at }}
                        </td>
                    </tr>

                    <tr v-for="track in batch.tracking" :key="track.id">
                        <td :class="cellClass">{{ track.number }}</td>
                        <td :class="cellClass">{{ statuses[track.status] }}</td>
                        <td :class="cellClass">{{ track.summary_response }}</td>
                        <td :class="cellClass" class="text-right">
                            <slot name="row-action" :track="track" />
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import TrackingTable from '@/Components/Tracking/TrackingTable.vue'
import { useTrackingPolling } from '@/Composables/useTrackingPolling'
import { startOfToday, endOfToday } from '@/Utils/dateRange'

const props = defineProps({
    statuses: { type: [Array, Object], default: () => ({}) },
    batches: { type: Array, default: () => [] },
    flash: { type: Object, default: () => ({}) },
})

const trackingData = ref([...props.batches])
const flash = ref(props.flash)
const newTracking = ref({ number: '' })
const deleting = ref([]) // ids currently being deleted

watch(
    () => props.batches,
    (batches) => {
        trackingData.value = [...batches]
    }
)

const createTracking = async () => {
    try {
        const { data } = await axios.post('/tracking', newTracking.value)
        flash.value = data.flash
        trackingData.value = data.tracking
        newTracking.value = { number: '' }
    } catch (error) {
        console.error('Error creating tracking:', error)
    }
}

const deleteTracking = async (id) => {
    if (!confirm('Are you sure you want to delete this tracking number?')) return

    deleting.value.push(id)
    try {
        await axios.delete(`/tracking/${id}`)
        trackingData.value = trackingData.value.map((batch) => ({
            ...batch,
            tracking: batch.tracking.filter((track) => track.id !== id),
        }))
    } catch (error) {
        console.error('Error deleting tracking:', error)
    } finally {
        deleting.value = deleting.value.filter((trackId) => trackId !== id)
    }
}

useTrackingPolling({
    format: 'time',
    range: () => ({ start: startOfToday(), end: endOfToday() }),
    onData: (data) => {
        trackingData.value = data
    },
})
</script>

<template>
    <Head title="Tracking" />

    <AuthenticatedLayout>
        <div class="container mx-auto max-w-3xl p-6">
            <FlashMessages :flash="flash" />

            <h1 class="mb-4 text-2xl font-bold">Tracking Numbers</h1>

            <form @submit.prevent="createTracking" class="mb-6 space-y-4">
                <div>
                    <label
                        for="number"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                        Tracking Numbers (one per line, max 50)
                    </label>
                    <textarea
                        id="number"
                        v-model="newTracking.number"
                        rows="5"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    ></textarea>
                </div>
                <button
                    type="submit"
                    class="w-full rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                >
                    Track
                </button>
            </form>

            <TrackingTable :batches="trackingData" :statuses="statuses">
                <template #toolbar>
                    <a
                        :href="
                            route('export.tracking', {
                                type: 'daterange',
                                start: startOfToday(),
                                end: endOfToday(),
                            })
                        "
                        class="btn"
                        aria-label="Export today's tracking"
                    >
                        <font-awesome-icon
                            :icon="['fas', 'file-excel']"
                            class="inline-block h-6 w-6 fill-current text-gray-300 hover:text-green-500"
                        />
                    </a>
                </template>

                <template #empty>Nothing tracked today, yet…</template>

                <template #row-action="{ track }">
                    <button
                        v-if="!deleting.includes(track.id)"
                        type="button"
                        class="w-36 rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-500 dark:hover:bg-red-400"
                        @click="deleteTracking(track.id)"
                    >
                        Delete
                    </button>
                    <button
                        v-else
                        type="button"
                        disabled
                        class="w-36 rounded-md bg-red-600 px-4 py-2 text-white opacity-75 dark:bg-red-500"
                    >
                        <font-awesome-icon :icon="['fas', 'spinner']" spin />
                    </button>
                </template>
            </TrackingTable>
        </div>
    </AuthenticatedLayout>
</template>

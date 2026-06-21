<script setup>
import { ref, computed, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import FlashMessages from '@/Components/FlashMessages.vue'
import TrackingTable from '@/Components/Tracking/TrackingTable.vue'
import { useTrackingPolling } from '@/Composables/useTrackingPolling'
import { epochStart, endOfToday } from '@/Utils/dateRange'

const props = defineProps({
    statuses: { type: [Array, Object], default: () => ({}) },
    batches: { type: Array, default: () => [] },
    flash: { type: Object, default: () => ({}) },
})

const trackingData = ref([...props.batches])
const flash = ref(props.flash)
const selected = ref([])
const deleting = ref(false)

watch(
    () => props.batches,
    (batches) => {
        trackingData.value = [...batches]
    }
)

const allIds = computed(() => trackingData.value.flatMap((batch) => batch.tracking.map((t) => t.id)))

const allSelected = computed(
    () => allIds.value.length > 0 && selected.value.length === allIds.value.length
)

const selectAll = () => {
    selected.value = [...allIds.value]
}

const clearSelection = () => {
    selected.value = []
}

const toggleBatch = (batch) => {
    const batchIds = batch.tracking.map((track) => track.id)
    const allInBatchSelected = batchIds.every((id) => selected.value.includes(id))

    selected.value = allInBatchSelected
        ? selected.value.filter((id) => !batchIds.includes(id))
        : [...new Set([...selected.value, ...batchIds])]
}

const deleteSelected = async () => {
    if (selected.value.length === 0) return
    if (!confirm('Are you sure you want to delete the selected tracking numbers?')) return

    deleting.value = true
    try {
        await axios.delete('/tracking/bulkdelete', { data: { ids: selected.value } })
        const removed = new Set(selected.value)
        trackingData.value = trackingData.value.map((batch) => ({
            ...batch,
            tracking: batch.tracking.filter((track) => !removed.has(track.id)),
        }))
        selected.value = []
    } catch (error) {
        console.error('Error deleting tracking:', error)
    } finally {
        deleting.value = false
    }
}

useTrackingPolling({
    format: 'date',
    range: () => ({ start: epochStart(), end: endOfToday() }),
    onData: (data) => {
        trackingData.value = data
        
        const valid = new Set(allIds.value)
        selected.value = selected.value.filter((id) => valid.has(id))
    },
})

const exportHref = computed(() =>
    route('export.tracking', {
        type: 'selection',
        start: epochStart(),
        end: endOfToday(),
        selection: selected.value,
    })
)
</script>

<template>
    <Head title="Tracking History" />

    <AuthenticatedLayout>
        <div class="container mx-auto max-w-3xl p-6">
            <FlashMessages :flash="flash" />

            <h1 class="mb-4 text-2xl font-bold">Tracking Numbers</h1>

            <TrackingTable
                :batches="trackingData"
                :statuses="statuses"
                selectable-batches
                @batch-click="toggleBatch"
            >
                <template #toolbar>
                    <div class="flex justify-end gap-2">
                        <button
                            type="button"
                            class="btn"
                            :aria-label="allSelected ? 'Deselect all' : 'Select all'"
                            @click="allSelected ? clearSelection() : selectAll()"
                        >
                            <font-awesome-icon
                                :icon="allSelected ? ['far', 'rectangle-xmark'] : ['fas', 'square-check']"
                                class="inline-block h-6 w-6 fill-current text-gray-300 hover:text-blue-500"
                            />
                        </button>

                        <a :href="exportHref" class="btn" aria-label="Export selection">
                            <font-awesome-icon
                                :icon="['fas', 'file-excel']"
                                class="inline-block h-6 w-6 fill-current text-gray-300 hover:text-green-500"
                            />
                        </a>

                        <button
                            type="button"
                            class="btn"
                            :disabled="deleting || selected.length === 0"
                            aria-label="Delete selected"
                            @click="deleteSelected"
                        >
                            <font-awesome-icon
                                v-if="!deleting"
                                :icon="['fas', 'trash-can']"
                                class="inline-block h-6 w-6 fill-current text-gray-300 hover:text-red-500"
                            />
                            <font-awesome-icon
                                v-else
                                :icon="['fas', 'spinner']"
                                spin
                                class="inline-block h-6 w-6 fill-current text-gray-300"
                            />
                        </button>
                    </div>
                </template>

                <template #empty>
                    No history available,
                    <a class="underline" :href="route('tracking.index')">start tracking</a>
                </template>

                <template #row-action="{ track }">
                    <input
                        :id="`track_id_${track.id}`"
                        v-model="selected"
                        :value="track.id"
                        type="checkbox"
                        class="h-4 w-4 rounded-sm border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600"
                    />
                </template>
            </TrackingTable>
        </div>
    </AuthenticatedLayout>
</template>

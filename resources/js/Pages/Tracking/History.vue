<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

import { ref, watch, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()

const props = defineProps({
    statuses: Array,
    batches: Array,
    flash: {
        type: Object,
        default: () => ({}),
    },
})

const trackingData = ref([...props.batches])
const selected = ref([])
const allSelected = ref([])
const loading = ref(false)

const deleteSelected = async () => {
    loading.value = true
    if (confirm('Are you sure you want to delete the selected tracking number?')) {
        await axios.delete(`/tracking/bulkdelete`, {
            data: { ids: [...selected.value] },
            onSuccess: () => {
                console.log('deleted')
                trackingData.value = trackingData.value.map((batch) => {
                    return {
                        ...batch,
                        tracking: batch.tracking.filter((track) => track.id !== id),
                    }
                })
            },
        })
    }
}
let intervalId
const pollTrackingUpdates = () => {
    intervalId = setInterval(() => {
        axios
            .get(
                '/api/tracking?format=date&start=' +
                    new Date(0).toISOString() +
                    '&end=' +
                    new Date(new Date().setHours(23, 59, 59, 999)).toISOString()
            )
            .then((response) => {
                trackingData.value = response.data
                loading.value = false
                console.log('loading false')
            })
            .catch((error) => {
                console.error('Error fetching tracking data:', error)
            })
    }, 5000) // Poll every 5 seconds
}

watch(
    () => props.tracking,
    (newTracking) => {
        trackingData.value = [...newTracking]
    }
)

onMounted(() => {
    pollTrackingUpdates()
})
onUnmounted(() => {
    clearInterval(intervalId)
})
const selectAll = () => {
    allSelected.value = 1
    selected.value = trackingData.value.flatMap((batch) => batch.tracking.map((track) => track.id))
}
const deSelectAll = () => {
    allSelected.value = 2
    selected.value = []
}
const selectBatch = (batch) => {
    const setA = [...Object.values(selected.value)].sort()
    const setB = (selected.value = batch.tracking.map((track) => track.id))
    if (setA.length === setB.length && setA.every((val, i) => val === setB[i])) {
        selected.value = []
    } else {
        selected.value = setB
    }

    changed()
}
const changed = () => {
    const setA = [...Object.values(selected.value)].sort()
    const setB = trackingData.value
        .flatMap((batch) => batch.tracking.map((track) => track.id))
        .sort()

    if (setA.length === setB.length && setA.every((val, i) => val === setB[i])) {
        allSelected.value = 1
    } else if (selected.value.length === 0) {
        allSelected.value = 2
    } else {
        allSelected.value = 0
    }
}
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <div class="container mx-auto max-w-3xl p-6">
            <!-- Success or Error Message -->
            <div
                v-if="flash && flash.success"
                class="mb-4 rounded-lg bg-green-100 p-4 text-green-700"
            >
                {{ flash.success }}
            </div>
            <div v-if="flash && flash.error" class="mb-4 rounded-lg bg-red-100 p-4 text-red-700">
                {{ flash.error }}
            </div>

            <h1 class="mb-4 text-2xl font-bold">Tracking Numbers</h1>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border dark:divide-gray-700">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300"
                            >
                                Tracking Number
                            </th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300"
                            >
                                Detail
                            </th>
                            <th class="pr-2 text-right">
                                <div class="flex justify-end gap-2">
                                    <div class="btn">
                                        <font-awesome-icon
                                            v-if="allSelected !== 1"
                                            @click="selectAll"
                                            class="inline-block h-6 w-6 fill-current text-gray-300 hover:cursor-pointer hover:text-blue-500"
                                            :icon="['fas', 'square-check']"
                                        />
                                        <font-awesome-icon
                                            v-if="allSelected === 1"
                                            @click="deSelectAll"
                                            class="inline-block h-6 w-6 fill-current text-gray-300 hover:cursor-pointer hover:text-blue-500"
                                            :icon="['far', 'rectangle-xmark']"
                                        />
                                    </div>
                                    <a
                                        :href="
                                            route('export.tracking', {
                                                type: 'selection',
                                                start: new Date(0).toISOString(),
                                                end: new Date(
                                                    new Date().setHours(23, 59, 59, 999)
                                                ).toISOString(),
                                                selection: selected,
                                            })
                                        "
                                        class="btn"
                                    >
                                        <font-awesome-icon
                                            class="inline-block h-6 w-6 fill-current text-gray-300 hover:text-green-500"
                                            :icon="['fas', 'file-excel']"
                                        />
                                    </a>

                                    <div class="btn">
                                        <font-awesome-icon
                                            v-if="!loading"
                                            @click="deleteSelected"
                                            class="hover: inline-block h-6 w-6 cursor-pointer fill-current text-gray-300 hover:text-red-500"
                                            :icon="['fas', 'trash-can']"
                                        />
                                        <FontAwesomeIcon
                                            v-if="loading"
                                            class="inline-block h-6 w-6 fill-current text-gray-300"
                                            icon="fa-solid fa-spinner"
                                            spin
                                        />
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-if="batches.length == 0">
                            <td
                                class="px-3 text-center text-sm text-gray-600 dark:text-gray-300"
                                colspan="4"
                            >
                                No history available,
                                <a class="underline" :href="route('tracking.index')"
                                    >start tracking</a
                                >
                            </td>
                        </tr>
                        <template v-for="(batch, batchIndex) in trackingData" :key="batch.id">
                            <tr>
                                <td
                                    @click="selectBatch(batch)"
                                    class="px-3 text-sm text-gray-600 hover:cursor-pointer dark:text-gray-300"
                                    colspan="4"
                                >
                                    Batch: {{ batch.formatted_created_at }}
                                </td>
                            </tr>
                            <tr v-for="(track, trackingIndex) in batch.tracking" :key="track.id">
                                <td
                                    class="bg-white px-6 py-4 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-200"
                                >
                                    {{ track.number }}
                                </td>
                                <td
                                    class="bg-white px-6 py-4 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-200"
                                >
                                    {{ props.statuses[track.status] }}
                                </td>
                                <td
                                    class="bg-white px-6 py-4 text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-200"
                                >
                                    {{ track.summary_response }}
                                </td>
                                <td
                                    class="bg-white px-6 py-4 text-right text-sm text-gray-900 dark:bg-gray-900 dark:text-gray-200"
                                >
                                    <input
                                        :id="'track_id_' + track.id"
                                        :value="track.id"
                                        v-model="selected"
                                        @change="changed"
                                        type="checkbox"
                                        value="1"
                                        class="h-4 w-4 rounded-sm border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600"
                                    />
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

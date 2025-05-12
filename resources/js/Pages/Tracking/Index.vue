
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import { ref, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const props = defineProps({
  statuses: Array,
  batches: Array,
  flash: {
    type: Object,
    default: () => ({}),
  },
});

const trackingData = ref([...props.batches]);
const loading = ref([]);

const newTracking = ref({ number: '' });
let flash = ref(props.flash);

const createTracking = async () => {
  try {
    const response = await axios.post('/tracking', newTracking.value);
    flash = response.data.flash;
    console.log(flash);
    trackingData.value = response.data.tracking;

    // axios.get('/api/tracking')
    //   .then(response => {
    //     //trackingData.value = response.data;
    //   })
    //   .catch(error => {
    //     console.error("Error fetching tracking data:", error);
    //   });
    newTracking.value = { number: '' }; // Reset form
  } catch (error) {
    console.error("Error creating tracking:", error);
  }
};

const deleteTracking = async (id) => {
  loading.value.push(id);
  if (confirm('Are you sure you want to delete this tracking number?')) {
    await axios.delete(`/tracking/${id}`, {
      onSuccess: () => {
        trackingData.value = trackingData.value.map(batch => {
          return {
            ...batch,
            tracking: batch.tracking.filter(track => track.id !== id)
          };
        });
      },
    });
  }
};
let intervalId;
const pollTrackingUpdates = () => {
  intervalId = setInterval(() => {
    axios.get('/api/tracking?format=time&start=' + new Date(new Date().setHours(0, 0, 0, 0)).toISOString() + '&end=' + new Date(new Date().setHours(23, 59, 59, 999)).toISOString())
      .then(response => {
        trackingData.value = response.data;
        loading.value = [];
      })
      .catch(error => {
        console.error("Error fetching tracking data:", error);
      });
  }, 5000); // Poll every 5 seconds
};

watch(() => props.tracking, (newTracking) => {
  trackingData.value = [...newTracking];
});

onMounted(() => {
  pollTrackingUpdates();
});

onUnmounted(() => {
  clearInterval(intervalId)
});

</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>

    <div class="container mx-auto max-w-3xl p-6">
      <!-- Success or Error Message -->
      <div v-if="flash && flash.success" class="mb-4 rounded-lg bg-green-100 p-4 text-green-700">
        {{ flash.success }}
      </div>
      <div v-if="flash && flash.error" class="mb-4 rounded-lg bg-red-100 p-4 text-red-700">
        {{ flash.error }}
      </div>

      <h1 class="text-2xl font-bold mb-4">Tracking Numbers</h1>



  <div class="mb-6">
    <form @submit.prevent="createTracking" class="space-y-4">
      <div>
        <label for="number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Tracking Numbers (one per line, max 50)
        </label>
        <textarea 
          v-model="newTracking.number" 
          id="number"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
          rows="5"
        ></textarea>
      </div>
      <button 
        type="submit" 
        class="w-full rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-500 dark:hover:bg-indigo-400"
      >
        Track
      </button>
    </form>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full border divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-100 dark:bg-gray-800">
        <tr>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Tracking Number</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Status</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Detail</th>
          <th class="px-6 py-3 text-right">
            <a :href="route('export.tracking', {
              type: 'daterange',
              start: new Date(new Date().setHours(0, 0, 0, 0)).toISOString(),
              end: new Date(new Date().setHours(23, 59, 59, 999)).toISOString()
              })" class="btn">
                <svg class="w-6 h-6 text-gray-300 hover:text-green-500 fill-current inline-block" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M64 0C28.7 0 0 28.7 0 64L0 448c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-288-128 0c-17.7 0-32-14.3-32-32L224 0 64 0zM256 0l0 128 128 0L256 0zM155.7 250.2L192 302.1l36.3-51.9c7.6-10.9 22.6-13.5 33.4-5.9s13.5 22.6 5.9 33.4L221.3 344l46.4 66.2c7.6 10.9 5 25.8-5.9 33.4s-25.8 5-33.4-5.9L192 385.8l-36.3 51.9c-7.6 10.9-22.6 13.5-33.4 5.9s-13.5-22.6-5.9-33.4L162.7 344l-46.4-66.2c-7.6-10.9-5-25.8 5.9-33.4s25.8-5 33.4 5.9z"/></svg>
            </a>
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
        <tr v-if="trackingData.length == 0">
            <td class="px-3 text-center text-sm text-gray-600 dark:text-gray-300" colspan="4">Nothing tracked today, yet...</td>
        </tr>
        <template v-for="(batch, batchIndex) in trackingData" :key="batch.id">
          <tr>
            <td class="px-3 text-sm text-gray-600 dark:text-gray-300" colspan="4">Batch: {{ batch.formatted_created_at }}</td>
          </tr>
          <tr v-for="(track, trackingIndex) in batch.tracking" :key="track.id">
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200 bg-white dark:bg-gray-900">{{ track.number }}</td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200 bg-white dark:bg-gray-900">{{ props.statuses[track.status] }}</td>
            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200 bg-white dark:bg-gray-900">{{ track.summary_response }}</td>
            <td class="px-6 py-4 text-right bg-white dark:bg-gray-900">
              <button 
                @click="deleteTracking(track.id)" 
                v-if="!loading.includes(track.id)"
                class="w-36 rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-500 dark:hover:bg-red-400"
              >
                Delete
              </button>
              <button
                v-if="loading.includes(track.id)"
                class="w-36 rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-500 dark:hover:bg-red-400"
              >
                <FontAwesomeIcon icon="fa-solid fa-spinner" spin />
              </button>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>

    </AuthenticatedLayout>
</template>
  
  
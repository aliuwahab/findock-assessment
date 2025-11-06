<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    uploads: Object,
});

const showUploadForm = ref(false);

const form = useForm({
    file: null,
    mappings: {
        address: 'Address' // Default mapping - user's CSV must have this column
    }
});

const submit = () => {
    form.post(route('csv-uploads.store'), {
        onSuccess: () => {
            form.reset();
            showUploadForm.value = false;
        },
    });
};

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        failed: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString();
};
</script>

<template>
    <Head title="CSV Uploads" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    CSV Address Validation
                </h2>
                <PrimaryButton @click="showUploadForm = !showUploadForm">
                    {{ showUploadForm ? 'Cancel' : 'Upload New CSV' }}
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Upload Form -->
                <div v-if="showUploadForm" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Upload CSV File</h3>
                        
                        <form @submit.prevent="submit" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Select CSV File
                                </label>
                                <input
                                    type="file"
                                    accept=".csv"
                                    @input="form.file = $event.target.files[0]"
                                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer focus:outline-none p-2"
                                />
                                <p class="mt-2 text-sm text-gray-500">
                                    Your CSV must have an "Address" column containing full addresses to validate.
                                </p>
                                <div v-if="form.errors.file" class="text-sm text-red-600 mt-2">
                                    {{ form.errors.file }}
                                </div>
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <p class="text-sm text-blue-800">
                                    <strong>CSV Format:</strong> Files with ≤10 addresses process instantly. 
                                    Larger files process in the background with progress tracking.
                                </p>
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton 
                                    :class="{ 'opacity-25': form.processing }" 
                                    :disabled="form.processing || !form.file"
                                >
                                    {{ form.processing ? 'Uploading...' : 'Upload & Validate' }}
                                </PrimaryButton>
                                
                                <button
                                    type="button"
                                    @click="showUploadForm = false"
                                    class="text-sm text-gray-600 hover:text-gray-900"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Uploads List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Your Uploads</h3>
                        
                        <div v-if="uploads.data && uploads.data.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            File Name
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Progress
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Addresses
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Uploaded
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="upload in uploads.data" :key="upload.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ upload.file_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[getStatusColor(upload.status), 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full']">
                                                {{ upload.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2 max-w-[120px]">
                                                    <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: upload.progress_percentage + '%' }"></div>
                                                </div>
                                                <span class="text-xs">{{ upload.progress_percentage }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ upload.processed_rows }} / {{ upload.total_rows }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(upload.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link
                                                :href="route('csv-uploads.show', upload.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                View Results
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No uploads yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by uploading a CSV file with addresses.</p>
                            <div class="mt-6">
                                <PrimaryButton @click="showUploadForm = true">
                                    Upload CSV
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

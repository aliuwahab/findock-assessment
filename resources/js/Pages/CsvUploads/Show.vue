<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    upload: Object,
    fields: Object,
    statistics: Object,
});

const getStatusColor = (status) => {
    const colors = {
        valid: 'bg-green-100 text-green-800',
        invalid: 'bg-red-100 text-red-800',
        error: 'bg-yellow-100 text-yellow-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString();
};
</script>

<template>
    <Head :title="`Upload: ${upload.file_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ upload.file_name }}
                </h2>
                <Link
                    :href="route('csv-uploads.index')"
                    class="text-sm text-indigo-600 hover:text-indigo-900"
                >
                    ← Back to Uploads
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Upload Info & Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Upload Status Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Status</h3>
                            <span :class="[getStatusColor(upload.status), 'px-3 py-1 inline-flex text-sm font-semibold rounded-full']">
                                {{ upload.status }}
                            </span>
                            <div class="mt-4">
                                <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
                                    <span>Progress</span>
                                    <span>{{ upload.progress_percentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: upload.progress_percentage + '%' }"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    {{ upload.processed_rows }} / {{ upload.total_rows }} addresses processed
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Valid Addresses -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Valid Addresses</h3>
                            <p class="text-3xl font-bold text-green-600">{{ statistics.valid }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ statistics.valid_percentage }}% of total</p>
                        </div>
                    </div>

                    <!-- Invalid & Errors -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Issues</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Invalid</span>
                                    <span class="text-lg font-semibold text-red-600">{{ statistics.invalid }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Errors</span>
                                    <span class="text-lg font-semibold text-yellow-600">{{ statistics.error }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Validation Results Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Validation Results</h3>
                        
                        <div v-if="fields.data && fields.data.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Original Address
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Formatted Address
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Confidence
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Location
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="field in fields.data" :key="field.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ field.field_data?.address || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[getStatusColor(field.validation_status), 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full']">
                                                {{ field.validation_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <template v-if="field.validation_result">
                                                {{ field.validation_result.formatted_address || '-' }}
                                            </template>
                                            <template v-else>-</template>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <template v-if="field.validation_result?.confidence">
                                                <div class="flex items-center">
                                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                        <div 
                                                            class="bg-green-600 h-2 rounded-full" 
                                                            :style="{ width: (field.validation_result.confidence * 100) + '%' }"
                                                        ></div>
                                                    </div>
                                                    <span class="text-xs">{{ Math.round(field.validation_result.confidence * 100) }}%</span>
                                                </div>
                                            </template>
                                            <template v-else>-</template>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <template v-if="field.validation_result?.latitude">
                                                {{ field.validation_result.latitude.toFixed(4) }}, 
                                                {{ field.validation_result.longitude.toFixed(4) }}
                                            </template>
                                            <template v-else>-</template>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <div v-if="fields.links" class="mt-4 flex justify-between items-center">
                                <div class="text-sm text-gray-700">
                                    Showing {{ fields.from }} to {{ fields.to }} of {{ fields.total }} results
                                </div>
                                <div class="flex gap-1">
                                    <Link
                                        v-for="link in fields.links"
                                        :key="link.label"
                                        :href="link.url"
                                        :class="[
                                            'px-3 py-1 text-sm rounded',
                                            link.active 
                                                ? 'bg-indigo-600 text-white' 
                                                : link.url 
                                                    ? 'bg-white text-gray-700 hover:bg-gray-50 border' 
                                                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        ]"
                                        v-html="link.label"
                                    />
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="text-center py-12">
                            <p class="text-gray-500">
                                <template v-if="upload.status === 'pending' || upload.status === 'processing'">
                                    Processing addresses... This page will update automatically.
                                </template>
                                <template v-else>
                                    No validation results available.
                                </template>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Upload Metadata -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Information</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Uploaded At</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(upload.created_at) }}</dd>
                            </div>
                            <div v-if="upload.processing_started_at">
                                <dt class="text-sm font-medium text-gray-500">Processing Started</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(upload.processing_started_at) }}</dd>
                            </div>
                            <div v-if="upload.processing_completed_at">
                                <dt class="text-sm font-medium text-gray-500">Processing Completed</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(upload.processing_completed_at) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Addresses in CSV</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ upload.total_rows }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Unique Addresses Processed</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ statistics.total }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

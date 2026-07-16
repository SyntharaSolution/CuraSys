<template>
    <div class="max-w-7xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Expiry Monitor</h1>
            <p class="text-sm text-slate-500 mt-1">Track medicine batches nearing their expiration dates.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-red-50 border border-red-100 rounded-xl p-4 flex flex-col justify-center items-center text-center">
                <div class="text-3xl font-bold text-red-600 mb-1">{{ expiredCount }}</div>
                <div class="text-xs font-semibold text-red-800 uppercase tracking-wider">Expired</div>
            </div>
            <div class="bg-orange-50 border border-orange-100 rounded-xl p-4 flex flex-col justify-center items-center text-center">
                <div class="text-3xl font-bold text-orange-600 mb-1">{{ expiring30Count }}</div>
                <div class="text-xs font-semibold text-orange-800 uppercase tracking-wider">Expiring < 30 Days</div>
            </div>
            <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4 flex flex-col justify-center items-center text-center">
                <div class="text-3xl font-bold text-yellow-600 mb-1">{{ expiring90Count }}</div>
                <div class="text-xs font-semibold text-yellow-800 uppercase tracking-wider">Expiring < 90 Days</div>
            </div>
            <div class="bg-green-50 border border-green-100 rounded-xl p-4 flex flex-col justify-center items-center text-center">
                <div class="text-3xl font-bold text-green-600 mb-1">{{ safeCount }}</div>
                <div class="text-xs font-semibold text-green-800 uppercase tracking-wider">Safe (> 90 Days)</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mt-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Medicine</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Batch No.</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Expiry Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Days Left</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <tr v-if="loading">
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">Scanning warehouse...</td>
                        </tr>
                        <tr v-else-if="batches.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                No batches found.
                            </td>
                        </tr>
                        <tr v-for="batch in processedBatches" :key="batch.id" class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                {{ batch.medicine?.name }}
                                <div class="text-xs text-slate-400 font-normal">SKU: {{ batch.medicine?.sku }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-700">
                                {{ batch.batch_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ new Date(batch.expiry_date).toLocaleDateString() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" :class="batch.daysClass">
                                {{ batch.daysLeft }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" :class="batch.statusClass">
                                    {{ batch.statusLabel }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const batches = ref([]);
const loading = ref(true);

const expiredCount = ref(0);
const expiring30Count = ref(0);
const expiring90Count = ref(0);
const safeCount = ref(0);

const fetchData = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/v1/pharmacy/batches/expiring');
        batches.value = res.data.data;
    } catch (e) {
        console.error("Failed to fetch batches", e);
    } finally {
        loading.value = false;
    }
};

const processedBatches = computed(() => {
    let expired = 0;
    let exp30 = 0;
    let exp90 = 0;
    let safe = 0;

    const today = new Date();
    today.setHours(0,0,0,0);

    const mapped = batches.value.map(batch => {
        const expDate = new Date(batch.expiry_date);
        const diffTime = expDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        let statusLabel = '';
        let statusClass = '';
        let daysClass = '';

        if (diffDays < 0) {
            statusLabel = 'Expired';
            statusClass = 'bg-red-100 text-red-800';
            daysClass = 'text-red-600';
            expired++;
        } else if (diffDays <= 30) {
            statusLabel = 'Critical (< 30d)';
            statusClass = 'bg-orange-100 text-orange-800';
            daysClass = 'text-orange-600';
            exp30++;
        } else if (diffDays <= 90) {
            statusLabel = 'Warning (< 90d)';
            statusClass = 'bg-yellow-100 text-yellow-800';
            daysClass = 'text-yellow-600';
            exp90++;
        } else {
            statusLabel = 'Safe';
            statusClass = 'bg-green-100 text-green-800';
            daysClass = 'text-green-600';
            safe++;
        }

        return {
            ...batch,
            daysLeft: diffDays < 0 ? `${Math.abs(diffDays)} days ago` : `${diffDays} days`,
            statusLabel,
            statusClass,
            daysClass
        };
    });

    expiredCount.value = expired;
    expiring30Count.value = exp30;
    expiring90Count.value = exp90;
    safeCount.value = safe;

    return mapped;
});

onMounted(() => {
    fetchData();
});
</script>

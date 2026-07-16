<template>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div :class="['w-14 h-14 rounded-xl flex items-center justify-center shrink-0', colorClass]">
            <slot name="icon"></slot>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500 mb-1">{{ title }}</p>
            <h3 class="text-2xl font-bold text-slate-800">{{ value }}</h3>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: { type: String, required: true },
    value: { type: [String, Number], required: true },
    colorClass: { type: String, default: 'bg-blue-50 text-blue-600' }
});
</script>

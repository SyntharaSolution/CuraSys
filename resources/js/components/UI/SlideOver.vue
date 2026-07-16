<template>
    <div>
        <!-- Backdrop -->
        <transition enter-active-class="transition-opacity ease-linear duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-opacity ease-linear duration-300" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="show" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40" @click="close"></div>
        </transition>

        <!-- SlideOver Panel -->
        <transition enter-active-class="transform transition ease-in-out duration-300 sm:duration-500" enter-from-class="translate-x-full" enter-to-class="translate-x-0" leave-active-class="transform transition ease-in-out duration-300 sm:duration-500" leave-from-class="translate-x-0" leave-to-class="translate-x-full">
            <div v-if="show" class="fixed inset-y-0 right-0 z-50 flex w-full sm:max-w-md">
                <div class="w-full h-full flex flex-col bg-white shadow-2xl overflow-y-auto relative">
                    
                    <!-- Header -->
                    <div class="px-6 py-6 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white/90 backdrop-blur z-10">
                        <h2 class="text-xl font-bold text-slate-800">{{ title }}</h2>
                        <button @click="close" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <!-- Body -->
                    <div class="flex-1 px-6 py-6 relative">
                        <slot></slot>
                    </div>

                    <!-- Footer Actions -->
                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3 sticky bottom-0">
                        <button type="button" @click="close" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                            Cancel
                        </button>
                        <button type="button" @click="$emit('save')" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-500/30 flex items-center gap-2" :disabled="saving">
                            <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ saving ? 'Saving...' : saveText }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, required: true },
    saveText: { type: String, default: 'Save' },
    saving: { type: Boolean, default: false }
});

const emit = defineEmits(['update:show', 'save']);

const close = () => {
    if(!props.saving) {
        emit('update:show', false);
    }
};
</script>

<script setup>
const props = defineProps({
    type: {
        type: String,
        default: 'primary',
    },
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <button 
        class="px-4 py-2 rounded-lg flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed" 
        :class="{
            'bg-red-500 text-white': type === 'delete',
            'bg-green-500 text-white': type === 'success',
            'bg-blue-500 text-white': type === 'info',
            'bg-yellow-500 text-white': type === 'warning',
            'bg-purple-500 text-white': type === 'question',
        }"
        :disabled="loading || disabled"
    >
        <div v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
        <slot />
    </button>
</template>

<script setup>
defineProps({
    type: String,
});
</script>

<template>
    <div class="flex flex-row gap-4 text-white p-4 rounded-lg" :class="{
        'bg-green-500': type === 'success',
        'bg-red-500': type === 'error' || type === 'danger',
        'bg-blue-500': type === 'info',
        'bg-yellow-500': type === 'warning',
        'bg-purple-500': type === 'question',
    }">
        <slot />
    </div>
</template>

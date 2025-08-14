<script setup>
import { Icon } from '@iconify/vue';    
const props = defineProps({
    label: String,
    value: String,
    stack: Boolean,
    icon: String,
    type: String,
});
</script>

<template>
    <div class="flex flex-row gap-2 min-h-10 items-center pl-1" :class="{ 'flex-col': stack, 'border-b border-gray-200': !stack }">
        <div class="w-2/3 font-bold flex flex-row gap-2 items-center" :class="{ 'w-full': stack }">
            <Icon v-if="icon" :icon="icon" class="mr-2" />
            {{ label }}
        </div>
        <div class="w-1/3" :class="{ 'w-full': stack }">
            <span :class="type === 'danger' ? 'bg-red-500 text-white px-2 py-1 rounded-md' : ''">{{ value }}</span>
        </div>
    </div>
</template>


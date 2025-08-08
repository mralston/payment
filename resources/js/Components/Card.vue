<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    headerClass: String,
    bodyClass: String,
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const isContentVisible = ref(!props.collapsed);

const toggleCollapse = () => {
    isContentVisible.value = !isContentVisible.value;
};

const arrowClass = computed(() => {
    return isContentVisible.value ? '' : 'rotate-90';
});
</script>

<template>
    <div class="divide-y divide-gray-200 overflow-hidden rounded-lg bg-white shadow-lg">
        <div class="flex cursor-pointer items-center justify-between px-4 py-5 sm:px-6" :class="headerClass" @click="toggleCollapse">
            <slot name="header"></slot>
            <svg :class="arrowClass" class="h-5 w-5 transform text-gray-400 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </div>

        <div v-show="isContentVisible" class="px-4 py-5 sm:p-6" :class="bodyClass">
            <slot />
        </div>
    </div>
</template>

<style scoped>
/* You can add custom styles here if needed, but Tailwind CSS handles most of the styling. */
</style>

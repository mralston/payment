<script setup>
import { Icon } from '@iconify/vue';

defineProps({
    crumbs: Array,
});
</script>

<template>
    <div class="flex flex-row gap-2 items-center">
       <div
        v-for="(crumb, index) in crumbs"
        :key="crumb.href"
        class="flex flex-row gap-4 items-center"
       >
        <a
            :href="crumb.href"
            class="text-blue-500 text-md"
            :class="{'font-bold underline': index === crumbs.length - 1}"
        >
            {{ crumb.name }}
        </a>
        <span
            v-if="index !== crumbs.length - 1"
            class="text-gray-500"
        >
            /
        </span>
       </div>
    </div>
</template>
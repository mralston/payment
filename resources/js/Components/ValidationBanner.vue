<script setup>

import {ExclamationTriangleIcon} from "@heroicons/vue/20/solid/index.js";

const props = defineProps({
    form: Object,
});

</script>

<template>
    <div v-if="form.hasErrors" class="border-red-500 bg-red-100 border-2 rounded-lg p-2 mb-4">
        <ExclamationTriangleIcon class="h-6 w-6 inline-block mr-2 text-red-500"/>
        The form cannot be submitted. There
        <span v-if="Object.keys(form.errors).length == 1">is 1 item</span>
        <span v-else>are {{ Object.keys(form.errors).length }} items</span>
        to be addressed.
    </div>
</template>

<style scoped>

</style>

<script setup>

import {Head} from "@inertiajs/vue3";

const props = defineProps({
    reason: String,
});

</script>

<template>
    <Head>
        <title>Payment Process Disabled</title>
    </Head>

    <div class="p-4">
        <h1 class="text-4xl font-bold mb-5">
            Payment Process Unavailable
        </h1>

        <p>
            {{ reason }}
        </p>
    </div>
</template>

<style scoped>

</style>

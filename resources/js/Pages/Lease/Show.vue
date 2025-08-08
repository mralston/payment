<script setup>

import {Head} from "@inertiajs/vue3";
import {cleanUrl} from "../../Helpers/Strings.js";
import {formatDate, fromNow} from "../../Helpers/Date.js";
import {makeNumeric} from "../../Helpers/Number.js";
import RepresentativeExample from "../../Components/RepresentativeExample.vue";

const props = defineProps({
    parentModel: Object,
    survey: Object,
    offer: Object,
    totalCost: Number,
    deposit: Number,
    companyDetails: Object,
    lenders: Array,
});

</script>

<template>

    <Head>
        <title>Lease</title>
    </Head>

    <div class="p-4">

        <h1 class="text-4xl font-bold mb-6">
            Hometree
        </h1>

        <p class="mt-4">Great news! Hometree have accepted your application. They'll get in touch with you soon.</p>

        <button class="mt-10 rounded-md bg-blue-600 px-3 py-2 text-center text-sm/6 font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
            Pay Deposit
        </button>

    </div>

</template>

<style scoped>

</style>

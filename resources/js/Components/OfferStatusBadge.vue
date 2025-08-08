<script setup>

import {CheckCircleIcon} from "@heroicons/vue/20/solid/index.js";

const props = defineProps({
    offer: Object,
    lowestOffer: Object,
});

</script>

<template>
    <div class="absolute -top-4 -left-4">
        <span v-if="offer?.status === 'preapproved'"
              class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
            <CheckCircleIcon class="h-4 w-4 mr-1 flex-none text-green-600" aria-hidden="true" />
            PRE APPROVED
        </span>
        <span v-else-if="offer && offer?.id === lowestOffer?.id"
              class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
            FROM ONLY
        </span>
        <span v-else-if="offer"
              class="inline-flex items-center rounded-full bg-orange-50 px-2 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20">
            YOU PAY
        </span>
    </div>
</template>

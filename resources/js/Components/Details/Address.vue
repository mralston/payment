<script setup>
import { Icon } from '@iconify/vue';
import DetailsRow from './DetailsRow.vue';
import Card from '../Card.vue';

const props = defineProps({
    payment: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Card class="border border-[#ed6058]" header-class="border-[#ed6058]">
        <template #header>
            <div class="flex flex-row gap-2 items-center">
                <h2 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:house" /> Address History (Billing Address)</h2>
            </div>
        </template>
        <div class="flex flex-row gap-4 max-md:flex-col">
            <div
                v-for="(address, key) in payment.addresses"
                class="max-md:w-full"
                :class="{
                    'w-full': payment.addresses.length === 1,
                    'w-1/2': payment.addresses.length === 2,
                    'w-1/3': payment.addresses.length === 3,
                }"
            >
                <h2 v-if="key === 0" class="text-xl font-bold">Installation Address</h2>
                <h2 v-else class="text-xl font-bold">Previous Address</h2>
                <DetailsRow
                    label="House"
                    :value="payment.addresses[0]?.house_number"
                />
                <DetailsRow
                    label="Line 1"
                    :value="payment.addresses[0]?.address1"
                />
                <DetailsRow
                    label="Line 2"
                    :value="payment.addresses[0]?.address2"
                />
                <DetailsRow
                    label="Town"
                    :value="payment.addresses[0]?.town"
                />
                <DetailsRow
                    label="County"
                    :value="payment.addresses[0]?.county"
                />
                <DetailsRow
                    label="Post Code"
                    :value="payment.addresses[0]?.postCode"
                />
            </div>
        </div>
    </Card>
</template>

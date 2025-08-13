<script setup>
import { Icon } from '@iconify/vue';
import DetailsRow from './DetailsRow.vue';
import SigningLink from '../SigningLink.vue';
import Cancel from '../Cancel.vue';

const props = defineProps({
    payment: {
        type: Object,
        required: true,
    },
    paymentType: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <div class="p-10 bg-white border border-[#ed6058] rounded-lg">
        <h1 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:file-invoice" /> Summary</h1>
        <div class="mt-10 flex flex-col gap-8">
            <SigningLink :payment="payment" />
            <DetailsRow 
                icon="fa6-solid:file-invoice"
                :stack="true"
                label="Application #"
                :value="payment.reference"
            />
            <DetailsRow
                icon="fa6-solid:user"
                :stack="true" label="Customer"
                :value="payment.first_name + ' ' + payment.last_name" />
            <DetailsRow
                icon="fa6-solid:hand-holding-dollar"
                :stack="true"
                :label="paymentType"
                :value="payment.payment_offer.name"
            />
            <DetailsRow
                icon="fa6-solid:building"
                :stack="true"
                label="Lender"
                :value="payment.payment_provider.name"
            />
            <DetailsRow
                icon="fa6-solid:circle-check"
                :stack="true"
                label="Status"
                :value="payment.payment_status.name"
            />
            <DetailsRow
                icon="fa6-solid:solar-panel"
                :stack="true"
                label="Purpose of loan"
                value="Home Improvements"
            />
            <DetailsRow
                icon="fa6-solid:user"
                :stack="true"
                label="Consultant ref"
                :value="payment.parentable?.user?.name" />
            <Cancel :payment="payment" />
        </div>
    </div>
</template>
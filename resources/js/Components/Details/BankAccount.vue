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
                <h2 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:credit-card" /> Bank Account</h2>
            </div>
        </template>
        <div class="flex flex-row gap-4 max-md:flex-col">
            <div class="w-1/2 max-md:w-full">
                <DetailsRow label="Account Holder" :value="payment.bank_account_holder_name" />
                <DetailsRow label="Sort code" :value="payment.bank_account_sort_code" />
                <DetailsRow label="Account Number" :value="payment.bank_account_number.substring(0, 2) + '****' + payment.bank_account_number.substring(6, 8)" />
            </div>
            <div class="w-1/2 max-md:w-full">
                <DetailsRow label="Bank name" :value="payment.bank_name" />
            </div>
        </div>
    </Card>
</template>

<script setup>
import { Icon } from '@iconify/vue';
import DetailsRow from './DetailsRow.vue';
import Card from '../Card.vue';
import moment from 'moment';

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
                <h2 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:bullhorn" /> Marketing & Consent</h2>
            </div>
        </template>
        <div class="flex flex-row gap-4 max-md:flex-col">
            <div class="w-1/2 max-md:w-full">
                <DetailsRow
                    label="Is over 21 years of age?"
                    :value="moment(payment.date_of_birth).isBefore(moment().subtract(21, 'years')) ? 'Yes' : 'No'"
                />
                <DetailsRow
                    label="Is UK resident?"
                    value="####"
                />
                <DetailsRow
                    label="Owns own property?"
                    value="###"
                />
                <DetailsRow
                    label="Is employed, self employed, or..."
                    :value="payment.employment_status"
                />
                <DetailsRow
                    label="Has a UK bank account"
                    :value="payment.bank_account_number ? 'Yes' : 'No'"
                />
            </div>
            <div class="w-1/2 max-md:w-full">
                <DetailsRow
                    label="Consent to credit search"
                    :value="payment.payment_status.name != 'new' ? 'Yes' : 'No'" />
                <DetailsRow
                    label="Consent to terms"
                    :value="payment.read_terms_conditions ? 'Yes' : 'No'" />
                <DetailsRow
                    label="Consent to marketing"
                    :value="payment.gdpr_opt_in ? 'Yes' : 'No'" />
            </div>
        </div>
    </Card>
</template>
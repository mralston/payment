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
                <h2 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:user" /> Applicant Details</h2>
            </div>
        </template>
        <div class="flex flex-row gap-4 max-md:flex-col">
            <div class="w-1/2 max-md:w-full">
                <DetailsRow
                    label="Title"
                    :value="payment.title"
                />
                <DetailsRow
                    label="First name"
                    :value="payment.first_name"
                />
                <DetailsRow
                    label="Middle name"
                    :value="payment.middle_name"
                />
                <DetailsRow
                    label="Last name"
                    :value="payment.last_name"
                />
                <DetailsRow
                    label="Email address"
                    :value="payment.email_address"
                />
                <DetailsRow
                    label="Primary phone"
                    :value="payment.primary_telephone"
                />
                <DetailsRow
                    label="Secondary phone"
                    :value="payment.secondary_telephone"
                />
            </div>
            <div class="w-1/2 max-md:w-full">
                <DetailsRow
                    label="Date of birth"
                    :value="moment(payment.date_of_birth).format('DD/MM/YYYY')"
                />
                <DetailsRow
                    label="Marital status"
                    :value="payment.marital_status"
                />
                <DetailsRow
                    label="Number of dependents"
                    :value="payment.dependents" />
            </div>
        </div>
    </Card>
</template>
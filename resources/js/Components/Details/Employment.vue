<script setup>
import { Icon } from '@iconify/vue';
import DetailsRow from './DetailsRow.vue';
import Card from '../Card.vue';
import {formatDate} from "../../Helpers/Date.js";

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
                <h2 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:briefcase" /> Employment</h2>
            </div>
        </template>
        <div class="bg-white">
            <div class="flex flex-row gap-4 max-md:flex-col">
                <div class="w-1/2 max-md:w-full">
                    <DetailsRow label="Employment status">
                        {{ payment.employment_status?.name }}
                    </DetailsRow>
                    <DetailsRow label="Employer name" class="mb-6">
                        {{ payment.employer_name }}
                    </DetailsRow>

                    <h2 class="text-xl font-bold">Employer's Address</h2>
                    <DetailsRow label="Building">
                        {{ payment.employer_address?.houseNumber }} {{ payment.employer_address?.street }}
                    </DetailsRow>
                    <DetailsRow v-if="payment.employer_address?.address1" label="Line 1">
                        {{ payment.employer_address.address1 }}
                    </DetailsRow>
                    <DetailsRow v-if="payment.employer_address?.address2" label="Line 2">
                        {{ payment.employer_address.address2 }}
                    </DetailsRow>
                    <DetailsRow label="Town">
                        {{ payment.employer_address?.town }}
                    </DetailsRow>
                    <DetailsRow label="County">
                        {{ payment.employer_address?.county }}
                    </DetailsRow>
                    <DetailsRow label="Post Code">
                        {{ payment.employer_address?.postCode }}
                    </DetailsRow>

                    <DetailsRow v-if="payment.employer_address.uprn" label="UPRN" class="text-gray-400">
                        {{ payment.employer_address.uprn }}
                    </DetailsRow>
                    <DetailsRow v-if="payment.employer_address.udprn" label="UDPRN" class="text-gray-400">
                        {{ payment.employer_address.udprn }}
                    </DetailsRow>

                </div>
                <div class="w-1/2 max-md:w-full">
                    <DetailsRow label="Occupation">
                        {{ payment.occupation }}
                    </DetailsRow>
                    <DetailsRow label="Time at employer">
                        {{ Math.floor(payment.time_with_employer / 12) }} years, {{ payment.time_with_employer % 12 }} months
                    </DetailsRow>
                </div>
            </div>
        </div>
    </Card>
</template>

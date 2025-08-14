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
                <h2 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:briefcase" /> Employment</h2>
            </div>
        </template>
        <div class="bg-white">
            <div class="flex flex-row gap-4 max-md:flex-col">
                <div class="w-1/2 max-md:w-full">
                    <DetailsRow
                        label="Employment status"
                        :value="payment.employment_status?.name"
                    />
                    <DetailsRow
                        label="Employer name"
                        :value="payment.employer_name"
                    />
                </div>
                <div class="w-1/2 max-md:w-full">
                    <DetailsRow
                        label="Occupation"
                        :value="payment.occupation" />
                    <DetailsRow
                        label="Time at employer"
                        :value="String(Math.floor(payment.time_with_employer / 12)) + ' years, ' + String(payment.time_with_employer % 12) + ' months'"
                    />
                </div>
            </div>
        </div>
    </Card>
</template>

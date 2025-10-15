<script setup>
import { Icon } from '@iconify/vue';
import DetailsRow from './DetailsRow.vue';
import Card from '../Card.vue';
import {formatDate} from "../../Helpers/Date.js";
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
                <h2 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:house" /> Address History</h2>
            </div>
        </template>
        <div class="flex flex-row gap-4 max-md:flex-col">
            <div v-for="(address, key) in payment.addresses"
                 class="max-md:w-full"
                 :class="{
                     'w-full': payment.addresses.length === 1,
                     'w-1/2': payment.addresses.length === 2,
                     'w-1/3': payment.addresses.length === 3,
                 }">
                <h2 v-if="key === 0" class="text-xl font-bold">Installation Address</h2>
                <h2 v-else class="text-xl font-bold">Previous Address</h2>
                <DetailsRow label="House">
                    {{ address?.houseNumber }} {{ address?.street }}
                </DetailsRow>
                <DetailsRow v-if="address?.address1" label="Line 1">
                    {{ address.address1 }}
                </DetailsRow>
                <DetailsRow v-if="address?.address2" label="Line 2">
                    {{ address.address2 }}
                </DetailsRow>
                <DetailsRow label="Town">
                    {{ address?.town }}
                </DetailsRow>
                <DetailsRow label="County">
                    {{ address?.county }}
                </DetailsRow>
                <DetailsRow label="Post Code">
                    {{ address?.postCode }}
                </DetailsRow>
                <DetailsRow v-if="address.dateMovedIn" label="Moved In">
                    <div class="mb-1">{{ formatDate(address.dateMovedIn, 'DD/MM/Y') }}</div>
                    <div class="italic text-gray-600">{{ moment(address?.dateMovedIn).from(moment(), true) }} ago</div>
                </DetailsRow>

                <DetailsRow v-if="address.uprn" label="UPRN" class="text-gray-400">
                    {{ address.uprn }}
                </DetailsRow>
                <DetailsRow v-if="address.udprn" label="UDPRN" class="text-gray-400">
                    {{ address.udprn }}
                </DetailsRow>
            </div>
        </div>
    </Card>
</template>

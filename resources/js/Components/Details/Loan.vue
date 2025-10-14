<script setup>
import { formatCurrency } from '../../Helpers/Currency.js';
import moment from 'moment';
import DetailsRow from './DetailsRow.vue';
import Card from '../Card.vue';
import { Icon } from '@iconify/vue';
import {makeNumeric} from "../../Helpers/Number.js";

const props = defineProps({
    payment: {
        type: Object,
        required: true,
    },
    products: {
        type: Array,
        required: true,
    },
    paymentType: {
        type: String,
        required: true,
    },
});

</script>

<template>
    <Card class="border border-[#ed6058]" header-class="border-[#ed6058]">
        <template #header>
            <div class="flex flex-row gap-2 items-center">
                <h2 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:money-bill-wave" /> {{ paymentType }}</h2>
            </div>
        </template>
        <div class="bg-white">
            <div class="flex flex-row gap-4 max-md:flex-col">
                <div class="w-1/2 max-md:w-full">
                    <DetailsRow label="Advance amount">
                        {{ formatCurrency(payment.amount) }}
                    </DetailsRow>
                    <DetailsRow label="Term (months)">
                        {{ payment.term }}
                    </DetailsRow>
                    <DetailsRow label="Deferred">
                        {{ payment.deferred ?? '-' }}
                        <span v-if="payment.payment_product?.deferred_type === 'bnpl_months'">&nbsp;months BNPL</span>
                        <span v-else-if="payment.payment_product?.deferred_type === 'deferred_payments'">&nbsp;deferred payments</span>
                    </DetailsRow>
                    <DetailsRow label="Cash price">
                        {{ formatCurrency(payment.total_cost ?? (makeNumeric(payment.amount) + makeNumeric(payment.deposit))) }}
                    </DetailsRow>
                    <DetailsRow label="Deposit">
                        {{ formatCurrency(payment.deposit) }}
                    </DetailsRow>
                    <DetailsRow label="Goods">
                        {{ products.filter(product => product?.name).map(product => product.name).join(', ') }}
                    </DetailsRow>
                    <DetailsRow label="Subsidy">
                        {{ formatCurrency(payment.subsidy) }}
                    </DetailsRow>
                    <DetailsRow
                        label="Monthly payment">
                        {{ formatCurrency(payment.monthly_payment) }}
                    </DetailsRow>
                    <DetailsRow v-if="payment.apr"
                                label="APR">
                        {{ payment.apr }}%
                    </DetailsRow>
                    <DetailsRow label="Total charge for credit">
                        {{ formatCurrency(payment.total_payable - payment.amount) }}
                    </DetailsRow>
                    <DetailsRow label="Total amount repayable">
                        {{ formatCurrency(payment.total_payable) }}
                    </DetailsRow>
                </div>
                <div class="w-1/2 max-md:w-full">
                    <DetailsRow label="Application submitted date">
                        <span v-if="payment.submitted_at">{{ moment(payment.submitted_at).format('DD/MM/YYYY') }}</span>
                    </DetailsRow>
                    <DetailsRow label="Agreement signed">
                        <span v-if="payment.signed_at">Yes</span>
                        <span v-else>No</span>
                    </DetailsRow>
                    <DetailsRow label="Sat note signed">
                        <span v-if="payment.sat_note_file_id">Yes</span>
                        <span v-else>No</span>
                    </DetailsRow>
                    <DetailsRow label="Offer expiry date">
                        <span v-if="payment.offer_expiration_date">{{ moment(payment.offer_expiration_date).format('DD/MM/YYYY') }}</span>
                    </DetailsRow>
                    <DetailsRow label="Agreement signed date">
                        <span v-if="payment.signed_at">{{ moment(payment.signed_at).format('DD/MM/YYYY') }}</span>
                    </DetailsRow>
                    <DetailsRow label="Cancellation expiry date">
                        <span v-if="payment.decision_received_at && payment.payment_status.identifier !== 'declined'">
                            {{ moment(payment.decision_received_at).add(12, 'days').format('DD/MM/YYYY') }}
                        </span>
                    </DetailsRow>
                </div>
            </div>
        </div>
    </Card>
</template>

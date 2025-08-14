<script setup>
import { formatCurrency } from '../../Helpers/Currency.js';
import moment from 'moment';
import DetailsRow from './DetailsRow.vue';
import Card from '../Card.vue';
import { Icon } from '@iconify/vue';

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
                    <DetailsRow
                        label="Advance amount"
                        :value="formatCurrency(payment.amount)"
                    />
                    <DetailsRow
                        label="Term (months)"
                        :value="String(payment.term)"
                    />
                    <DetailsRow
                        label="Cash price"
                        :value="formatCurrency(payment.amount)"
                    />
                    <DetailsRow
                        label="Deposit"
                        :value="formatCurrency(payment.deposit)"
                    />
                    <DetailsRow
                        label="Goods"
                        :value="products.filter(product => product?.name).map(product => product.name).join(', ')"
                    />
                    <DetailsRow
                        label="Subsidy"
                        :value="formatCurrency(payment.subsidy)"
                    />
                    <DetailsRow
                        label="Monthly repayment"
                        :value="formatCurrency(payment.monthly_payment)"
                    />
                    <DetailsRow
                        label="Monthly interest rate"
                        :value="payment.apr ? String(Math.round(payment.apr / 12, 7)) + '&percnt;' : ''"
                    />
                    <DetailsRow
                        v-if="payment.apr"
                        label="APR"
                        :value="String(payment.apr) + '&percnt;'" />
                    <DetailsRow
                        label="Total charge for credit"
                        :value="formatCurrency(payment.total_payable - payment.amount)"
                    />
                    <DetailsRow
                        label="Total amount repayable"
                        :value="formatCurrency(payment.total_payable)"
                    />
                </div>
                <div class="w-1/2 max-md:w-full">
                    <DetailsRow
                        label="Application submitted date"
                        :value="payment.submitted_at ? moment(payment.submitted_at).format('DD/MM/YYYY') : ''"
                    />
                    <DetailsRow
                        label="Agreement signed"
                        :value="payment.signed_at ? 'Yes' : 'No'"
                    />
                    <DetailsRow
                        label="Sat note signed"
                        :value="payment.sat_note_file_id ? 'Yes' : 'No'"
                    />
                    <DetailsRow
                        label="Offer expiry date"
                        :value="payment.offer_expiration_date ? moment(payment.offer_expiration_date).format('DD/MM/YYYY') : ''"
                    />
                    <DetailsRow
                        label="Agreement signed date"
                        :value="payment.signed_at ? moment(payment.signed_at).format('DD/MM/YYYY') : ''"
                    />
                    <DetailsRow
                        label="Cancellation expiry date"
                        :value="payment.decision_received_at && payment.payment_status.identifier !== 'declined' ?
                            moment(payment.decision_received_at).add(12, 'days').format('DD/MM/YYYY') : ''"
                    />
                </div>
            </div>
        </div>
    </Card>
</template>

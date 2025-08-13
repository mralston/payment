<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import moment from 'moment';
import Summary from '../../Components/Details/Summary.vue';
import Loan from '../../Components/Details/Loan.vue';
import Applicant from '../../Components/Details/Applicant.vue';
import Employment from '../../Components/Details/Employment.vue';
import Income from '../../Components/Details/Income.vue';
import Address from '../../Components/Details/Address.vue';
import BankAccount from '../../Components/Details/BankAccount.vue';
import MarketingConsent from '../../Components/Details/MarketingConsent.vue';
import Banner from '../../Components/Details/Banner.vue';

const props = defineProps({
    payment: Object,
    products: Array,
});

const payment = ref(props.payment);

</script>

<template>
    <Head>
        <title>Payment</title>
    </Head>

    <div class="flex max-w-7xl mx-auto max-md:flex-col text-sm p-10 gap-4">
        <div class="w-1/4 max-md:w-full">
            <Summary
                :payment="payment"
            />

        </div>
        <div class="w-3/4 max-md:w-full flex flex-col gap-4">

            <Banner :type="payment.payment_status.identifier === 'cancelled' ? 'error' : 'success'">
                <div v-if="payment.payment_status.identifier === 'cancelled'">
                    Payment cancelled
                    <div v-for="cancellation in payment.payment_cancellations" :key="cancellation.id">
                        {{ moment(cancellation.created_at).format('DD/MM/YYYY') }} - {{ cancellation.reason }}
                    </div>
                </div>
                <span v-else-if="payment.signed_at">Agreement signed on: <span class="font-bold">{{ moment(payment.signed_at).format('DD/MM/YYYY') }}</span></span>
                <span v-else>Agreement not signed</span>
            </Banner>
            
            <Loan
                :payment="payment"
                :products="products"/>

            <Applicant
                :payment="payment"/>

            <Employment
                :payment="payment"/>

            <Income
                :payment="payment"/>

            <Address
                :payment="payment"/>

            <BankAccount
                :payment="payment"/>

            <MarketingConsent
                :payment="payment"/>

        </div>
    </div>

</template>

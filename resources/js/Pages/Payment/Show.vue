<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import BreadCrumbs from '../../Components/BreadCrumbs.vue';
import Summary from '../../Components/Details/Summary.vue';
import Loan from '../../Components/Details/Loan.vue';
import Applicant from '../../Components/Details/Applicant.vue';
import Employment from '../../Components/Details/Employment.vue';
import Income from '../../Components/Details/Income.vue';
import Address from '../../Components/Details/Address.vue';
import BankAccount from '../../Components/Details/BankAccount.vue';
import MarketingConsent from '../../Components/Details/MarketingConsent.vue';
import DetailsBanner from '../../Components/Details/DetailsBanner.vue';

const props = defineProps({
    payment: Object,
    products: Array,
});

const payment = ref(props.payment);

const crumbs = ref([
    {
        name: 'Payments',
        href: route('payments.index'),
    },
    {
        name: payment.value?.reference,
        href: payment.value?.id ? route('payments.show', payment.value.id) : '#',
    },
]);
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
            <div class="flex flex-row gap-4">
                <BreadCrumbs :crumbs="crumbs" />
            </div>

            <DetailsBanner
                :payment="payment"
            />
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

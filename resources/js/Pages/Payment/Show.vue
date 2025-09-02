<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
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
import { Icon } from '@iconify/vue';
import { PaymentProvider } from '../../Enums/PaymentProvider';

const props = defineProps({
    payment: Object,
    products: Array,
});

const crumbs = ref([
    {
        name: 'Payments',
        href: route('payments.index'),
    },
    {
        name: props.payment.reference,
        href: props.payment.id ? route('payments.show', props.payment.id) : '#',
    },
]);

const view = ref('view 1');

const layout = {
    'view 1' : {
        container : 'flex flex-col gap-4',
    },
    'view 2' : {
        container : 'grid grid-cols-2 gap-4',
    },
}

const paymentType = computed(() => {
    if (props.payment.payment_provider.identifier === PaymentProvider.HOMETREE) {
        return 'Lease';
    }
    return 'Loan';
});

</script>

<template>
    <Head>
        <title>Payment</title>
    </Head>

    <div class="flex max-w-8xl mx-auto max-md:flex-col text-sm px-10 gap-4 justify-between items-center">
        <BreadCrumbs :crumbs="crumbs" />
        <button
            @click="view = view === 'view 1' ? 'view 2' : 'view 1'"
            class="text-blue-500 text-md"
        >
            <Icon icon="fa6-solid:window-maximize" class="text-2xl" />
        </button>
    </div>

    <div class="flex max-w-8xl mx-auto max-md:flex-col text-sm p-10 gap-4">
        <div class="w-1/4 max-md:w-full">
            <Summary
                :payment="payment"
                :paymentType="paymentType"
            />

        </div>
        <div class="w-3/4 max-md:w-full flex flex-col gap-4">
            <DetailsBanner
                :payment="payment"/>

            <div :class="layout[view].container">
                <Loan
                    :payment="payment"
                    :paymentType="paymentType"
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
    </div>

</template>

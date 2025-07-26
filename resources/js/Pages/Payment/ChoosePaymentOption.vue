<script setup>
import {Head, router} from "@inertiajs/vue3";
import OptionColumns from "../../Components/OptionColumns/OptionColumns.vue";
import {onMounted, ref} from "vue";
import axios from "axios";
import { ArrowPathIcon } from '@heroicons/vue/24/outline';
import { useEcho } from '@laravel/echo-vue';

const props = defineProps({
    parentModel: Object,
    survey: Object,
    customers: Array,
});

const prequalRunning = ref(false);

const columns = [
    {
        name: 'Cash',
        id: 'tier-cash',
        href: '#',
        price: { total: props.parentModel.total_cost },
        description: 'Hey moneybags! Pay for it all up front! That\'s right, you\'ve got all the money in the world.',
        features: [
            'Expensive up front',
            'No monthly outgoings',
            'You own it on day one',
        ],
    },
    {
        name: 'Finance',
        id: 'tier-finance',
        href: '#',
        price: { monthly: 180 },
        options: [
            {
                label: 'Tandem 9.9% 60 months',
                value: 'tandem-9.9-60',
            },
            {
                label: 'Tandem 9.9% 120 months',
                value: 'tandem-9.9-120',
            },
            {
                label: 'Tandem 9.9% 180 months',
                value: 'tandem-9.9-180',
                default: true,
            },
            {
                label: 'Tandem 9.9% 240 months',
                value: 'tandem-9.9-240',
            },
            {
                label: 'Propensio 10.9% 60 months',
                value: 'propensio-10.9-60',
            },
            {
                label: 'Propensio 10.9% 120 months',
                value: 'propensio-10.9-120',
            },
            {
                label: 'Propensio 10.9% 180 months',
                value: 'propensio-10.9-180',
                default: true,
            },
            {
                label: 'Propensio 10.9% 240 months',
                value: 'propensio-10.9-240',
            },
        ],
        description: 'I\'m doing all right, but I think I\'d like to pay monthly.',
        features: [
            'The system will be yours...',
            '...when you finish paying',
            'Probably what I\'d go for',
        ],
    },
    {
        name: 'Lease',
        id: 'tier-lease',
        href: '#',
        price: { monthly: 98 },
        options: [
            {
                label: '30 years',
                value: 'hometree-' + 30 * 12,
                default: true,
            },
            {
                label: '25 years',
                value: 'hometree-' + 25 * 12,
            },
            {
                label: '20 years',
                value: 'hometree-' + 20 * 12,
            },
            {
                label: '15 years',
                value: 'hometree-' + 15 * 12,
            },
            {
                label: '10 years',
                value: 'hometree-' + 10 * 12,
            },
            {
                label: '5 years',
                value: 'hometree-' + 5 * 12,
            },
        ],
        description: 'Woah! Hold on there! I\'m not made of money you know.',
        features: [
            'Cheap as chips',
            'We\'ll even give it to you...',
            '...in 25 years time',
        ],
    },
];

onMounted(() => {
    initiatePrequal();
});

useEcho(
    `offers.${props.survey.id}`,
    '\\Mralston\\Payment\\Events\\OfferReceived',
    (e) => {
        console.log(e);
    }
)

function initiatePrequal()
{
    prequalRunning.value = true;

    axios.post(route('payment.prequal', {parent: props.parentModel}))
        .then(response => {
            //prequalRunning.value = false;
        })
        .catch(error => {
            //prequalRunning.value = false;
            //alert('There was a problem running starting prequalification process.')
        });
}

function proceed(e) {
    alert('Proceed with ' + e.column.name + ' ' + (e.option ?? ''));
}

function skip()
{
    router.get(route('payment.choose-payment-option', {parent: props.parentModel}));
}

</script>

<template>

    <Head>
        <title>Payment Options</title>
    </Head>

    <div class="p-4">
<button @click="initiatePrequal">prequal</button>
        <h1 class="text-4xl font-bold">
            Payment Options
            <ArrowPathIcon v-if="prequalRunning" class="animate-spin h-8 w-8 text-black inline" />
        </h1>

        <p class="mt-4">It's make your mind up time. Are you going with what's behind door number one, door number two or door number three?</p>

        <OptionColumns :columns="columns" @proceed="proceed"/>

        <div class="my-4">
            <button type="button"
                    class="rounded-md bg-gray-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600"
                    @click="survey">
                Back to Survey
            </button>
        </div>

    </div>

</template>

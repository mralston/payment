<script setup>
import { CheckIcon, MinusIcon, PlusIcon } from '@heroicons/vue/16/solid';
import { Tab, TabGroup, TabList, TabPanel, TabPanels } from '@headlessui/vue';
import {formatCurrency} from "../../Helpers/Currency.js";
import {Head} from "@inertiajs/vue3";

const props = defineProps({
    parentModel: Object,
    customers: Array,
});

const tiers = [
    {
        name: 'Cash',
        description: 'Hey moneybags! Pay for it all up front! That\'s right, you\'ve got all the money in the world.',
        totalPrice: props.parentModel.total_cost,
        href: '#',
        highlights: [
            { description: 'Expensive up front' },
            { description: 'No monthly outgoings' },
            { description: 'You own it on day one' },
        ],
    },
    {
        name: 'Finance',
        description: 'I\'m doing all right, but I think I\'d like to pay monthly.',
        priceMonthly: 180,
        href: '#',
        highlights: [
            { description: 'The system will be yours...' },
            { description: '...when you finish paying' },
            { description: 'Probably what I\'d go for' },
        ],
    },
    {
        name: 'Lease',
        description: 'Woah! Hold on there! I\'m not made of money you know.',
        priceMonthly: 98,
        href: '#',
        highlights: [
            { description: 'Cheap as chips' },
            { description: 'Likely pays for itself' },
            { description: 'We\'ll even give it to you...' },
            { description: '...in 25 years time' },
        ],
    },
]
const sections = [
    {
        name: 'Features',
        features: [
            { name: 'Edge content delivery', tiers: { Starter: true, Growth: true, Scale: true } },
            { name: 'Custom domains', tiers: { Starter: '1', Growth: '3', Scale: 'Unlimited' } },
            { name: 'Team members', tiers: { Starter: '3', Growth: '20', Scale: 'Unlimited' } },
            { name: 'Single sign-on (SSO)', tiers: { Starter: false, Growth: false, Scale: true } },
        ],
    },
    {
        name: 'Reporting',
        features: [
            { name: 'Advanced analytics', tiers: { Starter: true, Growth: true, Scale: true } },
            { name: 'Basic reports', tiers: { Starter: false, Growth: true, Scale: true } },
            { name: 'Professional reports', tiers: { Starter: false, Growth: false, Scale: true } },
            { name: 'Custom report builder', tiers: { Starter: false, Growth: false, Scale: true } },
        ],
    },
    {
        name: 'Support',
        features: [
            { name: '24/7 online support', tiers: { Starter: true, Growth: true, Scale: true } },
            { name: 'Quarterly workshops', tiers: { Starter: false, Growth: true, Scale: true } },
            { name: 'Priority phone support', tiers: { Starter: false, Growth: false, Scale: true } },
            { name: '1:1 onboarding tour', tiers: { Starter: false, Growth: false, Scale: true } },
        ],
    },
];
</script>

<template>

    <Head>
        <title>Payment Options</title>
    </Head>

    <div class="p-4">

        <h1 class="text-4xl font-bold">Payment Options</h1>

        <p class="mt-4">It's make your mind up time. Are you going with what's behind door number one, door number two or door number three?</p>

        <div class="relative mt-8 mb-8">
            <div class="absolute inset-x-0 bottom-0 top-48 " />
            <div class="relative mx-auto max-w-2xl px-6 lg:max-w-7xl lg:px-8">
                <div class="grid grid-cols-1 gap-10 lg:grid-cols-3">
                    <div v-for="tier in tiers" :key="tier.name" class="-m-2 grid grid-cols-1 rounded-[2rem] shadow-[inset_0_0_2px_1px_#ffffff4d] ring-1 ring-black/5 max-lg:mx-auto max-lg:w-full max-lg:max-w-md">
                        <div class="grid grid-cols-1 rounded-[2rem] p-2 shadow-md shadow-black/5">
                            <div class="rounded-3xl bg-white p-10 pb-9 shadow-2xl ring-1 ring-black/5">
                                <h2 class="text-sm font-semibold text-blue-600">{{ tier.name }} <span class="sr-only">plan</span></h2>
                                <p class="mt-2 text-pretty text-sm/6 text-gray-600">{{ tier.description }}</p>

                                <div class="mt-8 flex items-center gap-4">
                                    <div class="text-5xl font-semibold text-gray-950">{{ formatCurrency(tier.totalPrice ?? tier.priceMonthly) }}</div>
                                    <div class="text-sm text-gray-600">
                                        <p v-if="tier.priceMonthly">per month</p>
                                    </div>
                                </div>

                                <div class="mt-8">
                                    <h3 class="text-sm/6 font-medium text-gray-950">Highlights:</h3>
                                    <ul class="mt-3 space-y-3">
                                        <li v-for="highlight in tier.highlights" :key="highlight.description" :data-disabled="highlight.disabled" class="group flex items-start gap-4 text-sm/6 text-gray-600 data-[disabled]:text-gray-400">
                                            <span class="inline-flex h-6 items-center">
                                                <PlusIcon class="size-4 fill-gray-400 group-data-[disabled]:fill-gray-300" aria-hidden="true" />
                                            </span>
                                            <span v-if="highlight.disabled" class="sr-only">Not included:</span>
                                            {{ highlight.description }}
                                        </li>
                                    </ul>
                                </div>

                                <div class="mt-8 text-center">
                                    <a :href="tier.href" :aria-label="`Start a free trial on the ${tier.name} plan`" class="inline-block rounded-md bg-blue-600 px-3.5 py-2 text-center text-sm/6 font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                        Continue
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</template>

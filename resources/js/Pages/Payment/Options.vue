<script setup>
import {Head, router} from "@inertiajs/vue3";
import {computed, onMounted, reactive, ref, watch} from "vue";
import axios from "axios";
import { ArrowPathIcon } from '@heroicons/vue/24/outline';
import { useEcho } from '@laravel/echo-vue';
import {formatCurrency} from "../../Helpers/Currency.js";
import {CheckCircleIcon, ExclamationTriangleIcon} from "@heroicons/vue/20/solid/index.js";
import MoreInfoModal from "../../Components/MoreInfoModal.vue";

import CashInfo from "../Cash/Create.vue";

const props = defineProps({
    parentModel: Object,
    survey: Object,
    customers: Array,
    totalCost: Number,
    deposit: Number,
    prequalOnLoad: {
        type: Boolean,
        default: false,
    },
    showManualPrequalButton: {
        type: Boolean,
        default: true,
    }
});

const cashMoreInfoModal = ref(null);
const financeMoreInfoModal = ref(null);
const leaseMoreInfoModal = ref(null);
const prequalErrorsModal = ref(null);

const prequalRunning = ref(false);

const pendingGateways = ref([]);

const gatewayErrors = reactive({
    finance: null,
    lease: null,
});

const currentErrorsToDisplay = ref([]);

const offers = ref([]);

const financeOffers = computed (() => {
    return offers.value.filter((offer) => offer.type === 'finance');
});

const leaseOffers = computed (() => {
    return offers.value.filter((offer) => offer.type === 'lease');
});

const lowestFinanceOffer = computed(() => {
    if (financeOffers.value.length === 0) {
        return null; // Return null if no finance offers exist
    }

    return financeOffers.value.reduce((minOffer, currentOffer) => {
        const minPayment = parseFloat(minOffer.monthly_payment);
        const currentPayment = parseFloat(currentOffer.monthly_payment);
        return currentPayment < minPayment ? currentOffer : minOffer;
    });
});

const lowestLeaseOffer = computed(() => {
    if (leaseOffers.value.length === 0) {
        return null; // Return null if no lease offers exist
    }

    return leaseOffers.value.reduce((minOffer, currentOffer) => {
        const minPayment = parseFloat(minOffer.monthly_payment);
        const currentPayment = parseFloat(currentOffer.monthly_payment);
        return currentPayment < minPayment ? currentOffer : minOffer;
    });
});

const lowestFinanceMonthlyRepayment = computed(() => {
    return lowestFinanceOffer.value?.monthly_payment ?? 0;
});

const lowestLeaseMonthlyRepayment = computed(() => {
    return lowestLeaseOffer.value?.monthly_payment ?? 0;
});

const selectedFinanceOffer = ref(null);
const selectedLeaseOffer = ref(null);

onMounted(() => {
    if (props.prequalOnLoad) {
        initiatePrequal();
    }
});

watch(pendingGateways, () => {
    // Auto select lowest offers when all gateways have responded
    if (pendingGateways.value.length === 0 && offers.value.length > 0) {
        if (selectedFinanceOffer.value == null && lowestFinanceOffer.value != null) {
            selectedFinanceOffer.value = lowestFinanceOffer.value;
        }

        if (selectedLeaseOffer.value == null && lowestLeaseOffer.value != null) {
            selectedLeaseOffer.value = lowestLeaseOffer.value;
        }
    }
});

useEcho(
    `offers.${props.survey.id}`,
    'OffersReceived',
    (e) => {
        // Remove gateways that have replied from the pendingGateways array
        pendingGateways.value = pendingGateways.value.filter((offerPromise) => offerPromise === e.gateway);

        // Add the offers to the dropdown lists
        offerLoop: for (const offer of e.offers) {
            // Skip
            for (let i = 0; i < offers.value.length; i++) {
                if (offer.id == offers.value[i].id) {
                    continue offerLoop;
                }
            }

            offers.value.push(offer);
        }
    }
);

useEcho(
    `offers.${props.survey.id}`,
    'PrequalError',
    (e) => {
        // Remove gateways that have replied from the pendingGateways array
        pendingGateways.value = pendingGateways.value.filter((offerPromise) => offerPromise === e.gateway);

        // Store the error
        gatewayErrors[e.type] = e;
        console.log(e);
    }
);

function initiatePrequal()
{
    offers.value = [];
    selectedFinanceOffer.value = null;
    selectedLeaseOffer.value = null;
    gatewayErrors.finance = null;
    gatewayErrors.lease = null;

    axios.post(route('payment.prequal', {parent: props.parentModel}))
        .then(response => {
            // Populate pendingGateway array with those that have promised offers
            for (const offerPromise of response.data) {
                pendingGateways.value.push(offerPromise.gateway);
            }
        })
        .catch(error => {
            alert('There was a problem running starting prequalification process.')
        });
}

function proceed(paymentType) {
    router.get(route('payment.' + paymentType + '.create', {
        parent: props.parentModel,
        offer: selectedFinanceOffer.value ?? selectedLeaseOffer.value
    }));
}

function survey()
{
    router.get(route('payment.surveys.create', {parent: props.parentModel}));
}

function showAlert(message)
{
    alert(message);
}

const parseGatewayErrors = computed(() => (gatewayType) => {
    const errorData = gatewayErrors[gatewayType]?.response;

    if (!errorData) {
        return [gatewayErrors[gatewayType]?.errorMessage ?? 'There was a problem with the prequalification process.'];
    }

    try {
        const errors = JSON.parse(errorData);
        let messages = [];
        for (const key in errors) {
            if (Object.hasOwnProperty.call(errors, key)) {
                messages = messages.concat(errors[key]);
            }
        }
        return messages;
    } catch (e) {
        console.error(`Error parsing JSON string for ${gatewayType}:`, e);
        return [`An error occurred while processing ${gatewayType} messages.`];
    }
});

function showPrequalErrorsModal(gatewayType) {
    currentErrorsToDisplay.value = parseGatewayErrors.value(gatewayType);
    prequalErrorsModal.value.show();
}



</script>

<template>

    <Head>
        <title>Payment Options</title>
    </Head>

    <MoreInfoModal ref="cashMoreInfoModal" title="Cash">
        <CashInfo :totalCost="totalCost" :deposit="deposit" :minimal="true" />
    </MoreInfoModal>

    <MoreInfoModal ref="financeMoreInfoModal" title="Finance">
        <table class="table table-bordered table-striped mb-0"><thead><tr><th>Yr</th> <th>Acc. grand total</th> <th>Savings</th> <th>Potential monthly repayment diff.</th></tr></thead> <tbody><tr><td>1</td> <td>£776.25</td> <td>£64.69</td> <td class="alert-danger">-£71.52</td></tr><tr><td>2</td> <td>£824.10</td> <td>£68.68</td> <td class="alert-danger">-£67.53</td></tr><tr><td>3</td> <td>£875.25</td> <td>£72.94</td> <td class="alert-danger">-£63.27</td></tr><tr><td>4</td> <td>£929.91</td> <td>£77.49</td> <td class="alert-danger">-£58.72</td></tr><tr><td>5</td> <td>£988.35</td> <td>£82.36</td> <td class="alert-danger">-£53.85</td></tr><tr><td>6</td> <td>£1,050.85</td> <td>£87.57</td> <td class="alert-danger">-£48.64</td></tr><tr><td>7</td> <td>£1,117.69</td> <td>£93.14</td> <td class="alert-danger">-£43.07</td></tr><tr><td>8</td> <td>£1,189.19</td> <td>£99.10</td> <td class="alert-danger">-£37.11</td></tr><tr><td>9</td> <td>£1,265.69</td> <td>£105.47</td> <td class="alert-danger">-£30.74</td></tr><tr><td>10</td> <td>£1,347.54</td> <td>£112.30</td> <td class="alert-danger">-£23.91</td></tr></tbody></table>
    </MoreInfoModal>

    <MoreInfoModal ref="leaseMoreInfoModal" title="Lease">
        <video src="https://media.projectbetterenergy.com/projectsolaruk/hometree2.mp4" class="w-full" controls="" poster="/img/hometree-lease-video.jpg"></video>
        <table class="table table-bordered table-striped mb-0"><thead><tr><th>Yr</th> <th>Acc. grand total</th> <th>Savings</th> <th>Potential monthly repayment diff.</th></tr></thead> <tbody><tr><td>1</td> <td>£776.25</td> <td>£64.69</td> <td class="alert-danger">-£71.52</td></tr><tr><td>2</td> <td>£824.10</td> <td>£68.68</td> <td class="alert-danger">-£67.53</td></tr><tr><td>3</td> <td>£875.25</td> <td>£72.94</td> <td class="alert-danger">-£63.27</td></tr><tr><td>4</td> <td>£929.91</td> <td>£77.49</td> <td class="alert-danger">-£58.72</td></tr><tr><td>5</td> <td>£988.35</td> <td>£82.36</td> <td class="alert-danger">-£53.85</td></tr><tr><td>6</td> <td>£1,050.85</td> <td>£87.57</td> <td class="alert-danger">-£48.64</td></tr><tr><td>7</td> <td>£1,117.69</td> <td>£93.14</td> <td class="alert-danger">-£43.07</td></tr><tr><td>8</td> <td>£1,189.19</td> <td>£99.10</td> <td class="alert-danger">-£37.11</td></tr><tr><td>9</td> <td>£1,265.69</td> <td>£105.47</td> <td class="alert-danger">-£30.74</td></tr><tr><td>10</td> <td>£1,347.54</td> <td>£112.30</td> <td class="alert-danger">-£23.91</td></tr></tbody></table>
    </MoreInfoModal>

    <MoreInfoModal ref="prequalErrorsModal" title="Error">
        <ul class="list-disc text-red-500 mx-6">
            <li v-for="(error, index) in currentErrorsToDisplay" :key="index">
                {{ error }}
            </li>
        </ul>
    </MoreInfoModal>

    <div class="p-4">

        <p class="mb-4 float-right">
            <div v-if="!prequalOnLoad" class="inline px-4 py-2 mr-2 rounded bg-white text-red-500 border-2 border-red-500 border-dashed">
                <ExclamationTriangleIcon class="text-red-500 h-6 w-6 inline mr-2" aria-hidden="true"/>
                Auto-Prequal OFF
            </div>

            <button v-if="showManualPrequalButton" @click="initiatePrequal" class="px-4 py-2 rounded bg-white hover:bg-gray-100 text-blue-500 border-2 border-blue-500 border-dashed">
                Run Prequal
            </button>
        </p>


        <h1 class="text-4xl font-bold">
            Payment Options
        </h1>

        <p class="mt-4">It's make your mind up time. Are you going with what's behind door number one, door number two or door number three?</p>

        <div class="bg-white py-16">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="flow-root">
                    <div class="isolate -mt-16 grid max-w-sm grid-cols-1 gap-y-16 divide-y divide-gray-100 sm:mx-auto lg:-mx-8 lg:mt-0 lg:max-w-none lg:grid-cols-3 lg:divide-x lg:divide-y-0 xl:-mx-4">

                        <!-- Cash -->
                        <div class="pt-16 lg:px-8 lg:pt-0 xl:px-14">
                            <h3 class="text-4xl font-semibold text-blue-800">Cash</h3>
                            <p class="mt-6 flex items-baseline gap-x-1">
                                <span class="text-5xl font-semibold tracking-tight text-gray-900">{{ formatCurrency(totalCost) }}</span>
                            </p>

                            <div class="mt-8 h-10">
                                <!-- Placeholder for dropdown -->
                            </div>

                            <button @click="proceed('cash')" class="mt-10 w-full rounded-md bg-blue-600 px-3 py-2 text-center text-sm/6 font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                Proceed
                            </button>

                            <button @click="cashMoreInfoModal.show" class="mt-4 w-full rounded-md bg-white border border-blue-500 px-3 py-2 text-center text-sm/6 font-semibold text-blue-500 shadow-sm hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                More Info
                            </button>

                            <p class="mt-10 text-sm/6 font-semibold text-gray-900">
                                Hey moneybags! Pay for it all up front! That's right, you've got all the money in the world.
                            </p>

                            <ul role="list" class="mt-6 space-y-3 text-sm/6 text-gray-600">
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    Expensive up front
                                </li>
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    No monthly outgoings
                                </li>
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    You own it on day one
                                </li>
                            </ul>
                        </div>

                        <!-- Finance -->
                        <div class="pt-16 lg:px-8 lg:pt-0 xl:px-14">
                            <h3 class="text-4xl font-semibold text-blue-800">Finance</h3>

                            <p class="mt-6 flex items-baseline gap-x-1 relative">
                                <span v-if="selectedFinanceOffer && selectedFinanceOffer?.id === lowestFinanceOffer?.id"
                                      class="absolute -top-4 -left-4 opacity-90 inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                    FROM ONLY
                                </span>

                                <span v-else-if="selectedFinanceOffer"
                                      class="absolute -top-4 -left-4 opacity-90 inline-flex items-center rounded-full bg-orange-50 px-2 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20">
                                    YOU PAY
                                </span>

                                <span class="text-5xl font-semibold tracking-tight text-gray-900">{{ formatCurrency(selectedFinanceOffer?.monthly_payment ?? 0) }}</span>
                                <span class="text-sm/6 font-semibold text-gray-600">/month</span>
                            </p>

                            <div class="mt-8 h-10">
                                <div v-if="pendingGateways.length > 0 && financeOffers.length === 0" class="text-center">
                                    <ArrowPathIcon  class="animate-spin h-10 w-10 text-black inline" />
                                </div>

                                <select v-if="financeOffers.length > 0"
                                        v-model="selectedFinanceOffer"
                                        class="w-full rounded-md bg-white py-1.5 pl-3 pr-8 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                                    <option v-for="offer in financeOffers" :key="offer.id" :value="offer">
                                        {{ offer.name }}
                                    </option>
                                </select>

                                <div v-if="gatewayErrors.finance" class="text-center">
                                    <ExclamationTriangleIcon class="h-10 w-10 text-red-500 inline cursor-pointer" @click="showPrequalErrorsModal('finance')"/>
                                </div>
                            </div>


                            <button @click="proceed('finance')" class="mt-10 w-full rounded-md bg-blue-600 px-3 py-2 text-center text-sm/6 font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                Proceed
                            </button>

                            <button @click="financeMoreInfoModal.show" class="mt-4 w-full rounded-md bg-white border border-blue-500 px-3 py-2 text-center text-sm/6 font-semibold text-blue-500 shadow-sm hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                More Info
                            </button>

                            <p class="mt-10 text-sm/6 font-semibold text-gray-900">
                                I'm doing all right, but I think I'd like to pay monthly.
                            </p>

                            <ul role="list" class="mt-6 space-y-3 text-sm/6 text-gray-600">
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    The system will be yours...
                                </li>
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    ...when you finish paying
                                </li>
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    Probably what I'd go for
                                </li>
                            </ul>
                        </div>

                        <!-- Lease -->
                        <div class="pt-16 lg:px-8 lg:pt-0 xl:px-14">
                            <h3 class="text-4xl font-semibold text-blue-800">Lease</h3>
                            <p class="mt-6 flex items-baseline gap-x-1">
                                <span v-if="selectedLeaseOffer && selectedLeaseOffer?.id === lowestLeaseOffer?.id"
                                      class="absolute -top-4 -left-4 opacity-90 inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                    FROM ONLY
                                </span>

                                <span v-else-if="selectedLeaseOffer"
                                      class="absolute -top-4 -left-4 opacity-90 inline-flex items-center rounded-full bg-orange-50 px-2 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/20">
                                    YOU PAY
                                </span>

                                <span class="text-5xl font-semibold tracking-tight text-gray-900">{{ formatCurrency(selectedLeaseOffer?.monthly_payment ?? 0) }}</span>
                                <span class="text-sm/6 font-semibold text-gray-600">/month</span>
                            </p>

                            <div class="mt-8 h-10">
                                <div v-if="pendingGateways.length > 0 && leaseOffers.length === 0" class="text-center">
                                    <ArrowPathIcon  class="animate-spin h-10 w-10 text-black inline" />
                                </div>

                                <select v-if="leaseOffers.length > 0"
                                        v-model="selectedLeaseOffer"
                                        class="w-full rounded-md bg-white py-1.5 pl-3 pr-8 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                                    <option v-for="offer in leaseOffers" :key="offer.id" :value="offer">
                                        {{ offer.name }}
                                    </option>
                                </select>

                                <div v-if="gatewayErrors.lease" class="text-center">
                                    <ExclamationTriangleIcon class="h-10 w-10 text-red-500 inline cursor-pointer" @click="showPrequalErrorsModal('lease')"/>
                                </div>
                            </div>

                            <button @click="proceed('lease')" class="mt-10 w-full rounded-md bg-blue-600 px-3 py-2 text-center text-sm/6 font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                Proceed
                            </button>

                            <button @click="leaseMoreInfoModal.show" class="mt-4 w-full rounded-md bg-white border border-blue-500 px-3 py-2 text-center text-sm/6 font-semibold text-blue-500 shadow-sm hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                More Info
                            </button>

                            <p class="mt-10 text-sm/6 font-semibold text-gray-900">
                                Woah! Hold on there! I'm not made of money you know.
                            </p>

                            <ul role="list" class="mt-6 space-y-3 text-sm/6 text-gray-600">
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    Cheap as chips
                                </li>
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    We'll even give it to you...
                                </li>
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    ...in 25 years time
                                </li>
                            </ul>
                        </div>


                    </div>
                </div>
            </div>
        </div>



        <div class="my-4">
            <button type="button"
                    class="rounded-md bg-gray-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600"
                    @click="survey">
                Back to Survey
            </button>
        </div>

    </div>

</template>

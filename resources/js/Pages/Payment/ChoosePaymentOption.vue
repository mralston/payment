<script setup>
import {Head, router} from "@inertiajs/vue3";
import {computed, onMounted, ref, watch} from "vue";
import axios from "axios";
import { ArrowPathIcon } from '@heroicons/vue/24/outline';
import { useEcho } from '@laravel/echo-vue';
import {formatCurrency} from "../../Helpers/Currency.js";
import {CheckCircleIcon} from "@heroicons/vue/20/solid/index.js";
import MoreInfoModal from "../../Components/MoreInfoModal.vue";

const props = defineProps({
    parentModel: Object,
    survey: Object,
    customers: Array,
});

const cashMoreInfo = ref(null);
const financeMoreInfo = ref(null);
const leaseMoreInfo = ref(null);

const prequalRunning = ref(false);

const pendingGateways = ref([]);

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
    initiatePrequal();
    // cashMoreInfo.value.show();
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
)

function initiatePrequal()
{
    offers.value = [];

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
    alert('Proceed with ' + paymentType);
}

function moreInfo(paymentType) {
    alert('More info on ' + paymentType);
}

function survey()
{
    router.get(route('payment.surveys.create', {parent: props.parentModel}));
}

</script>

<template>

    <Head>
        <title>Payment Options</title>
    </Head>

    <MoreInfoModal ref="cashMoreInfo" title="Cash">
        <p>Info about cash payment goes here...</p>
    </MoreInfoModal>

    <MoreInfoModal ref="financeMoreInfo" title="Finance">
        <p>Info about finance payment goes here...</p>
    </MoreInfoModal>

    <MoreInfoModal ref="leaseMoreInfo" title="Lease">
        <video src="https://media.projectbetterenergy.com/projectsolaruk/hometree2.mp4" class="w-full" controls="" poster="/img/hometree-lease-video.jpg"></video>
    </MoreInfoModal>

    <div class="p-4">

        <p class="mb-4 float-right">
            <button @click="initiatePrequal" class="px-4 py-2 rounded bg-white hover:bg-gray-100 text-blue-500 border-2 border-blue-500 border-dashed">
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
                                <span class="text-5xl font-semibold tracking-tight text-gray-900">{{ formatCurrency(parentModel.total_cost) }}</span>
                            </p>

                            <div class="mt-8 h-10">
                                <!-- Placeholder for dropdown -->
                            </div>

                            <button @click="proceed('cash')" class="mt-10 w-full rounded-md bg-blue-600 px-3 py-2 text-center text-sm/6 font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                Proceed
                            </button>

                            <button @click="cashMoreInfo.show" class="mt-4 w-full rounded-md bg-white border border-blue-500 px-3 py-2 text-center text-sm/6 font-semibold text-blue-500 shadow-sm hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
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
                            <p class="mt-6 flex items-baseline gap-x-1">
                                <span class="text-5xl font-semibold tracking-tight text-gray-900">{{ formatCurrency(lowestFinanceMonthlyRepayment) }}</span>
                                <span class="text-sm/6 font-semibold text-gray-600">/month</span>
                            </p>

                            <div class="mt-8 h-10">
                                <div v-if="pendingGateways.length > 0 && financeOffers.length === 0" class="text-center">
                                    <ArrowPathIcon  class="animate-spin h-10 w-10 text-black inline" />
                                </div>

                                <select v-if="financeOffers.length > 0"
                                        v-model="selectedFinanceOffer.id"
                                        class="w-full rounded-md bg-white py-1.5 pl-3 pr-8 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                                    <option v-for="offer in financeOffers" :key="offer.id" :value="offer.id">
                                        {{ offer.name }}
                                    </option>
                                </select>
                            </div>


                            <button @click="proceed('finance')" class="mt-10 w-full rounded-md bg-blue-600 px-3 py-2 text-center text-sm/6 font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                Proceed
                            </button>

                            <button @click="financeMoreInfo.show" class="mt-4 w-full rounded-md bg-white border border-blue-500 px-3 py-2 text-center text-sm/6 font-semibold text-blue-500 shadow-sm hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
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
                                <span class="text-5xl font-semibold tracking-tight text-gray-900">{{ formatCurrency(lowestLeaseMonthlyRepayment) }}</span>
                                <span class="text-sm/6 font-semibold text-gray-600">/month</span>
                            </p>

                            <div class="mt-8 h-10">
                                <div v-if="pendingGateways.length > 0 && leaseOffers.length === 0" class="text-center">
                                    <ArrowPathIcon  class="animate-spin h-10 w-10 text-black inline" />
                                </div>

                                <select v-if="leaseOffers.length > 0"
                                        v-model="selectedLeaseOffer.id"
                                        class="w-full rounded-md bg-white py-1.5 pl-3 pr-8 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                                    <option v-for="offer in financeOffers" :key="offer.id" :value="offer.id">
                                        {{ offer.name }}
                                    </option>
                                </select>
                            </div>

                            <button @click="proceed('lease')" class="mt-10 w-full rounded-md bg-blue-600 px-3 py-2 text-center text-sm/6 font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                Proceed
                            </button>

                            <button @click="leaseMoreInfo.show" class="mt-4 w-full rounded-md bg-white border border-blue-500 px-3 py-2 text-center text-sm/6 font-semibold text-blue-500 shadow-sm hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
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

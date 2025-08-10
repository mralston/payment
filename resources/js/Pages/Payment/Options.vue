<script setup>
import {Head, router} from "@inertiajs/vue3";
import {computed, onMounted, reactive, ref, watch} from "vue";
import axios from "axios";
import { useEcho } from '@laravel/echo-vue';
import {formatCurrency} from "../../Helpers/Currency.js";
import {CheckCircleIcon, ExclamationTriangleIcon} from "@heroicons/vue/20/solid/index.js";
import MoreInfoModal from "../../Components/MoreInfoModal.vue";

import LeaseMoreInfo from "../../Components/MoreInfo/Lease.vue";
import FinanceMoreInfo from "../../Components/MoreInfo/Finance.vue";
import CashMoreInfo from "../../Components/MoreInfo/Cash.vue";

import CashVsFinanceMoreInfo from "../../Components/MoreInfo/CashVsFinance.vue";
import FinanceVsLeaseMoreInfo from "../../Components/MoreInfo/FinanceVsLease.vue";
import PaymentOffersSelect from "../../Components/PaymentOffersSelect.vue";
import OfferStatusBadge from "../../Components/OfferStatusBadge.vue";
import BulletPointsSkeleton from "../../Components/BulletPointsSkeleton.vue";
import SkeletonItem from "../../Components/SkeletonItem.vue";
import {makeNumeric} from "../../Helpers/Number.js";
import {decompress} from "../../Helpers/Compression.js";

const props = defineProps({
    parentModel: Object,
    survey: Object,
    customers: Array,
    totalCost: Number,
    deposit: Number,
    leaseMoreInfoContent: String,
    paymentProviders: Array,
    systemSavings: Object,
    prequalOnLoad: {
        type: Boolean,
        default: true,
    },
});

const cashMoreInfoModal = ref(null);
const financeMoreInfoModal = ref(null);
const leaseMoreInfoModal = ref(null);
const prequalErrorsModal = ref(null);
const cashVsFinanceModal = ref(null);
const financeVsLeaseModal = ref(null);

const prequalRunning = ref(false);

const pendingGateways = ref([]);

const gatewayErrors = reactive({
    finance: null,
    lease: null,
});

const currentErrorsToDisplay = ref([]);

const offers = ref([]);

const financeOffers = computed (() => {
    return offers.value
        .filter((offer) => offer.type === 'finance')
        .sort(sortOffers);
});

const leaseOffers = computed (() => {
    return offers.value
        .filter((offer) => offer.type === 'lease')
        .sort(sortOffers);
});

function sortOffers(offers) {
    if (!Array.isArray(offers)) {
        return [];
    }

    return offers.sort((a, b) => {
        // Handle null priority values by treating them as a higher number
        const aPriority = a.priority ?? Number.MAX_SAFE_INTEGER;
        const bPriority = b.priority ?? Number.MAX_SAFE_INTEGER;

        // Sort by priority first
        if (aPriority !== bPriority) {
            return aPriority - bPriority;
        }

        // If priorities are the same, sort by monthly_payment
        if (parseFloat(a.monthly_payment) !== parseFloat(b.monthly_payment)) {
            return parseFloat(a.monthly_payment) - parseFloat(b.monthly_payment);
        }

        // If monthly payments are also the same, use upfront_payment as a tie-breaker
        return parseFloat(a.upfront_payment) - parseFloat(b.upfront_payment);
    });
}

/**
 * Finds the single best offer based on consumer-centric logic.
 *
 * The selection criteria is as follows:
 * 1. Strongly prefer offers with a zero upfront payment.
 * 2. Among the preferred offers, find the one with the absolute lowest monthly payment.
 * 3. The loan term is only used as a tiebreaker if two offers have the exact same monthly payment.
 * 4. If no zero-upfront offers exist, find the lowest monthly payment among all other valid offers.
 *
 * @param {Array} offers - The array of offers to evaluate.
 * @returns {Object|null} The best offer, or null if no valid offers exist.
 */
function getBestOffer(offers) {
    if (!offers || offers.length === 0) {
        return null;
    }

    const zeroUpfrontOffers = offers.filter(offer => parseFloat(offer.upfront_payment ?? 0) === 0);
    let bestOffer = null;

    // We start with the group of zero-upfront offers if it exists.
    const candidates = zeroUpfrontOffers.length > 0 ? zeroUpfrontOffers : offers;

    // Find the offer with the lowest monthly payment in the chosen group.
    if (candidates.length > 0) {
        bestOffer = candidates.reduce((best, current) => {
            const bestMonthly = parseFloat(best.monthly_payment);
            const currentMonthly = parseFloat(current.monthly_payment);

            // If current offer's monthly payment is lower, it becomes the new best.
            if (currentMonthly < bestMonthly) {
                return current;
            }

            // If monthly payments are identical, the shorter term wins.
            if (currentMonthly === bestMonthly && current.term < best.term) {
                return current;
            }

            // Otherwise, stick with the current best.
            return best;
        });
    }

    return bestOffer;
}

const bestFinanceOffer = computed(() => {
    return getBestOffer(financeOffers.value);
});

const bestLeaseOffer = computed(() => {
    return getBestOffer(leaseOffers.value);
});

const lowestFinanceMonthlyPayment = computed(() => {
    return lowestFinanceOffer.value?.monthly_payment ?? 0;
});

const lowestLeaseMonthlyPayment = computed(() => {
    return lowestLeaseOffer.value?.monthly_payment ?? 0;
});

const selectedFinanceOffer = ref(null);
const selectedLeaseOffer = ref(null);

onMounted(() => {
    if (props.survey.payment_offers.length > 0) {
        // Load offers from props (if present)
        offers.value = props.survey.payment_offers;
        selectBestOffers();
    } else if (props.prequalOnLoad) {
        // Otherwise fetch with AJAX
        initiatePrequal();
    }
});

watch(pendingGateways, () => {
    // Auto select best offers when all gateways have responded
    selectBestOffers();
});

function selectBestOffers()
{
    if (pendingGateways.value.length === 0 && offers.value.length > 0) {
        if (selectedFinanceOffer.value == null && bestFinanceOffer.value != null) {
            selectedFinanceOffer.value = bestFinanceOffer.value;
        }

        if (selectedLeaseOffer.value == null && bestLeaseOffer.value != null) {
            selectedLeaseOffer.value = bestLeaseOffer.value;
        }
    }
}

useEcho(
    `offers.${props.survey.id}`,
    'OffersReceived',
    updateOffers
);

useEcho(
    `offers.${props.survey.id}`,
    'OffersUpdated',
    updateOffers
);

useEcho(
    `offers.${props.survey.id}`,
    'PrequalError',
    (e) => {
        // Remove gateways that have replied from the pendingGateways array
        pendingGateways.value = pendingGateways.value.filter((offerPromise) => offerPromise !== e.gateway);

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
            alert('There was a problem running starting prequalification process.');
        });
}

function updateOffers(e) {
    e = decompress(e.payload);

    let financeSelectionInvalidated = false;
    let leaseSelectionInvalidated = false;

    // Process all incoming offers from the event
    for (const incomingOffer of e.offers) {
        const index = offers.value.findIndex(o => o.id === incomingOffer.id);

        if (incomingOffer.status === 'declined') {
            // Only proceed if the offer actually exists in our list
            if (index > -1) {
                // Check if the offer we're about to remove is currently selected.
                // If so, flag that we need to pick a new one.
                if (selectedFinanceOffer.value?.id === incomingOffer.id) {
                    financeSelectionInvalidated = true;
                }
                if (selectedLeaseOffer.value?.id === incomingOffer.id) {
                    leaseSelectionInvalidated = true;
                }

                // Remove the declined offer from the main array.
                offers.value.splice(index, 1);
            }
        } else {
            // If the offer is NOT declined, we either update it or add it.
            if (index > -1) {
                // The offer already exists, so update it in place.
                offers.value[index] = incomingOffer;
            } else {
                // This is a new offer, so add it to the array.
                offers.value.push(incomingOffer);
            }

            // If this offer is the currently selected one, ensure our `selected...Offer` ref
            // points to the new, updated object. This keeps statuses fresh.
            if (selectedFinanceOffer.value?.id === incomingOffer.id) {
                selectedFinanceOffer.value = incomingOffer;
            }
            if (selectedLeaseOffer.value?.id === incomingOffer.id) {
                selectedLeaseOffer.value = incomingOffer;
            }
        }
    }

    // After the loop, if a selection was invalidated, pick the new best offer.
    // The `best...Offer` computed properties have already recalculated automatically.
    if (financeSelectionInvalidated) {
        selectedFinanceOffer.value = bestFinanceOffer.value;
    }
    if (leaseSelectionInvalidated) {
        selectedLeaseOffer.value = bestLeaseOffer.value;
    }

    // Remove gateways that have replied from the pendingGateways array
    pendingGateways.value = pendingGateways.value.filter((gateway) => gateway !== e.gateway);
}

function proceed(paymentType) {
    let selectedOffer = null;

    if (paymentType === 'finance') {
        selectedOffer = selectedFinanceOffer.value;
    } else if (paymentType === 'lease') {
        selectedOffer = selectedLeaseOffer.value;
    }

    router.get(route('payment.' + paymentType + '.create', {
        parent: props.parentModel,
        offerId: selectedOffer?.id
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
        return [
            errorData,
        ];
    }
});

const selectedFinanceProvider = computed(() => {
    for (const paymentProvider of props.paymentProviders) {
        if (paymentProvider.id === selectedFinanceOffer.value?.payment_provider_id) {
            return paymentProvider;
        }
    }
});

const selectedLeaseProvider = computed(() => {
    for (const paymentProvider of props.paymentProviders) {
        if (paymentProvider.id === selectedLeaseOffer.value?.payment_provider_id) {
            return paymentProvider;
        }
    }
});

function showPrequalErrorsModal(gatewayType) {
    currentErrorsToDisplay.value = parseGatewayErrors.value(gatewayType);
    prequalErrorsModal.value.show();
}

function cashVsFinance()
{
    cashVsFinanceModal.value.show();
}

function financeVsLease()
{
    financeVsLeaseModal.value.show();
}


</script>

<template>

    <Head>
        <title>Payment Options</title>
    </Head>

    <MoreInfoModal ref="cashMoreInfoModal" title="More Info - Cash">
        <CashMoreInfo :totalCost="totalCost" :deposit="deposit"/>
    </MoreInfoModal>

    <MoreInfoModal ref="financeMoreInfoModal" title="More Info - Finance">
        <FinanceMoreInfo :totalCost="makeNumeric(totalCost)"
                         :deposit="makeNumeric(deposit)"
                         :selected-offer="selectedFinanceOffer"
                         :other-offers="financeOffers.filter(offer => offer.id !== selectedFinanceOffer.id && offer.payment_provider_id === selectedFinanceOffer.payment_provider_id)"
                         :system-savings="systemSavings"/>
    </MoreInfoModal>

    <MoreInfoModal ref="leaseMoreInfoModal" title="More Info - Lease">
        <LeaseMoreInfo :content="leaseMoreInfoContent"
                       :totalCost="makeNumeric(totalCost)"
                       :deposit="makeNumeric(deposit)"
                       :selected-offer="selectedLeaseOffer"
                       :other-offers="leaseOffers.filter(offer => offer.id !== selectedLeaseOffer.id)"
                       :system-savings="systemSavings"
        />
    </MoreInfoModal>

    <MoreInfoModal ref="cashVsFinanceModal" title="Cash vs Finance">
        <CashVsFinanceMoreInfo/>
    </MoreInfoModal>

    <MoreInfoModal ref="financeVsLeaseModal" title="Finance vs Lease">
        <FinanceVsLeaseMoreInfo/>
    </MoreInfoModal>

    <MoreInfoModal ref="prequalErrorsModal" title="Error">
        <ul class="list-disc text-red-500 mx-6">
            <li v-for="(error, index) in currentErrorsToDisplay" :key="index">
                {{ error }}
            </li>
        </ul>
    </MoreInfoModal>

    <div class="p-4">

        <div v-if="!prequalOnLoad" class="mb-4 float-right">
            <div class="inline px-4 py-2 mr-2 rounded bg-white text-red-500 border-2 border-red-500 border-dashed">
                <ExclamationTriangleIcon class="text-red-500 h-6 w-6 inline mr-2" aria-hidden="true"/>
                Auto-Prequal OFF
            </div>

            <button @click="initiatePrequal" class="px-4 py-2 rounded bg-white hover:bg-gray-100 text-blue-500 border-2 border-blue-500 border-dashed">
                Run Prequal
            </button>
        </div>


        <h1 class="text-4xl font-bold">
            Payment Options
        </h1>

        <div class="bg-white py-16">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="flow-root">
                    <div class="isolate -mt-16 grid max-w-sm grid-cols-1 gap-y-16 divide-y divide-gray-100 sm:mx-auto lg:-mx-8 lg:mt-0 lg:max-w-none lg:grid-cols-3 lg:divide-x lg:divide-y-0 xl:-mx-4">

                        <!-- Cash -->
                        <div class="pt-16 lg:px-8 lg:pt-0 xl:px-14 relative">

                            <button class="absolute z-10 -right-7 top-16 bg-blue-600 hover:bg-blue-500 text-white text-2xl font-bold p-3 rounded-full"
                                    @click="cashVsFinance"
                                    title="Compare Cash &amp; Finance">
                                VS
                            </button>

                            <h3 class="text-3xl font-semibold text-blue-800">Outright Purchase</h3>

                            <p class="mt-4 flex items-baseline gap-x-1">
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
                                Cash Purchase Option
                            </p>

                            <ul role="list" class="mt-6 space-y-3 text-sm/6 text-gray-600">
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    Deposit: 25% upfront (paid upon booking)
                                </li>
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    Balance: 75% on satisfactory completion
                                </li>
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    Ownership: Immediate
                                </li>
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    Interest: None
                                </li>
                            </ul>

                            <p class="mt-10 text-sm/6 font-semibold text-gray-900">
                                Why cash?
                            </p>

                            <ul role="list" class="mt-6 space-y-3 text-sm/6 text-gray-600">
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    Lowest overall cost — no interest or fees
                                </li>
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    Full system ownership from day one
                                </li>
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    No finance agreements or credit checks
                                </li>
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    Straightforward and clean transaction
                                </li>
                            </ul>

                            <p class="mt-10 text-sm/6 font-semibold text-gray-900">
                                Best for
                            </p>

                            <ul role="list" class="mt-6 space-y-3 text-sm/6 text-gray-600">
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    Customers with available savings
                                </li>
                                <li class="flex gap-x-3">
                                    <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                    Those focused on long-term value and maximum ROC
                                </li>
                            </ul>

                        </div>

                        <!-- Finance -->
                        <div class="pt-16 lg:px-8 lg:pt-0 xl:px-14 relative">

                            <div class="mb-4">
                                <img v-if="selectedFinanceProvider?.logo" :src="selectedFinanceProvider.logo" class="max-w-1/3 h-7" :alt="selectedFinanceProvider.name">
                                <h3 v-else-if="selectedFinanceProvider" class="text-3xl font-semibold text-blue-800">{{ selectedFinanceProvider.name }}</h3>
                                <h3 v-else-if="pendingGateways.length === 0" class="text-3xl font-semibold text-blue-800">Finance</h3>
                                <SkeletonItem v-else class="h-7 w-5/6"/>
                            </div>

                            <button class="absolute z-10 -right-7 top-16 bg-blue-600 hover:bg-blue-500 text-white text-2xl font-bold p-3 rounded-full"
                                    @click="financeVsLease"
                                    title="Compare Finance &amp; Lease">
                                VS
                            </button>

                            <div class="mt-8 h-10">
                                <SkeletonItem v-if="pendingGateways.length > 0 && financeOffers.length === 0" class="rounded-md h-10 w-3/4"/>
                                <p v-else class="mt-6 flex items-baseline gap-x-1 relative">
                                    <OfferStatusBadge :offer="selectedFinanceOffer" :best-offer="bestFinanceOffer"/>

                                    <span class="text-5xl font-semibold tracking-tight text-gray-900">{{ formatCurrency(selectedFinanceOffer?.monthly_payment ?? 0) }}</span>
                                    <span class="text-sm/6 font-semibold text-gray-600">/month</span>
                                </p>
                            </div>

                            <div class="mt-8 h-10">

                                <SkeletonItem v-if="pendingGateways.length > 0 && financeOffers.length === 0" class="mt-8 rounded-md h-10 w-full"/>

                                <PaymentOffersSelect v-if="financeOffers.length > 0" :offers="financeOffers" v-model="selectedFinanceOffer"/>

                                <div v-if="gatewayErrors.finance" class="text-center">
                                    <ExclamationTriangleIcon class="h-10 w-10 text-red-500 inline cursor-pointer" @click="showPrequalErrorsModal('finance')"/>
                                </div>
                            </div>

                            <button @click="proceed('finance')"
                                    :disabled="selectedFinanceOffer === null"
                                    class="mt-10 w-full rounded-md bg-blue-600 px-3 py-2 text-center text-sm/6 font-semibold text-white shadow-sm hover:bg-blue-500 disabled:bg-blue-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                Proceed
                            </button>

                            <button @click="financeMoreInfoModal.show"
                                    :disabled="selectedFinanceOffer === null"
                                    class="mt-4 w-full rounded-md bg-white border border-blue-500 px-3 py-2 text-center text-sm/6 font-semibold text-blue-500 shadow-sm hover:bg-gray-100 disabled:bg-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                More Info
                            </button>

                            <BulletPointsSkeleton v-if="pendingGateways.length > 0 && financeOffers.length === 0"/>
                            <BulletPointsSkeleton v-if="pendingGateways.length > 0 && financeOffers.length === 0"/>

                            <div v-if="selectedFinanceProvider?.selling_points"
                                 v-for="sellingPoint in selectedFinanceProvider.selling_points"
                                 :key="sellingPoint.title">
                                <p class="mt-10 text-sm/6 font-semibold text-gray-900">
                                    {{ sellingPoint.title }}
                                </p>
                                <ul role="list" class="mt-6 space-y-3 text-sm/6 text-gray-600">
                                    <li class="flex gap-x-3" v-for="bullet in sellingPoint.bullets" :key="bullet">
                                        <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                        {{ bullet }}
                                    </li>
                                </ul>
                            </div>

                        </div>

                        <!-- Lease -->
                        <div class="pt-16 lg:px-8 lg:pt-0 xl:px-14 relative">

                            <div class="mb-4">
                                <img v-if="selectedLeaseProvider?.logo" :src="selectedLeaseProvider.logo" class="max-w-1/3 h-7" :alt="selectedLeaseProvider.name">
                                <h3 v-else-if="selectedLeaseProvider" class="text-3xl font-semibold text-blue-800">{{ selectedLeaseProvider.name }}</h3>
                                <h3 v-else-if="pendingGateways.length === 0" class="text-3xl font-semibold text-blue-800">Lease Purchase</h3>
                                <SkeletonItem v-else class="h-7 w-5/6"/>
                            </div>

                            <div class="mt-8 h-10">
                                <SkeletonItem v-if="pendingGateways.length > 0 && financeOffers.length === 0" class="rounded-md h-10 w-3/4"/>
                                <p v-else class="mt-6 flex items-baseline gap-x-1 relative">
                                    <OfferStatusBadge :offer="selectedLeaseOffer" :best-offer="bestLeaseOffer"/>

                                    <span class="text-5xl font-semibold tracking-tight text-gray-900">{{ formatCurrency(selectedLeaseOffer?.monthly_payment ?? 0) }}</span>
                                    <span class="text-sm/6 font-semibold text-gray-600">/month</span>
                                </p>
                            </div>

                            <div class="mt-8 h-10">
                                <SkeletonItem v-if="pendingGateways.length > 0 && leaseOffers.length === 0" class="mt-8 rounded-md h-10 w-full"/>

                                <PaymentOffersSelect v-if="leaseOffers.length > 0" :offers="leaseOffers" v-model="selectedLeaseOffer"/>

                                <div v-if="gatewayErrors.lease" class="text-center">
                                    <ExclamationTriangleIcon class="h-10 w-10 text-red-500 inline cursor-pointer" @click="showPrequalErrorsModal('lease')"/>
                                </div>
                            </div>

                            <button @click="proceed('lease')"
                                    :disabled="selectedFinanceOffer === null"
                                    class="mt-10 w-full rounded-md bg-blue-600 px-3 py-2 text-center text-sm/6 font-semibold text-white shadow-sm hover:bg-blue-500 disabled:bg-blue-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                Proceed
                            </button>

                            <button @click="leaseMoreInfoModal.show"
                                    :disabled="selectedFinanceOffer === null"
                                    class="mt-4 w-full rounded-md bg-white border border-blue-500 px-3 py-2 text-center text-sm/6 font-semibold text-blue-500 shadow-sm hover:bg-gray-100 disabled:bg-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                More Info
                            </button>

                            <BulletPointsSkeleton v-if="pendingGateways.length > 0 && financeOffers.length === 0"/>
                            <BulletPointsSkeleton v-if="pendingGateways.length > 0 && financeOffers.length === 0"/>

                            <div v-if="selectedLeaseProvider?.selling_points"
                                 v-for="sellingPoint in selectedLeaseProvider.selling_points"
                                 :key="sellingPoint.title">
                                <p class="mt-10 text-sm/6 font-semibold text-gray-900">
                                    {{ sellingPoint.title }}
                                </p>
                                <ul role="list" class="mt-6 space-y-3 text-sm/6 text-gray-600">
                                    <li class="flex gap-x-3" v-for="bullet in sellingPoint.bullets" :key="bullet">
                                        <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                                        {{ bullet }}
                                    </li>
                                </ul>
                            </div>

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

<style scoped>
INPUT, SELECT, TEXTAREA
{
    border-color: inherit;
}
</style>

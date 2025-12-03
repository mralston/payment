<script setup>

import {Head, router} from "@inertiajs/vue3";
import {formatCurrency} from "../../Helpers/Currency.js";
import {ArrowPathIcon} from "@heroicons/vue/24/outline/index.js";
import {CheckCircleIcon} from "@heroicons/vue/20/solid/index.js";

const props = defineProps({
    parentModel: Object,
    totalCost: Number,
    deposit: Number,
    minimal: Boolean,
    canChangePaymentMethod: {
        type: Boolean,
        default: true,
    },
});

function unselectOffer() {
    router.post(route('payment.unselect', {
        parent: props.parentModel,
    }));
}

</script>

<template>

    <Head>
        <title>Cash</title>
    </Head>

    <div class="p-4">

        <button v-if="canChangePaymentMethod"
                type="button"
                class="float-end rounded bg-gray-600 px-2 py-1 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600"
                @click="unselectOffer">
            Change Payment Option
        </button>

        <h1 v-if="!minimal" class="text-4xl font-bold">
            Cash
        </h1>

        <p v-if="!minimal" class="mt-4">Here's how you pay cash.</p>

        <div class="bg-white py-16">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="flow-root">
                    <div class="isolate -mt-16 grid max-w-sm grid-cols-1 gap-y-16 divide-y divide-gray-100 lg:mt-0 lg:max-w-none lg:grid-cols-2 lg:divide-x lg:divide-y-0"
                         :class="{'sm:mx-auto lg:mx-14 xl:mx-24': !minimal}">

                        <!-- Deposit -->
                        <div class="pt-16 lg:px-8 lg:pt-0 xl:px-14">
                            <h3 class="text-4xl font-semibold text-blue-800">Deposit</h3>
                            <p class="mt-6 flex items-baseline gap-x-1">
                                <span class="text-5xl font-semibold tracking-tight text-gray-900">
                                    {{ formatCurrency(deposit) }}
                                </span>
                            </p>

                            <p class="mt-10 text-sm/6 font-semibold text-gray-900">
                                Pay your deposit now.
                            </p>

                            <!-- <button v-if="!minimal" class="mt-10 w-full rounded-md bg-blue-600 px-3 py-2 text-center text-sm/6 font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                Pay Deposit
                            </button> -->

                        </div>

                        <!-- Final Payment -->
                        <div class="pt-16 lg:px-8 lg:pt-0 xl:px-14">
                            <h3 class="text-4xl font-semibold text-blue-800">Final Payment</h3>
                            <p class="mt-6 flex items-baseline gap-x-1">
                                <span class="text-5xl font-semibold tracking-tight text-gray-900">
                                    {{ formatCurrency(totalCost - deposit) }}
                                </span>
                            </p>

                            <p class="mt-10 text-sm/6 font-semibold text-gray-900">
                                Once your installation is complete, we'll be in touch to collect your final payment.
                            </p>

                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

</template>

<style scoped>

</style>

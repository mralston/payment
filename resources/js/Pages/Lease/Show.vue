<script setup>

import {Head, router} from "@inertiajs/vue3";
import {formatCurrency} from "../../Helpers/Currency.js";

const props = defineProps({
    parentModel: Object,
    survey: Object,
    payment: Object,
    response: Object,
    offer: Object,
});

function cancel()
{
    if (!confirm('Are you sure you want to cancel this application?')) {
        return;
    }

    router.post(route('payment.cancel', {parent: props.parentModel, payment: props.payment}));
}

</script>

<template>

    <Head>
        <title>Lease</title>
    </Head>

    <div class="p-4">

        <div v-if="payment.payment_provider.logo" class="mb-6">
            <img :src="payment.payment_provider.logo" class="max-w-1/3 h-14" :alt="payment.payment_provider.name">
        </div>
        <h1 v-else class="text-4xl font-bold mb-6">
            {{ payment.payment_provider.name }}
        </h1>

        <div v-if="payment.payment_status.identifier === 'error'">

            <p class="mb-4">Looks like something went wrong with this application.</p>

            <ul v-for="field in response.errors" class="list-disc list-inside text-red-500">
                <li v-for="error in field">
                    {{ error.description }}
                </li>
            </ul>

        </div>

        <div v-else>

            <button type="button"
                    class="float-right rounded bg-gray-600 px-2 py-1 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600"
                    @click="cancel">
                Cancel Application
            </button>

            <p class="mb-4">Great news! Hometree have accepted your application. They'll get in touch with you soon.</p>

            <table class="mb-4 w-full md:w-1/2">
                <tbody>
                    <tr>
                        <th class="bg-gray-100 p-1 mr-2">Reference</th>
                        <td class="bg-gray-100 p-1">
                            {{ response.reference }}
                        </td>
                    </tr>
                    <tr>
                        <th class="p-1 mr-2">Plan</th>
                        <td class="p-1">
                            {{ offer.name }}
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-gray-100 p-1 mr-2">First Payment</th>
                        <td class="bg-gray-100 p-1">
                            {{ formatCurrency(offer.first_payment) }}
                        </td>
                    </tr>
                    <tr>
                        <th class="p-1 mr-2">Monthly Payment</th>
                        <td class="p-1">
                            {{ formatCurrency(offer.monthly_payment) }}
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-gray-100 p-1 mr-2">Final Payment</th>
                        <td class="bg-gray-100 p-1">
                            {{ formatCurrency(offer.final_payment) }}
                        </td>
                    </tr>
                </tbody>
            </table>

<!--            <button class="mt-10 rounded-md bg-blue-600 px-3 py-2 text-center text-sm/6 font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">-->
<!--                Pay Deposit-->
<!--            </button>-->

        </div>

    </div>

</template>

<style scoped>

</style>

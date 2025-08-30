<script setup>
import { toPounds } from "../Helpers/Currency.js";
import { toMax2DP } from "../Helpers/Number.js";
import {computed} from "vue";

const props = defineProps({
    title: {
        type: String,
        default: 'Representative Example',
    },
    amount: Number,
    deposit: Number,
    term: Number,
    upfrontPayment: Number,
    firstPayment: Number,
    monthlyPayment: Number,
    finalPayment: Number,
    totalPayable: Number,
    showMonthlyPayment: {
        type: Boolean,
        default: true,
    },
});

</script>

<template>
    <div>
        <h2 class="text-2xl mb-4">{{ title }}</h2>

        <table class="w-full">
            <tbody>
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">Deposit (paid upon booking)</th>
                    <td class="bg-gray-100 p-1">{{ toPounds(deposit) }}</td>
                    <th class="bg-gray-100 p-1 mr-2">
                        Upfront payment
                        <span class="font-normal">
                            (by direct debit a few days after installation)
                        </span>
                    </th>
                    <td class="bg-gray-100 p-1">
                        <span v-if="upfrontPayment">
                            {{ toPounds(upfrontPayment) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th class="p-1 mr-2">Term</th>
                    <td class="p-1">{{ term / 12 }} years</td>
                    <th class="p-1 mr-2">
                        Total amount payable
                    </th>
                    <td class="p-1">
                        {{ toPounds(totalPayable) }}
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">
                        First payment
                        <span class="font-normal">
                            (includes larger upfront amount if chosen)
                        </span>
                    </th>
                    <td class="bg-gray-100 p-1">
                        <span v-if="firstPayment">
                            {{ toPounds(firstPayment) }}
                        </span>
                    </td>
                    <th class="bg-gray-100 p-1 mr-2">
                        <span v-if="finalPayment">
                            Final payment
                        </span>
                    </th>
                    <td class="bg-gray-100 p-1">
                        <span v-if="finalPayment">
                            {{ toPounds(finalPayment) }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>



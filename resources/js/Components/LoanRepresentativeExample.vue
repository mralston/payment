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
    apr: Number,
    upfrontPayment: Number,
    firstPayment: Number,
    monthlyPayment: Number,
    finalPayment: Number,
    totalPayable: Number,
    showInterest: {
        type: Boolean,
        default: true,
    },
});

/**
 * Calculate AER from APR
 * As per https://www.natwest.com/savings/savings-guides/what-is-aer.html
 */
const aer = computed(() => {
    if (!props.apr) {
        return null; // Return null if APR is not provided
    }

    // The annual percentage rate as a decimal
    const r = props.apr / 100;

    // The number of compounding periods per year
    const n = 12;

    // AER formula: (1 + r/n)^n - 1
    return (Math.pow(1 + r / n, n) - 1) * 100;
});

</script>

<template>
    <div>
        <h2 class="text-2xl mb-4">{{ title }}</h2>
        <table class="w-full">
            <tbody>
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">Total cash price</th>
                    <td class="bg-gray-100 p-1">{{ toPounds(amount + deposit) }}</td>
                    <th class="bg-gray-100 p-1 mr-2">Duration of agreement</th>
                    <td class="bg-gray-100 p-1">{{ term / 12 }} years</td>
                </tr>
                <tr>
                    <th class="p-1 mr-2">Deposit</th>
                    <td class="p-1">{{ toPounds(deposit) }}</td>
                    <th class="p-1 mr-2">
                        <span v-if="firstPayment">
                            First payment
                        </span>
                    </th>
                    <td class="p-1">
                        <span v-if="firstPayment">
                            {{ toPounds(firstPayment) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">Amount of credit</th>
                    <td class="bg-gray-100 p-1">{{ toPounds(amount) }}</td>
                    <th class="bg-gray-100 p-1 mr-2">
                        <span v-if="firstPayment">
                            {{ term - 2 }} payments of
                        </span>
                        <span v-else>
                            {{ term }} payments of
                        </span>
                    </th>
                    <td class="bg-gray-100 p-1">
                        {{ toPounds(monthlyPayment) }}
                    </td>
                </tr>
                <tr>
                    <th class="p-1 mr-2">
                        <span v-if="apr">
                            Annual percentage rate (APR)
                        </span>
                        <span v-if="upfrontPayment">
                            Upfront payment
                        </span>
                    </th>
                    <td class="p-1">
                        <span v-if="apr">
                            {{ toMax2DP(apr) }}%
                        </span>
                        <span v-if="upfrontPayment">
                            {{ toPounds(upfrontPayment) }}
                        </span>
                    </td>
                    <th class="p-1 mr-2">
                        <span v-if="finalPayment">
                            Final payment
                        </span>
                    </th>
                    <td class="p-1">
                        <span v-if="finalPayment">
                            {{ toPounds(finalPayment) }}
                        </span>
                    </td>
                </tr>
                <tr v-if="aer || showInterest">
                    <th class="bg-gray-100 p-1 mr-2">
                        <span v-if="aer">
                            Effective annual rate (AER)
                        </span>
                    </th>
                    <td class="bg-gray-100 p-1">
                        <span v-if="aer">
                            {{ toMax2DP(aer) }}%
                        </span>
                    </td>
                    <th class="bg-gray-100 p-1 mr-2">
                        <span v-if="showInterest">
                            Interest payable
                        </span>
                    </th>
                    <td class="bg-gray-100 p-1">
                        <span v-if="showInterest">
                            {{ toPounds(totalPayable - amount) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th class="p-1 mr-2" :class="{'bg-gray-100': !aer && !showInterest}"></th>
                    <td class="p-1" :class="{'bg-gray-100': !aer && !showInterest}"></td>
                    <th class="p-1 mr-2" :class="{'bg-gray-100': !aer && !showInterest}">Total amount payable</th>
                    <td class="p-1" :class="{'bg-gray-100': !aer && !showInterest}">{{ toPounds(totalPayable) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>



<script setup>
import { toPounds } from "../Helpers/Currency.js";
import { toMax2DP } from "../Helpers/Number.js";
import {computed} from "vue";

const props = defineProps({
    amount: Number,
    deposit: Number,
    term: Number,
    apr: Number,
    firstPayment: Number,
    monthlyPayment: Number,
    finalPayment: Number,
    totalPayable: Number,
});

const aer = computed(() => {
    if (props.term <= 0) {
        return 0;
    }

    return (Math.pow(1 + props.apr / 100, 1 / props.term) - 1) * props.term * 100;
});

</script>

<template>
    <div>
        <h2 class="text-2xl mb-4">Representative Example</h2>
        <table class="w-full">
            <tbody>
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">Total cash price</th>
                    <td class="bg-gray-100 p-1">{{ toPounds(amount + deposit) }}</td>
                    <th class="bg-gray-100 p-1 mr-2">Duration of agreement</th>
                    <td class="bg-gray-100 p-1">{{ term }} months</td>
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
                        <span v-if="aer">
                            Annual percentage rate (APR)
                        </span>
                    </th>
                    <td class="p-1">
                        <span v-if="aer">
                            {{ toMax2DP(apr) }}%
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
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">
                        <span v-if="aer">
                            Annual interest rate
                        </span>
                    </th>
                    <td class="bg-gray-100 p-1">
                        <span v-if="aer">
                            {{ toMax2DP(aer) }}%
                        </span>
                    </td>
                    <th class="bg-gray-100 p-1 mr-2">Interest payable</th>
                    <td class="bg-gray-100 p-1">{{ toPounds(totalPayable - amount) }}</td>
                </tr>
                <tr>
                    <th class="p-1 mr-2"></th>
                    <td class="p-1"></td>
                    <th class="p-1 mr-2">Total amount payable</th>
                    <td class="p-1">{{ toPounds(totalPayable) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>



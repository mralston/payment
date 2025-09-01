<script setup>

import {toPounds} from "../Helpers/Currency.js";
import {toMax2DP} from "../Helpers/Number.js";
import {computed} from "vue";

const props = defineProps({
    term: Number, // In months
    deferred: Number,
    upfrontPayment: Number,
    yearlyPayments: Array,
    apr: Number,
    systemSavings: Array,
    // Optional fallback when yearlyPayments are missing/zero
    monthlyPayment: Number,
    showTitle: {
        type: Boolean,
        default: true,
    },
    year1Disclaimer: String,
});

const paymentsBreakdown = computed(() => {
    let result = [];
    const term = Number(props.term ?? 0) || 0;
    const numberOfYears = Math.max(0, Math.floor(term / 12));

    const yearlyPayments = Array.isArray(props.yearlyPayments) ? props.yearlyPayments : [];
    const systemSavings = Array.isArray(props.systemSavings) ? props.systemSavings : [];

    for (let i = 0; i < numberOfYears; i++) {
        // Year
        let datum = {
            'year': i + 1
        };

        // Savings calculations remain the same
        if (systemSavings[i]) {
            datum.savings = Number(systemSavings[i]) || 0;
            datum.monthly = datum.savings / 12;
        } else {
            datum.legend = '*';
            datum.savings = 0;
            datum.monthly = 0;
        }

        // Get the total payment for this year from our new prop
        let yearlyPayment = Number(yearlyPayments[i] ?? 0) || 0;

        // Fallback: if yearlyPayments missing/zero, derive from monthlyPayment prop
        if ((!yearlyPayment || yearlyPayment === 0) && typeof props.monthlyPayment === 'number') {
            yearlyPayment = props.monthlyPayment * 12;
        }

        // Calculate the average monthly cost for this specific year
        datum.monthlyPayment = yearlyPayment / 12;

        // Calculate the difference
        datum.diff = datum.monthly - datum.monthlyPayment;

        result.push(datum);
    }

    return result;
});

const termInYears = computed(() => {
    const term = Number(props.term ?? 0) || 0;
    return Math.max(0, Math.floor(term / 12));
});

</script>

<template>

    <div>
        <p v-if="showTitle" class="mb-2">
            <strong>
                {{ termInYears }} Year Finance Option
                <span v-if="apr">at {{ toMax2DP(apr) }}% APR</span>
            </strong>
            <span v-if="deferred > 0">
                (First payment deferred for {{ deferred }} months)
            </span>
            <span v-if="upfrontPayment > 0">
                ({{ toPounds(upfrontPayment) }} up front)
            </span>
        </p>
        <table class="table table-bordered table-striped mb-0">
            <thead>
                <tr>
                    <th>Yr</th>
                    <th>Avg. Monthly Payment</th>
                    <th>Yearly Savings</th>
                    <th>Avg. Monthly Savings</th>
                    <th>Monthly Net Position</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, index) in paymentsBreakdown" :key="row.year">
                    <td>
                        {{ row.year }}
                        <span v-if="upfrontPayment > 0 && index === 0">*</span>
                    </td>
                    <td>{{ toPounds(row.monthlyPayment) }}</td>
                    <td>{{ toPounds(row.savings) }}</td>
                    <td>{{ toPounds(row.monthly) }}</td>
                    <td :class="row.diff < 0 ? 'alert-danger' : 'alert-success'">
                        {{ toPounds(row.diff) }}
                    </td>
                </tr>
            </tbody>
        </table>
        <p v-if="upfrontPayment > 0" class="mt-2">
            * Does not include up front payment.
        </p>
    </div>

</template>

<!--<script setup>-->

<!--import {toPounds} from "../Helpers/Currency.js";-->
<!--import {toMax2DP} from "../Helpers/Number.js";-->
<!--import {computed} from "vue";-->

<!--const props = defineProps({-->
<!--    term: Number,-->
<!--    deferred: Number,-->
<!--    upfrontPayment: Number,-->
<!--    monthlyPayment: Number,-->
<!--    apr: Number,-->
<!--    systemSavings: Object,-->
<!--    showTitle: {-->
<!--        type: Boolean,-->
<!--        default: true,-->
<!--    }-->
<!--});-->

<!--const paymentsBreakdown = computed(() => {-->
<!--    let result = [];-->

<!--    for (let i = 0; i < (props.term / 12); i++) {-->

<!--        //year-->
<!--        let datum = {-->
<!--            'year': i + 1-->
<!--        };-->

<!--        if (props.systemSavings.data[i]) {-->
<!--            //total savings per year-->
<!--            datum.savings = props.systemSavings.data[i]['total'];-->

<!--            //total savings per month-->
<!--            datum.monthly = datum['savings'] / 12;-->
<!--        }-->
<!--        else {-->
<!--            datum.legend = '*';-->
<!--        }-->

<!--        //difference-->
<!--        datum.diff = datum.monthly - props.monthlyPayment;-->

<!--        result.push(datum);-->
<!--    }-->

<!--    return result;-->
<!--});-->

<!--</script>-->

<!--<template>-->

<!--    <div>-->
<!--        <p v-if="showTitle" class="mb-2">-->
<!--            <strong>-->
<!--                {{ term }} payments-->
<!--                of {{ toPounds(monthlyPayment) }}/month-->
<!--                <span v-if="apr">at {{ toMax2DP(apr) }}%</span>-->
<!--            </strong>-->
<!--            <span v-if="deferred > 0">-->
<!--                ({{ deferred }} months deferred)-->
<!--            </span>-->
<!--            <span v-if="upfrontPayment > 0">-->
<!--                ({{ toPounds(upfrontPayment) }} up front payment)-->
<!--            </span>-->
<!--        </p>-->
<!--        <table class="table table-bordered table-striped mb-0">-->
<!--            <thead>-->
<!--            <tr>-->
<!--                <th>Yr</th>-->
<!--                <th>Acc. grand total</th>-->
<!--                <th>Savings</th>-->
<!--                <th>Potential monthly payment diff.</th>-->
<!--            </tr>-->
<!--            </thead>-->
<!--            <tbody>-->
<!--            <tr v-for="row in paymentsBreakdown">-->
<!--                <td>{{ row.year }}</td>-->
<!--                <td>{{ toPounds(row.savings) }}</td>-->
<!--                <td>{{ toPounds(row.monthly) }}</td>-->
<!--                <td v-bind:class="row.diff <= 0 ? 'alert-danger' : 'alert-success'">{{ toPounds(row.diff) }}</td>-->
<!--            </tr>-->
<!--            </tbody>-->
<!--        </table>-->
<!--    </div>-->

<!--</template>-->

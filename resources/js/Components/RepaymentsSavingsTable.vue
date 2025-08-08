<script setup>

import {toPounds} from "../Helpers/Currency.js";
import {toMax2DP} from "../Helpers/Number.js";
import {computed} from "vue";

const props = defineProps({
    term: Number,
    deferred: Number,
    monthlyPayment: Number,
    apr: Number,
    systemSavings: Object,
    showTitle: {
        type: Boolean,
        default: true,
    }
});

const repaymentsBreakdown = computed(() => {
    let result = [];

    for (let i = 0; i < (props.term / 12); i++) {

        //year
        let datum = {
            'year': i + 1
        };

        if (props.systemSavings.data[i]) {
            //total savings per year
            datum.savings = props.systemSavings.data[i]['total'];

            //total savings per month
            datum.monthly = datum['savings'] / 12;
        }
        else {
            datum.legend = '*';
        }

        //difference
        datum.diff = datum.monthly - props.monthlyPayment;

        result.push(datum);
    }

    return result;
});

</script>

<template>

    <div>
        <p v-if="showTitle" class="mb-2">
            <strong>
                {{ term }} payments
                of {{ toPounds(monthlyPayment) }}/month
                <span v-if="apr">at {{ toMax2DP(apr) }}%</span>
            </strong>
            <span v-if="deferred > 0">
                ({{ deferred }} months deferred)
            </span>
        </p>
        <table class="table table-bordered table-striped mb-0">
            <thead>
                <tr>
                    <th>Yr</th>
                    <th>Acc. grand total</th>
                    <th>Savings</th>
                    <th>Potential monthly repayment diff.</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in repaymentsBreakdown">
                    <td>{{ row.year }}</td>
                    <td>{{ toPounds(row.savings) }}</td>
                    <td>{{ toPounds(row.monthly) }}</td>
                    <td v-bind:class="row.diff <= 0 ? 'alert-danger' : 'alert-success'">{{ toPounds(row.diff) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

</template>

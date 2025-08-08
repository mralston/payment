<script setup>
import {ref, watch, onMounted, computed} from 'vue';

// No longer importing a mixin or the FinanceApplication class
import {formatCurrency} from "../Helpers/Currency.js";
import {monthsYears} from "../Helpers/Date.js";

// Define props
const props = defineProps({
    loan_amount: Number,
    apr: Number,
    total_repayable: Number,
    default_loan_term: Number,
    default_monthly_repayment: Number,
    deferred_period: Number,
});

// Helper functions from the original FinanceApplication class
const aprToRateV2 = (apr) => {
    return apr / 100 / 12;
};

const aprToRates = (apr) => {
    return {
        monthly: apr / 12,
    };
};

const calculateRepayments = (amt, apr, term, paymentDeferred) => {
    if (term <= 0) {
        return null;
    }

    term = Math.round(term);

    if (paymentDeferred) {
        // ikano bank rate calculations are different
        const rate = aprToRateV2(apr);

        const balanceAtEndOfDeferredPeriod = Math.pow(1 + rate, paymentDeferred) * amt;
        const balanceOnFirstDueDate = (1 + rate * 5 / 365 * 12) * balanceAtEndOfDeferredPeriod;

        const repayment = (rate * (balanceOnFirstDueDate * Math.pow(1 + rate, term))) / ((1 + rate) * (Math.pow(1 + rate, term) - 1));

        const finalRepayment = ((repayment * (1 + rate) * (Math.pow(1 + rate, term - 1) - 1) / rate) - (balanceOnFirstDueDate * Math.pow(1 + rate, term - 1))) * -1;

        const totalRepayable = repayment * (term - 1) + finalRepayment;

        const interest = totalRepayable - amt;

        return {
            term: term,
            paymentDeferred: paymentDeferred,
            repayment: repayment,
            firstRepayment: null,
            finalRepayment: finalRepayment,
            total: totalRepayable,
            apr: apr,
            amt: amt,
            interest: interest
        };
    }

    const rate = aprToRates(apr).monthly / 100;
    let repayment = 0;
    if (apr == 0 || rate == 0) {
        repayment = amt / term;
    } else {
        const calc = 1 / (1 + rate);
        repayment = (amt * (calc - 1)) / (calc * ((Math.pow(calc, term) - 1)));
    }

    const total = repayment * term;
    const interest = total - amt;

    return {
        'term': term,
        'repayment': repayment,
        'total': total,
        'apr': apr,
        'amt': amt,
        'interest': interest
    };
};

const calculateTerm = (loanAmount, apr, monthlyPayment) => {
    const rate = aprToRates(apr).monthly / 100;
    const term = -Math.log(1 - (loanAmount * rate) / monthlyPayment) / Math.log(1 + rate);

    const total = monthlyPayment * term;
    const interest = total - loanAmount;

    return {
        term: term,
        total: total,
        interest: interest,
    };
};

// Reactive state
const loan_term = ref(0);
const monthly_repayment = ref(0);

// Flag to prevent the infinite loop
const isUpdating = ref(false);

// Watchers with the flag
watch(loan_term, (newTerm) => {
    if (isUpdating.value) return;
    isUpdating.value = true;
    if (!isNaN(newTerm) && newTerm > 0) {
        const calc = calculateRepayments(props.loan_amount, props.apr, newTerm);
        if (calc) {
            monthly_repayment.value = calc.repayment;
        }
    }
    isUpdating.value = false;
}, {immediate: true});

watch(monthly_repayment, (newRepayment) => {
    if (isUpdating.value) return;
    isUpdating.value = true;
    if (!isNaN(newRepayment) && newRepayment > 0) {
        const calc = calculateTerm(props.loan_amount, props.apr, newRepayment);
        if (calc) {
            loan_term.value = calc.term;
        }
    }
    isUpdating.value = false;
}, {immediate: false});

// Computed properties
const additional_payments = computed(() => {
    return Math.max(0, monthly_repayment.value - props.default_monthly_repayment);
});

const default_interest = computed(() => {
    return props.total_repayable - props.loan_amount;
});

const current_calc = computed(() => {
    // This computed property will run whenever loan_term or monthly_repayment changes
    return calculateRepayments(props.loan_amount, props.apr, loan_term.value);
});

const interest_saved = computed(() => {
    const calc = current_calc.value;
    if (calc) {
        return Math.max(0, default_interest.value - calc.interest);
    }
    return 0;
});

const total_amount_payable = computed(() => {
    const calc = current_calc.value;
    if (calc) {
        return calc.total;
    }
    return 0;
});

// Lifecycle hook
onMounted(() => {
    loan_term.value = props.default_loan_term;
    monthly_repayment.value = props.default_monthly_repayment;
});
</script>

<template>
    <div>

        <h2 class="text-2xl mb-4">Overpayments Estimator</h2>

        <div class="mb-4">
            <label for="loan_term"><b>Loan Term</b></label>
            <input type="range" id="loan_term" min="1" :max="default_loan_term" v-model="loan_term">
            {{ monthsYears(loan_term) }}
        </div>

        <div class="mb-4">
            <label for="monthly_repayment"><b>Monthly Payments</b></label>
            <input type="range" id="monthly_repayment" :min="default_monthly_repayment" :max="loan_amount" v-model="monthly_repayment">
            {{ formatCurrency(monthly_repayment) }}
        </div>

        <table class="w-full">
            <tbody>
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">Original Payment</th>
                    <td class="bg-gray-100 p-1">{{ formatCurrency(default_monthly_repayment) }}</td>
                </tr>
                <tr>
                    <th class="p-1 mr-2">Additional Payments of</th>
                    <td class="p-1">{{ formatCurrency(additional_payments) }}</td>
                </tr>
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">Interest Saved</th>
                    <td class="bg-gray-100 p-1">{{ formatCurrency(interest_saved) }}</td>
                </tr>
                <tr>
                    <th class="p-1 mr-2">Total Amount Payable</th>
                    <td class="p-1">{{ formatCurrency(total_amount_payable) }}</td>
                </tr>
            </tbody>
        </table>

    </div>
</template>

<script setup>

import {makeNumeric} from "../../Helpers/Number.js";
import OverpaymentCalculator from "../OverpaymentCalculator.vue";

const props = defineProps({
    totalCost: Number,
    deposit: Number,
    offer: Object,
});

</script>

<template>

    <h2 class="text-2xl mb-4">Potential Savings</h2>

    <table class="mb-4 table table-bordered table-striped mb-0"><thead><tr><th>Yr</th> <th>Acc. grand total</th> <th>Savings</th> <th>Potential monthly repayment diff.</th></tr></thead> <tbody><tr><td>1</td> <td>£776.25</td> <td>£64.69</td> <td class="alert-danger">-£71.52</td></tr><tr><td>2</td> <td>£824.10</td> <td>£68.68</td> <td class="alert-danger">-£67.53</td></tr><tr><td>3</td> <td>£875.25</td> <td>£72.94</td> <td class="alert-danger">-£63.27</td></tr><tr><td>4</td> <td>£929.91</td> <td>£77.49</td> <td class="alert-danger">-£58.72</td></tr><tr><td>5</td> <td>£988.35</td> <td>£82.36</td> <td class="alert-danger">-£53.85</td></tr><tr><td>6</td> <td>£1,050.85</td> <td>£87.57</td> <td class="alert-danger">-£48.64</td></tr><tr><td>7</td> <td>£1,117.69</td> <td>£93.14</td> <td class="alert-danger">-£43.07</td></tr><tr><td>8</td> <td>£1,189.19</td> <td>£99.10</td> <td class="alert-danger">-£37.11</td></tr><tr><td>9</td> <td>£1,265.69</td> <td>£105.47</td> <td class="alert-danger">-£30.74</td></tr><tr><td>10</td> <td>£1,347.54</td> <td>£112.30</td> <td class="alert-danger">-£23.91</td></tr></tbody></table>

    <OverpaymentCalculator v-if="offer"
        :loan_amount="totalCost - deposit"
        :apr="makeNumeric(offer.apr)"
        :total_repayable="makeNumeric(offer.total_repayable)"
        :default_loan_term="makeNumeric(offer.term)"
        :default_monthly_repayment="makeNumeric(offer.monthly_payment)"
        :deferred_period="makeNumeric(offer.deferred)"/>
</template>

<style scoped>

</style>

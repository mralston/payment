<script setup>

import {makeNumeric, toMax2DP} from "../../Helpers/Number.js";
import OverpaymentCalculator from "../OverpaymentCalculator.vue";
import PaymentsSavingsTable from "../PaymentsSavingsTable.vue";
import Card from "../Card.vue";
import {toPounds} from "../../Helpers/Currency.js";

const props = defineProps({
    totalCost: Number,
    deposit: Number,
    selectedOffer: Object,
    otherOffers: Array,
    systemSavings: Object,
});

</script>

<template>

    <h2 class="text-2xl mb-4">Potential Savings</h2>

    <Card class="mb-8" header-class="bg-gray-100">
        <template v-slot:header>
            <div class="text-2xl">
                <img v-if="selectedOffer.payment_provider.logo"
                     :src="selectedOffer.payment_provider.logo"
                     :alt="selectedOffer.payment_provider.name"
                     class="h-7 mr-4 inline-block">
                <span v-else>selectedOffer.payment_provider.name</span>

                <span class="font-bold">
                    {{ toMax2DP(selectedOffer.apr) }}% -
                    {{ toPounds(selectedOffer.monthly_payment) }}/month
                </span>
                <span>
                    over {{ selectedOffer.term / 12 }} years
                </span>
                <span v-if="selectedOffer.deferred > 0">
                    ({{ selectedOffer.deferred }} months deferred)
                </span>
            </div>
        </template>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <PaymentsSavingsTable :show-title="false"
                                      :term="makeNumeric(selectedOffer.term)"
                                      :deferred="makeNumeric(selectedOffer.deferred)"
                                      :yearly-payments="selectedOffer.yearly_payments"
                                      :apr="makeNumeric(selectedOffer.apr)"
                                      :system-savings="systemSavings"
                                      class="bg-white mb-4"/>
            </div>
            <div>
                <h2 class="text-2xl mb-4">Overpayments Estimator</h2>
                <OverpaymentCalculator :loan_amount="totalCost - deposit"
                                       :apr="makeNumeric(selectedOffer.apr)"
                                       :total_payable="makeNumeric(selectedOffer.total_payable)"
                                       :default_loan_term="makeNumeric(selectedOffer.term)"
                                       :default_monthly_payment="makeNumeric(selectedOffer.monthly_payment)"
                                       :deferred_period="makeNumeric(selectedOffer.deferred)"/>
            </div>
        </div>
    </Card>

    <Card header-class="bg-gray-100" :collapsed="true">
        <template v-slot:header>
            <span class="text-2xl">Other offers</span>
        </template>
        <div class="grid grid-cols-2 gap-4">
            <div v-for="offer in otherOffers" :key="offer.id">
                <div class="text-2xl mb-2">
                    <img v-if="offer.payment_provider.logo"
                         :src="offer.payment_provider.logo"
                         :alt="offer.payment_provider.name"
                         class="h-7 mr-4 inline-block">
                    <span v-else>selectedOffer.payment_provider.name</span>
                </div>
                <PaymentsSavingsTable :term="makeNumeric(offer.term)"
                                      :deferred="makeNumeric(offer.deferred)"
                                      :yearly-payments="offer.yearly_payments"
                                      :apr="makeNumeric(offer.apr)"
                                      :system-savings="systemSavings"
                                      class="mb-4"/>
            </div>
        </div>
    </Card>



</template>

<style scoped>

</style>

<script setup>

import {makeNumeric, toMax2DP} from "../../Helpers/Number.js";
import PaymentsSavingsTable from "../PaymentsSavingsTable.vue";
import {toPounds} from "../../Helpers/Currency.js";
import Card from "../Card.vue";
import OverpaymentCalculator from "../OverpaymentCalculator.vue";

const props = defineProps({
    content: String,
    totalCost: Number,
    deposit: Number,
    selectedOffer: Object,
    otherOffers: Array,
    systemSavings: Array,
})

</script>

<template>
    <div v-if="selectedOffer">
    <div v-if="content" v-html="content" class="mb-4"/>

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
                    {{ toPounds(selectedOffer.monthly_payment) }}/month
                </span>
                <span>
                    over {{ selectedOffer.term / 12 }} years
                </span>
                <span v-if="selectedOffer.deferred > 0">
                    ({{ selectedOffer.deferred }} months deferred)
                </span>
                <span v-if="selectedOffer.upfront_payment > 0">
                ({{ toPounds(selectedOffer.upfront_payment) }} up front)
            </span>
            </div>
        </template>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <PaymentsSavingsTable :show-title="false"
                                       :term="makeNumeric(selectedOffer.term)"
                                       :deferred="makeNumeric(selectedOffer.deferred)"
                                       :upfront-payment="makeNumeric(selectedOffer.upfront_payment)"
                                       :yearly-payments="selectedOffer.yearly_payments"
                                       :monthly-payment="makeNumeric(selectedOffer.monthly_payment)"
                                       :apr="makeNumeric(selectedOffer.apr)"
                                       :system-savings="systemSavings"
                                       class="bg-white mb-4"/>
                <p>The cost of replacing the battery at the end of its warranty may have been taken into account. This is not relevant for {{ selectedOffer.payment_provider.name }}, who will facilitate this replacement, if required, at their own expense.</p>
            </div>
            <div>
                <p class="font-bold mb-4">Total Payable</p>
                <p>{{ toPounds(selectedOffer.total_payable) }}</p>
            </div>
        </div>
    </Card>

    <Card header-class="bg-gray-100" :collapsed="true">
        <template v-slot:header>
            <span class="text-2xl">Other options</span>
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
                                      :upfront-payment="makeNumeric(offer.upfront_payment)"
                                      :yearly-payments="offer.yearly_payments"
                                      :monthly-payment="makeNumeric(offer.monthly_payment)"
                                      :apr="makeNumeric(offer.apr)"
                                      :system-savings="systemSavings"
                                      class="mb-4"/>
            </div>
        </div>
    </Card>
    </div>

</template>

<style scoped>

</style>

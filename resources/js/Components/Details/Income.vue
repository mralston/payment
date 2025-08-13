<script setup>
import { Icon } from '@iconify/vue';
import DetailsRow from './DetailsRow.vue';
import Card from '../Card.vue';
import { formatCurrency } from '../../Helpers/Currency.js';

const props = defineProps({
    payment: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Card class="border border-[#ed6058]" header-class="border-[#ed6058]">
        <template #header>
            <div class="flex flex-row gap-2 items-center">
                <h2 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:money-bill-wave" /> Income &amp; Expenditure</h2>
            </div>
        </template>
        <div class="bg-white">
            <div class="flex flex-row gap-4 max-md:flex-col">
                <div class="w-1/2 max-md:w-full">
                    <DetailsRow
                        label="Gross Individual income (annual)"
                        :value="formatCurrency(payment.gross_income_individual)"
                    />
                    <DetailsRow
                        label="Net Individual income (monthly)"
                        :value="formatCurrency(payment.net_monthly_income_individual ?? 0)"
                    />
                    <DetailsRow
                        label="Other income (annual)"
                        :value="formatCurrency((payment.gross_income_household ?? 0) - (payment.gross_income_individual ?? 0))"
                    />
                    <DetailsRow
                        label="Total income (annual)"
                        :value="formatCurrency(payment.gross_income_household ?? 0)"
                    />
                </div>
                <div class="w-1/2 max-md:w-full">
                    <DetailsRow
                        label="Mortgage (monthly)"
                        :value="payment.mortgage_monthly ? formatCurrency(payment.mortgage_monthly) : ''"
                    />
                    <DetailsRow
                        label="Rent (monthly)"
                        :value="payment.rent_monthly ? formatCurrency(payment.rent_monthly) : ''"
                    />
                </div>
            </div>
        </div>
    </Card>
</template>
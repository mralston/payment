<script setup>

import Pagination from "../../Components/Pagination.vue";
import {Head, Link} from "@inertiajs/vue3";
import {formatDate} from "../../Helpers/Date.js";
import {formatCurrency} from "../../Helpers/Currency.js";
import {titleCase} from "../../Helpers/Strings.js";

const props = defineProps({
    payments: Object,
    parentRouteName: String,
    parentModelDescription: {
        type: String,
        default: 'Parent Model',
    },
});

</script>

<template>

    <Head>
        <title>Payments</title>
    </Head>

    <div class="p-10">
        <h1 class="text-4xl font-bold">Payments</h1>

        <div class="w-full overflow-x-scroll">
            <table class="min-w-full divide-y divide-gray-300">
                <thead>
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-4 text-left text-sm font-semibold text-gray-900 sm:pl-0">ID</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">Ref</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">{{ parentModelDescription }}</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">Customer</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">Post Code</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">Amount</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">Deposit</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">APR</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">Term</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">Deferred</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">Gateway</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">Subsidy</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">User</th>
                        <th scope="col" class="py-3.5 pl-4 pr-4 text-left text-sm font-semibold text-gray-900 sm:pr-0">&nbsp;</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="payment in payments.data" :key="payment.id">
                        <td class="whitespace-nowrap py-4 pl-4 pr-4 text-sm font-medium text-gray-500 sm:pl-0">{{ payment.id }}</td>
                        <td class="whitespace-nowrap p-4 text-sm text-gray-500">{{ formatDate(payment.created_at, 'DD/MM/YYYY') }}</td>
                        <td class="whitespace-nowrap p-4 text-sm text-gray-500">{{ payment.reference }}</td>
                        <td class="whitespace-nowrap p-4 text-sm text-gray-500">
<!--                            @if(!empty(payment.parentable?.id))-->
<!--                                <a :href="route(parentRouteName, payment.parentable_id)" class="text-blue-600 hover:text-blue-900">-->
<!--                                    {{ payment.parentable.id }}-->
<!--                                </a>-->
<!--                            @else-->
                            {{ payment.parentable_id }}
<!--                            @endif-->
                        </td>
                        <td class="whitespace-nowrap p-4 text-sm text-gray-500">
                            {{ payment.first_name }} {{ payment.last_name }}
                        </td>
                        <td class="whitespace-nowrap p-4 text-sm text-gray-500">{{ payment.addresses ? payment.addresses[0]?.postcode : '-' }}</td>
                        <td class="whitespace-nowrap p-4 text-sm text-gray-500">{{ formatCurrency(payment.amount) }}</td>
                        <td class="whitespace-nowrap p-4 text-sm text-gray-500">{{ formatCurrency(payment.deposit) }}</td>
                        <td class="whitespace-nowrap p-4 text-sm text-gray-500">{{ payment.apr ? payment.apr + '%' : '-' }}</td>
                        <td class="whitespace-nowrap p-4 text-sm text-gray-500">{{ payment.term }}</td>
                        <td class="whitespace-nowrap p-4 text-sm text-gray-500">{{ payment.deferred ?? '-' }}</td>
                        <td class="whitespace-nowrap p-4 text-sm text-gray-500">{{ payment.payment_status?.name }}</td>
                        <td class="whitespace-nowrap p-4 text-sm text-gray-500">{{ payment.payment_provider?.name }}</td>
                        <td class="whitespace-nowrap p-4 text-sm text-gray-500">{{ formatCurrency(payment.subsidy) }}</td>
                        <td class="whitespace-nowrap p-4 text-sm text-gray-500">{{ payment.parentable?.user?.name }}</td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                            <Link :href="route('payments.show', {payment: payment})" class="text-blue-600 hover:text-blue-900">
                                Show
                                <span class="sr-only">, {{ payment.reference }}</span>
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :records="payments"></Pagination>

    </div>
</template>

<style scoped></style>

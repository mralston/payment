<script setup>

import Pagination from "../../Components/Pagination.vue";
import {Head, Link, router, useForm} from "@inertiajs/vue3";
import {formatDate} from "../../Helpers/Date.js";
import {formatCurrency} from "../../Helpers/Currency.js";
import {titleCase} from "../../Helpers/Strings.js";
import {watch} from "vue";
import { ChevronDownIcon } from '@heroicons/vue/16/solid';

const props = defineProps({
    payments: Object,
    parentRouteName: String,
    parentModelDescription: {
        type: String,
        default: 'Parent Model',
    },
    search: Object,
    sort: String,
    direction: String,
});

const form = useForm({
    perPage: props.payments.per_page,
    search: props.search ?? {
        id: null,
        created_at: null,
        reference: null,
        parentable_id: null,
        customer: null,
        post_code: null,
        amount: null,
        deposit: null,
        apr: null,
        term: null,
        deferred: null,
        status: null,
        gateway: null,
        subsidy: null,
        user: null,
    },
    sort: props.sort ?? 'created_at',
    direction: props.direction ?? 'desc',
});

let searchTimeout = null;

watch(() => form.perPage, () => {
    router.get(route('payments.index', {
        search: form.search,
        per_page: form.perPage,
        sort: form.sort,
        direction: form.direction
    }, {
        preserveState: true,
        replace: true,
    }));
}, {deep: true});

function search() {
    router.get(route('payments.index'), {
        search: form.search,
        per_page: form.perPage,
        sort: form.sort,
        direction: form.direction
    }, {
        preserveState: true,
        replace: true,
    });
}

function toggleSort(field) {
    if (form.sort === field) {
        form.direction = form.direction === 'asc' ? 'desc' : 'asc';
    } else {
        form.sort = field;
        form.direction = 'asc';
    }
    router.get(route('payments.index'), {
        search: form.search,
        per_page: form.perPage,
        sort: form.sort,
        direction: form.direction,
    }, {
        preserveState: true,
        replace: true,
    });
}

function dataTableSortClass(field) {
    if (form.sort !== field) {
        return 'sorting';
    }
    return form.direction === 'asc' ? 'sorting_asc' : 'sorting_desc';
}

</script>

<template>

    <Head>
        <title>Payments</title>
    </Head>

    <div class="p-10">
        <h1 class="text-4xl font-bold mb-4">Payments</h1>

<!--        <div class="grid grid-cols-2">-->
<!--            <div>-->
<!--                <label class="block text-sm/6 font-medium text-gray-900 dark:text-white">-->
<!--                    Show-->
<!--                    <select v-model="form.perPage" class="rounded-md bg-white py-1.5 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:*:bg-gray-800 dark:focus-visible:outline-indigo-500">-->
<!--                        <option value="10">10</option>-->
<!--                        <option value="25">25</option>-->
<!--                        <option value="50">50</option>-->
<!--                        <option value="100">100</option>-->
<!--                    </select>-->
<!--                    entries-->
<!--                </label>-->
<!--            </div>-->
<!--            <div class="text-right">-->
<!--                <label>-->
<!--                    Search:-->
<!--                    <input type="search" class="rounded-md bg-white text-sm font-normal" placeholder="" v-model="form.search">-->
<!--                </label>-->
<!--            </div>-->
<!--        </div>-->





        <div class="w-full overflow-x-scroll">
            <table class="min-w-full divide-y divide-gray-300">
                <thead>
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-4 text-left text-sm font-semibold text-gray-900 sm:pl-0":class="dataTableSortClass('id')" @click="toggleSort('id')" role="button" :aria-sort="form.sort === 'id' ? (form.direction === 'asc' ? 'ascending' : 'descending') : 'none'">ID</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900" :class="dataTableSortClass('created_at')" @click="toggleSort('created_at')" role="button" :aria-sort="form.sort === 'created_at' ? (form.direction === 'asc' ? 'ascending' : 'descending') : 'none'">Date</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900" :class="dataTableSortClass('reference')" @click="toggleSort('reference')" role="button" :aria-sort="form.sort === 'reference' ? (form.direction === 'asc' ? 'ascending' : 'descending') : 'none'">Ref</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900" :class="dataTableSortClass('parent')" @click="toggleSort('parent')" role="button" :aria-sort="form.sort === 'parent' ? (form.direction === 'asc' ? 'ascending' : 'descending') : 'none'">{{ parentModelDescription }}</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900" :class="dataTableSortClass('customer')" @click="toggleSort('customer')" role="button" :aria-sort="form.sort === 'customer' ? (form.direction === 'asc' ? 'ascending' : 'descending') : 'none'">Customer</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900 whitespace-nowrap" :class="dataTableSortClass('post_code')" @click="toggleSort('post_code')" role="button" :aria-sort="form.sort === 'post_code' ? (form.direction === 'asc' ? 'ascending' : 'descending') : 'none'">Post Code</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900" :class="dataTableSortClass('amount')" @click="toggleSort('amount')" role="button" :aria-sort="form.sort === 'amount' ? (form.direction === 'asc' ? 'ascending' : 'descending') : 'none'">Amount</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900" :class="dataTableSortClass('deposit')" @click="toggleSort('deposit')" role="button" :aria-sort="form.sort === 'deposit' ? (form.direction === 'asc' ? 'ascending' : 'descending') : 'none'">Deposit</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900" :class="dataTableSortClass('apr')" @click="toggleSort('apr')" role="button" :aria-sort="form.sort === 'apr' ? (form.direction === 'asc' ? 'ascending' : 'descending') : 'none'">APR</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900" :class="dataTableSortClass('term')" @click="toggleSort('term')" role="button" :aria-sort="form.sort === 'term' ? (form.direction === 'asc' ? 'ascending' : 'descending') : 'none'">Term</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900" :class="dataTableSortClass('deferred')" @click="toggleSort('deferred')" role="button" :aria-sort="form.sort === 'deferred' ? (form.direction === 'asc' ? 'ascending' : 'descending') : 'none'">Deferred</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900" :class="dataTableSortClass('status')" @click="toggleSort('status')" role="button" :aria-sort="form.sort === 'status' ? (form.direction === 'asc' ? 'ascending' : 'descending') : 'none'">Status</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900" :class="dataTableSortClass('gateway')" @click="toggleSort('gateway')" role="button" :aria-sort="form.sort === 'gateway' ? (form.direction === 'asc' ? 'ascending' : 'descending') : 'none'">Gateway</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900" :class="dataTableSortClass('subsidy')" @click="toggleSort('subsidy')" role="button" :aria-sort="form.sort === 'subsidy' ? (form.direction === 'asc' ? 'ascending' : 'descending') : 'none'">Subsidy</th>
                        <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900" :class="dataTableSortClass('user')" @click="toggleSort('user')" role="button" :aria-sort="form.sort === 'user' ? (form.direction === 'asc' ? 'ascending' : 'descending') : 'none'">User</th>
                        <th scope="col" class="py-3.5 pl-4 pr-4 text-left text-sm font-semibold text-gray-900 sm:pr-0">&nbsp;</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr>
                        <td class="p-1">
                            <input type="text" class="border rounded border-gray-300 text-sm p-1 w-full" v-model="form.search.id" @keyup.enter="search">
                        </td>
                        <td class="p-1">
                            <input type="date" class="border rounded border-gray-300 text-sm p-1 w-full" v-model="form.search.created_at" @keyup.enter="search">
                        </td>
                        <td class="p-1">
                            <input type="text" class="border rounded border-gray-300 text-sm p-1 w-full" v-model="form.search.reference" @keyup.enter="search">
                        </td>
                        <td class="p-1">
                            <input type="text" class="border rounded border-gray-300 text-sm p-1 w-full" v-model="form.search.parentable_id" @keyup.enter="search">
                        </td>
                        <td class="p-1">
                            <input type="text" class="border rounded border-gray-300 text-sm p-1 w-full" v-model="form.search.customer" @keyup.enter="search">
                        </td>
                        <td class="p-1">
                            <input type="text" class="border rounded border-gray-300 text-sm p-1 w-full" v-model="form.search.post_code" @keyup.enter="search">
                        </td>
                        <td class="p-1">
                            <input type="text" class="border rounded border-gray-300 text-sm p-1 w-full" v-model="form.search.amount" @keyup.enter="search">
                        </td>
                        <td class="p-1">
                            <input type="text" class="border rounded border-gray-300 text-sm p-1 w-full" v-model="form.search.deposit" @keyup.enter="search">
                        </td>
                        <td class="p-1">
                            <input type="text" class="border rounded border-gray-300 text-sm p-1 w-full" v-model="form.search.apr" @keyup.enter="search">
                        </td>
                        <td class="p-1">
                            <input type="text" class="border rounded border-gray-300 text-sm p-1 w-full" v-model="form.search.term" @keyup.enter="search">
                        </td>
                        <td class="p-1">
                            <input type="text" class="border rounded border-gray-300 text-sm p-1 w-full" v-model="form.search.deferred" @keyup.enter="search">
                        </td>
                        <td class="p-1">
                            <input type="text" class="border rounded border-gray-300 text-sm p-1 w-full" v-model="form.search.status" @keyup.enter="search">
                        </td>
                        <td class="p-1">
                            <input type="text" class="border rounded border-gray-300 text-sm p-1 w-full" v-model="form.search.gateway" @keyup.enter="search">
                        </td>
                        <td class="p-1">
                            <input type="text" class="border rounded border-gray-300 text-sm p-1 w-full" v-model="form.search.subsidy" @keyup.enter="search">
                        </td>
                        <td class="p-1">
                            <input type="text" class="border rounded border-gray-300 text-sm p-1 w-full" v-model="form.search.user" @keyup.enter="search">
                        </td>
                        <td class="p-1">
                            <button class="bg-green-500 hover:bg-green-300 text-white px-2 py-1 rounded text-sm" @click="search">Search</button>
                        </td>
                    </tr>
                    <tr v-for="(payment, index) in payments.data" :key="payment.id">
                        <td class="whitespace-nowrap p-2 text-sm font-medium text-gray-500" :class="{ 'bg-gray-100': index % 2 === 0 }">{{ payment.id }}</td>
                        <td class="whitespace-nowrap p-2 text-sm text-gray-500" :class="{ 'bg-gray-100': index % 2 === 0 }">{{ formatDate(payment.created_at, 'DD/MM/YYYY') }}</td>
                        <td class="whitespace-nowrap p-2 text-sm text-gray-500" :class="{ 'bg-gray-100': index % 2 === 0 }">{{ payment.reference }}</td>
                        <td class="whitespace-nowrap p-2 text-sm text-gray-500" :class="{ 'bg-gray-100': index % 2 === 0 }">
                            <a v-if="payment.parentable?.id" :href="route(parentRouteName, payment.parentable_id)" class="text-blue-600 hover:text-blue-900">
                                {{ payment.parentable.id }}
                            </a>
                            <span v-else>
                                {{ payment.parentable_id }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap p-4 text-sm text-gray-500" :class="{ 'bg-gray-100': index % 2 === 0 }">
                            {{ payment.first_name }} {{ payment.last_name }}
                        </td>
                        <td class="whitespace-nowrap p-2 text-sm text-gray-500" :class="{ 'bg-gray-100': index % 2 === 0 }">{{ payment.addresses ? payment.addresses[0]?.postCode : '-' }}</td>
                        <td class="whitespace-nowrap p-2 text-sm text-gray-500" :class="{ 'bg-gray-100': index % 2 === 0 }">{{ formatCurrency(payment.amount) }}</td>
                        <td class="whitespace-nowrap p-2 text-sm text-gray-500" :class="{ 'bg-gray-100': index % 2 === 0 }">{{ formatCurrency(payment.deposit) }}</td>
                        <td class="whitespace-nowrap p-2 text-sm text-gray-500" :class="{ 'bg-gray-100': index % 2 === 0 }">{{ payment.apr ? payment.apr + '%' : '-' }}</td>
                        <td class="whitespace-nowrap p-2 text-sm text-gray-500" :class="{ 'bg-gray-100': index % 2 === 0 }">{{ payment.term }}</td>
                        <td class="whitespace-nowrap p-2 text-sm text-gray-500" :class="{ 'bg-gray-100': index % 2 === 0 }">{{ payment.deferred ?? '-' }}</td>
                        <td class="whitespace-nowrap p-2 text-sm text-gray-500" :class="{ 'bg-gray-100': index % 2 === 0 }">{{ payment.payment_status?.name }}</td>
                        <td class="whitespace-nowrap p-2 text-sm text-gray-500" :class="{ 'bg-gray-100': index % 2 === 0 }">{{ payment.payment_provider?.name }}</td>
                        <td class="whitespace-nowrap p-2 text-sm text-gray-500" :class="{ 'bg-gray-100': index % 2 === 0 }">{{ formatCurrency(payment.subsidy) }}</td>
                        <td class="whitespace-nowrap p-2 text-sm text-gray-500" :class="{ 'bg-gray-100': index % 2 === 0 }">{{ payment.parentable?.user?.name }}</td>
                        <td class="relative whitespace-nowrap p-2 text-right text-sm font-medium" :class="{ 'bg-gray-100': index % 2 === 0 }">
                            <Link :href="route('payments.show', {payment: payment})" class="bg-blue-500 hover:bg-blue-300 text-white px-2 py-1 rounded text-sm">
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

<style scoped>
/* Make sortable headers show pointer like DataTables */
th.sorting, th.sorting_asc, th.sorting_desc {
    cursor: pointer;
}

:before, :after {
    --tw-content: "";
}

th {
    box-sizing: content-box;
}

th.sorting, th.sorting_asc, th.sorting_desc
{
    position: relative;
}

th.sorting_asc:after
{
    content: "▲" / "";
    position: absolute;
    right: 10px;
    font-size: 10px;
}

th.sorting_desc:after
{
    content: "▼" / "";
    position: absolute;
    right: 10px;
    font-size: 10px;
}

</style>

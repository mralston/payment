<script setup>

import { useForm } from '@inertiajs/vue3';
import {Link} from "@inertiajs/vue3";

const props = defineProps({
    parentModel: Object,
    financeSurvey: Object,
    customers: Array,
});

const form = useForm({
    customers: props.customers,
});

function addCustomer() {
    form.customers.push({
        firstName: null,
        lastName: null,
    });
}

function removeCustomer(index) {
    form.customers.splice(index, 1);
}

function submit()
{
    form.submit('post', route('finance.surveys.store', {parent: props.parentModel}));
}

console.log(props.financeSurvey);

</script>

<template>

    <div class="p-4">

        <h1 class="text-4xl font-bold">Survey</h1>

        <p class="mt-4">We need to ask you a few basic questions so that we can find out which payment methods are right for you.</p>

        <h2 class="text-xl font-bold mt-4">Section 1: Customers</h2>

        <button type="button"
                class="mt-4 rounded bg-blue-600 px-2 py-1 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                @click="addCustomer">
            Add Customer
        </button>

        <div class="grid grid-cols-3 gap-6 mt-4">

            <div v-for="(customer, index) in form.customers" class="divide-y divide-gray-200 overflow-hidden rounded-lg bg-white shadow">
                <div class="px-3 py-2 font-bold bg-blue-50">
                    Customer {{ index + 1 }}
                    <button type="button"
                            class="float-end rounded bg-red-600 px-1.5 py-0.5 text-xs font-bold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                            @click="removeCustomer(index)">
                        X
                    </button>

                </div>
                <div class="bg-gray-50 p-4">
                    <div>
                        <label :for="firstName + '.' + index" class="block text-sm/6 font-medium text-gray-900">First name</label>
                        <div class="mt-2">
                            <input type="text" v-model="customer.firstName" :id="firstName + '.' + index" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                        </div>
                    </div>
                    <div class="mt-5">
                        <label :for="lastName + '.' + index" class="block text-sm/6 font-medium text-gray-900">Last name</label>
                        <div class="mt-2">
                            <input type="text" v-model="customer.lastName" :id="lastName + '.' + index" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <button type="button"
                class="mt-4 rounded bg-blue-600 px-2 py-1 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                @click="submit">
            Continue
        </button>

        <p v-if="financeSurvey == undefined" class="mt-4">
            <Link :href="route('finance.choose-method', {parent: props.parentModel})"
                  class="text-gray-500">
                I don't wanna tell you this yet. Just show me the options
            </Link>
        </p>


    </div>

</template>

<style scoped>

</style>

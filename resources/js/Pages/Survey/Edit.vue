<script setup>

import { useForm } from '@inertiajs/vue3';
import {Link} from "@inertiajs/vue3";
import {router} from "@inertiajs/vue3";
import { Head } from '@inertiajs/vue3'
import AddressInput from "../../Components/AddressInput.vue";

const props = defineProps({
    parentModel: Object,
    financeSurvey: Object,
    customers: Array,
    addresses: Array,
});

const form = useForm({
    customers: props.customers,
    addresses: props.addresses,
});

function addCustomer() {
    form.customers.push({
        firstName: null,
        middleName: null,
        lastName: null,
        email: null,
        phone: null,
        dateOfBirth: null,
        grossAnnualIncome: null,
        netMonthly: null,
        dependants: null,
        employmentStatus: null,
    });
}

function removeCustomer(index) {
    form.customers.splice(index, 1);
}

function addAddress() {
    form.addresses.push({
        houseNumber: null,
        street: null,
        address1: null,
        address2: null,
        town: null,
        county: null,
        postCode: null,
        timeAtAddress: 0,
    });
}

function removeAddress(index) {
    form.addresses.splice(index, 1);
}

function submit()
{
    if (props.financeSurvey === undefined) {
        return form.submit(
            'post',
            route(
                'finance.surveys.store',
                {
                    parent: props.parentModel,
                }
            )
        );
    }

    return form.submit(
        'patch',
        route(
            'finance.surveys.update',
            {
                parent: props.parentModel,
                survey: props.financeSurvey,
            }
        )
    );
}

function skip()
{
    router.get(route('finance.choose-payment-option', {parent: props.parentModel}));
}

</script>

<template>

    <Head>
        <title>Survey</title>
    </Head>

    <div class="p-4">

        <h1 class="text-4xl font-bold">Survey</h1>

        <p class="mt-4">We need to ask you a few basic questions so that we can find out which payment methods are right for you.</p>

        <section>

            <h2 class="text-xl font-bold mt-4">Section 1: Customers</h2>

            <button type="button"
                    class="mt-4 rounded bg-blue-600 px-2 py-1 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    @click="addCustomer">
                Add Customer
            </button>

            <div class="grid grid-cols-2 gap-6 mt-4">

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

                        <div class="grid grid-cols-3 gap-6">

                            <div>
                                <label :for="'firstName.' + index" class="block text-sm/6 font-medium text-gray-900">First name</label>
                                <div class="mt-2">
                                    <input type="text" v-model="customer.firstName" :id="'firstName.' + index" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                                </div>
                            </div>

                            <div>
                                <label :for="'middleName.' + index" class="block text-sm/6 font-medium text-gray-900">Middle name</label>
                                <div class="mt-2">
                                    <input type="text" v-model="customer.middleName" :id="'middleName.' + index" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                                </div>
                            </div>

                            <div>
                                <label :for="'lastName.' + index" class="block text-sm/6 font-medium text-gray-900">Last name</label>
                                <div class="mt-2">
                                    <input type="text" v-model="customer.lastName" :id="'lastName.' + index" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                                </div>
                            </div>

                        </div>

                        <div class="grid grid-cols-3 gap-6 mt-5">

                            <div>
                                <label :for="'email.' + index" class="block text-sm/6 font-medium text-gray-900">E-mail</label>
                                <div class="mt-2">
                                    <input type="text" v-model="customer.email" :id="'email.' + index" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                                </div>
                            </div>
                            <div>
                                <label :for="'phone.' + index" class="block text-sm/6 font-medium text-gray-900">Phone</label>
                                <div class="mt-2">
                                    <input type="text" v-model="customer.phone" :id="'phone.' + index" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                                </div>
                            </div>
                            <div>
                                <label :for="'dateOfBirth.' + index" class="block text-sm/6 font-medium text-gray-900">Date of Birth</label>
                                <div class="mt-2">
                                    <input type="date" v-model="customer.dateOfBirth" :id="'dateOfBirth.' + index" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                                </div>
                            </div>

                        </div>

                        <div class="grid grid-cols-2 gap-6 mt-5">

                            <div>
                                <label :for="'grossAnnualIncome.' + index" class="block text-sm/6 font-medium text-gray-900">Gross Annual Income</label>
                                <div class="mt-2">
                                    <input type="number" v-model="customer.grossAnnualIncome" :id="'grossAnnualIncome.' + index" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                                </div>
                            </div>

                            <div>
                                <label :for="'netMonthlyIncome.' + index" class="block text-sm/6 font-medium text-gray-900">Net Monthly Income</label>
                                <div class="mt-2">
                                    <input type="number" v-model="customer.netMonthlyIncome" :id="'netMonthlyIncome.' + index" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                                </div>
                            </div>

                        </div>

                        <div class="grid grid-cols-2 gap-6 mt-5">

                            <div>
                                <label :for="'employmentStatus.' + index" class="block text-sm/6 font-medium text-gray-900">Employment Status</label>
                                <div class="mt-2">
                                    <select v-model="customer.employmentStatus" :id="'employmentStatus.' + index" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                                        <option></option>
                                        <option value="100">Full Time</option>
                                        <option value="200">Part Time</option>
                                        <option value="300">Casual</option>
                                        <option value="300">Casual</option>
                                        <option value="400">Self Employed</option>
                                        <option value="500">Household Carer</option>
                                        <option value="600">Retired</option>
                                        <option value="700">Unemployed</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label :for="'dependants.' + index" class="block text-sm/6 font-medium text-gray-900">Dependants</label>
                                <div class="mt-2">
                                    <input type="number" v-model="customer.dependants" :id="'dependants.' + index" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>

        </section>

        <section>

            <h2 class="text-xl font-bold mt-4">Section 2: Addresses</h2>

            <p class="mt-4">We need a total of 3 years' address history.</p>

            <button type="button"
                    class="mt-4 rounded bg-blue-600 px-2 py-1 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    @click="addAddress">
                Add Address
            </button>

            <div class="grid grid-cols-3 gap-6 mt-4">

                <div v-for="(address, index) in form.addresses" class="divide-y divide-gray-200 overflow-hidden rounded-lg bg-white shadow">
                    <div class="px-3 py-2 font-bold bg-blue-50">
                        <span v-if="index === 0">Current Address</span>
                        <span v-else>Previous Address {{ index }}</span>
                        <button type="button"
                                class="float-end rounded bg-red-600 px-1.5 py-0.5 text-xs font-bold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                                @click="removeAddress(index)">
                            X
                        </button>

                    </div>
                    <div class="bg-gray-50 p-4">

                        <AddressInput v-model:address="form.addresses[index]" :index="index" />

                        <div class="mt-4">
                            <label :for="'timeAtAddress.' + index" class="block text-sm/6 font-medium text-gray-900">Time at address</label>
                            <div class="mt-2">
                                <input type="number" v-model="address.timeAtAddress" :id="'timeAtAddress.' + index" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </section>


        <div class="mt-4 mb-4">
            <button type="button"
                    class="rounded bg-blue-600 px-2 py-1 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    @click="submit">
                Continue
            </button>

            <button type="button"
                    class="float-end rounded bg-gray-600 px-2 py-1 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600"
                    @click="skip">
                Skip
            </button>
        </div>

    </div>

</template>

<style scoped>

</style>

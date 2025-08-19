<script setup>

import { useForm } from '@inertiajs/vue3';
import {router} from "@inertiajs/vue3";
import { Head } from '@inertiajs/vue3'
import AddressInput from "../../Components/AddressInput.vue";
import ValidationBanner from "../../Components/ValidationBanner.vue";
import ValidationWrapper from "../../Components/ValidationWrapper.vue";

const props = defineProps({
    parentModel: Object,
    paymentSurvey: Object,
    title: {
        type: String,
        default: 'Survey',
    },
    allowSkip: {
        type: Boolean,
        default: true,
    },
    showBasicQuestions: {
        type: Boolean,
        default: true,
    },
    showLeaseQuestions: {
        type: Boolean,
        default: false,
    },
    showFinanceQuestions: {
        type: Boolean,
        default: false,
    },
    basicIntroText: {
        type: String,
        default: 'We need to ask you a few basic questions so that we can find out which payment methods are right for you.',
    },
    leaseIntroText: {
        type: String,
        default: 'We need to ask a few more questions for your lease application.',
    },
    financeIntroText: {
        type: String,
        default: 'We need to ask a few more questions for your finance application.',
    },
    redirect: String,
    financeResponses: Object,
    employmentStatuses: Array,
    maritalStatuses: Array,
    residentialStatuses: Array,
    nationalities: Array,
    bankruptOrIvas: Array,
});

const form = useForm({
    redirect: props.redirect,
    basicQuestionsCompleted: props.showBasicQuestions,
    leaseQuestionsCompleted: props.showLeaseQuestions,
    financeQuestionsCompleted: props.showFinanceQuestions,
    customers: props.paymentSurvey.customers,
    addresses: props.paymentSurvey.addresses,
    financeResponses: props.paymentSurvey.finance_responses,
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
        dateMovedIn: null,
    });
}

function removeAddress(index) {
    form.addresses.splice(index, 1);
}

function submit()
{
    if (props.paymentSurvey.id === undefined) {
        return form.submit(
            'post',
            route(
                'payment.surveys.store',
                {
                    parent: props.parentModel,
                }
            )
        );
    }

    return form.submit(
        'patch',
        route(
            'payment.surveys.update',
            {
                parent: props.parentModel,
                survey: props.paymentSurvey,
            }
        )
    );
}

function skip()
{
    router.get(route('payment.options', {parent: props.parentModel}));
}

</script>

<template>

    <Head>
        <title>{{ title }}</title>
    </Head>

    <div class="p-4">

        <button v-if="allowSkip"
                type="button"
                class="float-end rounded bg-gray-600 px-2 py-1 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600"
                @click="skip">
            Skip
        </button>

        <ValidationBanner :form="form" class="mr-16"/>

        <h1 class="text-4xl font-bold mb-4">{{ title }}</h1>

        <section v-if="showBasicQuestions">

            <p class="mb-4">{{ basicIntroText }}</p>

            <h2 class="text-xl font-bold mb-4">Section 1: Customers</h2>

            <p class="mb-4">Please tell us about yourself and your partner; whoever is on your mortgage or lease agreement.</p>

            <ValidationWrapper :form="form" field="customers" class="mb-4">
                <button type="button"
                        class="rounded bg-blue-600 px-2 py-1 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                        @click="addCustomer">
                    Add Customer
                </button>
            </ValidationWrapper>

            <div class="grid grid-cols-2 gap-6 mb-4">

                <div v-for="(customer, index) in form.customers" class="divide-y divide-gray-200 overflow-hidden rounded-lg bg-white shadow">
                    <div class="px-3 py-2 font-bold bg-blue-50">
                        Customer {{ index + 1 }}
                        <button type="button"
                                class="float-end rounded bg-red-600 px-1.5 py-0.5 text-xs font-bold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                                @click="removeCustomer(index)">
                            X
                        </button>

                    </div>
                    <div class="bg-gray-50 p-4">

                        <div class="grid grid-cols-3 gap-6 mb-4">

                            <div class="mb-4">
                                <label :for="`customers.${index}.firstName`" class="block text-sm/6 font-medium text-gray-900">First name</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.firstName`">
                                    <input type="text" v-model="customer.firstName" :id="`customers.${index}.firstName`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                                </ValidationWrapper>
                            </div>

                            <div class="mb-4">
                                <label :for="`customers.${index}.middleName`" class="block text-sm/6 font-medium text-gray-900">Middle name</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.middleName`">
                                    <input type="text" v-model="customer.middleName" :id="`customers.${index}.middleName`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                                </ValidationWrapper>
                            </div>

                            <div class="mb-4">
                                <label :for="`customers.${index}.lastName`" class="block text-sm/6 font-medium text-gray-900">Last name</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.lastName`">
                                    <input type="text" v-model="customer.lastName" :id="`customers.${index}.lastName`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                                </ValidationWrapper>
                            </div>

                        </div>

                        <div class="grid grid-cols-4 gap-6 mb-4">

                            <div class="mb-4">
                                <label :for="`customers.${index}.email`" class="block text-sm/6 font-medium text-gray-900">E-mail</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.email`">
                                    <input type="text" v-model="customer.email" :id="`customers.${index}.email`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                                </ValidationWrapper>
                            </div>
                            <div class="mb-4">
                                <label :for="`customers.${index}.mobile`" class="block text-sm/6 font-medium text-gray-900">Mobile</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.mobile`">
                                    <input type="tel" v-model="customer.mobile" :id="`customers.${index}.mobile`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                                </ValidationWrapper>
                            </div>
                            <div class="mb-4">
                                <label :for="`customers.${index}.landline`" class="block text-sm/6 font-medium text-gray-900">Land Line</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.landline`">
                                    <input type="text" v-model="customer.landline" :id="`customers.${index}.landline`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                                </ValidationWrapper>
                            </div>
                            <div class="mb-4">
                                <label :for="`customers.${index}.dateOfBirth`" class="block text-sm/6 font-medium text-gray-900">Date of Birth</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.dateOfBirth`">
                                    <input type="date" v-model="customer.dateOfBirth" :id="`customers.${index}.dateOfBirth`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                                </ValidationWrapper>
                            </div>

                        </div>

                        <div class="grid grid-cols-2 gap-6 mb-4">

                            <div class="mb-4">
                                <label :for="`customers.${index}.grossAnnualIncome`" class="block text-sm/6 font-medium text-gray-900">Gross Annual Income</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.grossAnnualIncome`">
                                    <div class="flex items-center rounded-md bg-white pl-3 outline outline-1 -outline-offset-1 outline-gray-300 hover:outline-gray-300 has-[input:focus-within]:outline has-[input:focus-within]:outline-2 has-[input:focus-within]:-outline-offset-2 has-[input:focus-within]:outline-blue-600">
                                        <div class="shrink-0 select-none text-base text-gray-700 sm:text-sm/6">&pound;</div>
                                        <input type="number" step="0.01" v-model="customer.grossAnnualIncome" :id="`customers.${index}.grossAnnualIncome`" class="block min-w-0 grow py-1.5 pl-1 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline focus:outline-0 sm:text-sm/6 border-0" placeholder="0.00" />
                                    </div>
                                </ValidationWrapper>
                            </div>

                            <div class="mb-4">
                                <label :for="`customers.${index}.netMonthlyIncome`" class="block text-sm/6 font-medium text-gray-900">Net Monthly Income</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.netMonthlyIncome`">
                                    <div class="flex items-center rounded-md bg-white pl-3 outline outline-1 -outline-offset-1 outline-gray-300 hover:outline-gray-300 has-[input:focus-within]:outline has-[input:focus-within]:outline-2 has-[input:focus-within]:-outline-offset-2 has-[input:focus-within]:outline-blue-600">
                                        <div class="shrink-0 select-none text-base text-gray-700 sm:text-sm/6">&pound;</div>
                                        <input type="number" step="0.01" v-model="customer.netMonthlyIncome" :id="`customers.${index}.netMonthlyIncome`" class="block min-w-0 grow py-1.5 pl-1 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline focus:outline-0 sm:text-sm/6 border-0" placeholder="0.00" />
                                    </div>
                                </ValidationWrapper>
                            </div>

                        </div>

                        <div class="grid grid-cols-2 gap-6 mb-4">

                            <div class="mb-4">
                                <label :for="`customers.${index}.employmentStatus`" class="block text-sm/6 font-medium text-gray-900">Employment Status</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.employmentStatus`">
                                    <select v-model="customer.employmentStatus" :id="`customers.${index}.employmentStatus`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                                        <option></option>
                                        <option v-for="employmentStatus in employmentStatuses" :key="employmentStatuses.id" :value="employmentStatus.value">
                                            {{ employmentStatus.name }}
                                        </option>
                                    </select>
                                </ValidationWrapper>
                            </div>

                            <div class="mb-4">
                                <label :for="`customers.${index}.dependants`" class="block text-sm/6 font-medium text-gray-900">Dependants</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.dependants`">
                                    <input type="number" v-model="customer.dependants" :id="`customers.${index}.dependants`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                                </ValidationWrapper>
                            </div>

                        </div>

                    </div>
                </div>

            </div>

            <h2 class="text-xl font-bold mb-4">Section 2: Addresses</h2>

            <p class="mb-4">Please tell us where you're living now, and if you've not long moved in, where you were before that.<br>We need a total of 3 years' address history.</p>

            <ValidationWrapper :form="form" field="addresses" class="mb-4">
                <button type="button"
                        class="rounded bg-blue-600 px-2 py-1 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                        @click="addAddress">
                    Add Address
                </button>
            </ValidationWrapper>

            <div class="grid grid-cols-3 gap-6 mb-4">

                <div v-for="(address, index) in form.addresses" class="divide-y divide-gray-200 overflow-hidden rounded-lg bg-white shadow">
                    <div class="px-3 py-2 font-bold bg-blue-50">
                        <span v-if="index === 0">Current Address</span>
                        <span v-else>Previous Address {{ index }}</span>
                        <button type="button"
                                class="float-end rounded bg-red-600 px-1.5 py-0.5 text-xs font-bold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                                @click="removeAddress(index)">
                            X
                        </button>

                    </div>
                    <div class="bg-gray-50 p-4">

                        <ValidationWrapper :form="form" :field="[/*`addresses.${index}.houseNumber`, `addresses.${index}.street`, `addresses.${index}.address1`, `addresses.${index}.address2`, `addresses.${index}.town`, `addresses.${index}.county`,*/ `addresses.${index}.postcode`, `addresses.${index}.uprn`]" class="mb-4">
                            <AddressInput v-model:address="form.addresses[index]" :index="index"/>
                        </ValidationWrapper>

                        <div class="mb-4">
                            <label :for="`addresses.${index}.dateMovedIn`" class="block text-sm/6 font-medium text-gray-900">Approximate Move in Date</label>
                            <ValidationWrapper :form="form" :field="`addresses.${index}.dateMovedIn`">
                                <input type="date" v-model="address.dateMovedIn" :id="`addresses.${index}.dateMovedIn`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                            </ValidationWrapper>
                        </div>

                    </div>
                </div>

            </div>

        </section>

        <section v-if="showFinanceQuestions" class="mb-4">

            <p class="mb-4">{{ financeIntroText }}</p>

            <div class="grid grid-cols-3 gap-6 mb-4">

                <div class="divide-y divide-gray-200 overflow-hidden rounded-lg bg-gray-50 shadow mb-4">
                    <div class="px-3 py-2 font-bold bg-blue-50">
                        Personal Details
                    </div>
                    <div class="bg-gray-50 p-4">

                        <div class="mb-4">
                            <label for="maritalStatus" class="block text-sm/6 font-medium text-gray-900">Marital Status</label>
                            <ValidationWrapper :form="form" field="customers.0.maritalStatus">
                                <select v-model="form.customers[0].maritalStatus" id="maritalStatus" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                                    <option></option>
                                    <option v-for="maritalStatus in maritalStatuses" :key="maritalStatuses.id" :value="maritalStatus.value">
                                        {{ maritalStatus.name }}
                                    </option>
                                </select>
                            </ValidationWrapper>
                        </div>

                        <div class="mb-4">
                            <label for="residentialStatus" class="block text-sm/6 font-medium text-gray-900">Residential Status</label>
                            <ValidationWrapper :form="form" field="customers.0.residentialStatus">
                                <select v-model="form.customers[0].residentialStatus" id="residentialStatus" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                                    <option></option>
                                    <option v-for="residentialStatus in residentialStatuses" :key="residentialStatus.id" :value="residentialStatus.value">
                                        {{ residentialStatus.name }}
                                    </option>
                                </select>
                            </ValidationWrapper>
                        </div>


                        <div class="mb-4">
                            <label for="maritalStatus" class="block text-sm/6 font-medium text-gray-900">Nationality</label>
                            <ValidationWrapper :form="form" field="customers.0.nationality">
                                <select v-model="form.customers[0].nationality" id="nationality" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                                    <option></option>
                                    <option v-for="nationality in nationalities" :key="nationality.id" :value="nationality.value">
                                        {{ nationality.name }}
                                    </option>
                                </select>
                            </ValidationWrapper>
                        </div>

                        <div class="mb-4">
                            <label for="bankruptOrIva" class="block text-sm/6 font-medium text-gray-900">Have you ever been declared bankrupt or entered into an Individual Voluntary Agreement (IVA)?</label>
                            <ValidationWrapper :form="form" field="customers.0.bankruptOrIva">
                                <select v-model="form.customers[0].bankruptOrIva" id="bankruptOrIva" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                                    <option></option>
                                    <option v-for="bankruptOrIva in bankruptOrIvas" :key="bankruptOrIva.id" :value="bankruptOrIva.value">
                                        {{ bankruptOrIva.name }}
                                    </option>
                                </select>
                            </ValidationWrapper>
                        </div>

                        <div class="grid grid-cols-2 gap-6 mb-4">

                            <div class="mb-4">
                                <label :for="`financeResponses.monthlyMortgage`" class="block text-sm/6 font-medium text-gray-900">Monthly Mortgage</label>
                                <ValidationWrapper :form="form" :field="`financeResponses.monthlyMortgage`">
                                    <div class="flex items-center rounded-md bg-white pl-3 outline outline-1 -outline-offset-1 outline-gray-300 hover:outline-gray-300 has-[input:focus-within]:outline has-[input:focus-within]:outline-2 has-[input:focus-within]:-outline-offset-2 has-[input:focus-within]:outline-blue-600">
                                        <div class="shrink-0 select-none text-base text-gray-700 sm:text-sm/6">&pound;</div>
                                        <input type="number" step="0.01" v-model="form.financeResponses.monthlyMortgage" :id="`financeResponses.monthlyMortgage`" class="block min-w-0 grow py-1.5 pl-1 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline focus:outline-0 sm:text-sm/6 border-0" placeholder="0.00" />
                                    </div>
                                </ValidationWrapper>
                            </div>

                            <div class="mb-4">
                                <label :for="`financeResponses.monthlyRent`" class="block text-sm/6 font-medium text-gray-900">Monthly Rent</label>
                                <ValidationWrapper :form="form" :field="`financeResponses.monthlyRent`">
                                    <div class="flex items-center rounded-md bg-white pl-3 outline outline-1 -outline-offset-1 outline-gray-300 hover:outline-gray-300 has-[input:focus-within]:outline has-[input:focus-within]:outline-2 has-[input:focus-within]:-outline-offset-2 has-[input:focus-within]:outline-blue-600">
                                        <div class="shrink-0 select-none text-base text-gray-700 sm:text-sm/6">&pound;</div>
                                        <input type="number" step="0.01" v-model="form.financeResponses.monthlyRent" :id="`financeResponses.monthlyRent`" class="block min-w-0 grow py-1.5 pl-1 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline focus:outline-0 sm:text-sm/6 border-0" placeholder="0.00" />
                                    </div>
                                </ValidationWrapper>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="divide-y divide-gray-200 overflow-hidden rounded-lg bg-gray-50 shadow mb-4">
                    <div class="px-3 py-2 font-bold bg-blue-50">
                        Occupation
                    </div>
                    <div class="bg-gray-50 p-4">

                        <div class="mb-4">
                            <label for="customers.0.employmentStatus" class="block text-sm/6 font-medium text-gray-900">Employment Status</label>
                            <ValidationWrapper :form="form" field="customers.0.employmentStatus" class="mt-2">
                                <select v-model="form.customers[0].employmentStatus" :id="`customers.0.employmentStatus`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                                    <option></option>
                                    <option v-for="employmentStatus in employmentStatuses" :key="employmentStatuses.id" :value="employmentStatus.value">
                                        {{ employmentStatus.name }}
                                    </option>
                                </select>
                            </ValidationWrapper>
                        </div>

                        <div class="mb-4">
                            <label for="occupation" class="block text-sm/6 font-medium text-gray-900">Occupation</label>
                            <ValidationWrapper :form="form" field="financeResponses.occupation" class="mt-2">
                                <input type="text" v-model="form.financeResponses.occupation" id="occupation" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                            </ValidationWrapper>
                        </div>

                        <div class="mb-4">
                            <label for="employerName" class="block text-sm/6 font-medium text-gray-900">Employer name</label>
                            <ValidationWrapper :form="form" field="financeResponses.employerName" class="mt-2">
                                <input type="text" v-model="form.financeResponses.employerName" id="employerName" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                            </ValidationWrapper>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm/6 font-medium text-gray-900">Employer Address</label>
                            <ValidationWrapper :form="form" :field="['financeResponses.employerAddress.houseNumber', 'financeResponses.employerAddress.street', 'financeResponses.employerAddress.address1', 'financeResponses.employerAddress.address2', 'financeResponses.employerAddress.town', 'financeResponses.employerAddress.county', 'financeResponses.employerAddress.postCode', 'financeResponses.employerAddress.uprn']" class="mt-2">
                                <AddressInput v-model:address="form.financeResponses.employerAddress" :index="index" :showHouseNumber="false" class="mb-4"/>
                            </ValidationWrapper>
                        </div>

                        <div class="mb-4">
                            <label for="dateStartedEmployment" class="block text-sm/6 font-medium text-gray-900">Approximate Start Date</label>
                            <ValidationWrapper :form="form" field="financeResponses.dateStartedEmployment">
                                <input type="date" v-model="form.financeResponses.dateStartedEmployment" id="dateStartedEmployment" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                            </ValidationWrapper>
                        </div>

                    </div>
                </div>

                <div class="divide-y divide-gray-200 overflow-hidden rounded-lg bg-gray-50 shadow">
                    <div class="px-3 py-2 font-bold bg-blue-50">
                        Bank Details
                    </div>
                    <div class="bg-gray-50 p-4">

                        <div class="mb-4">
                            <label for="bankName" class="block text-sm/6 font-medium text-gray-900">Bank Name</label>
                            <ValidationWrapper :form="form" field="financeResponses.bankAccount.bankName">
                                <input type="text" v-model="form.financeResponses.bankAccount.bankName" class="w-full p-1 border-gray-300 rounded invalid:bg-red-100 placeholder:text-gray-300">
                            </ValidationWrapper>
                        </div>

                        <div class="mb-4">
                            <label for="accountName" class="block text-sm/6 font-medium text-gray-900">Account Name</label>
                            <ValidationWrapper :form="form" field="financeResponses.bankAccount.accountName">
                                <input type="text" v-model="form.financeResponses.bankAccount.accountName" class="w-full p-1 border-gray-300 rounded invalid:bg-red-100 placeholder:text-gray-300">
                            </ValidationWrapper>
                        </div>

                        <div class="mb-4">
                            <label for="accountNumber" class="block text-sm/6 font-medium text-gray-900">Account Number</label>
                            <ValidationWrapper :form="form" field="financeResponses.bankAccount.accountNumber">
                                <input type="text" v-model="form.financeResponses.bankAccount.accountNumber" class="w-full p-1 border-gray-300 rounded invalid:bg-red-100 placeholder:text-gray-300" pattern="\d{8}" placeholder="12345678">
                            </ValidationWrapper>
                        </div>

                        <div class="mb-4">
                            <label for="sortCode" class="block text-sm/6 font-medium text-gray-900">Sort Code</label>
                            <ValidationWrapper :form="form" field="financeResponses.bankAccount.sortCode">
                                <input type="text" v-model="form.financeResponses.bankAccount.sortCode" class="w-full p-1 border-gray-300 rounded invalid:bg-red-100 placeholder:text-gray-300" pattern="\d{2}-\d{2}-\d{2}|\d{6}" placeholder="12-34-56">
                            </ValidationWrapper>
                        </div>

                    </div>
                </div>



            </div>

        </section>


        <div class="my-4 text-end">
            <button type="button"
                    class="rounded-md bg-blue-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                    @click="submit">
                Continue
            </button>
        </div>

    </div>

</template>

<style scoped>
INPUT, SELECT, TEXTAREA
{
    border-color: inherit;
}
</style>

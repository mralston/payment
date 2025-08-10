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
    customers: Array,
    addresses: Array,
    employmentStatuses: Array,
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
        dateMovedIn: 0,
    });
}

function removeAddress(index) {
    form.addresses.splice(index, 1);
}

function submit()
{
    if (props.paymentSurvey === undefined) {
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
        <title>Survey</title>
    </Head>

    <div class="p-4">

        <button type="button"
                class="float-end rounded bg-gray-600 px-2 py-1 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600"
                @click="skip">
            Skip
        </button>

        <ValidationBanner :form="form" class="mr-16"/>

        <h1 class="text-4xl font-bold">Survey</h1>

        <p class="mt-4 text-xl">We need to ask you a few basic questions so that we can find out which payment methods are right for you.</p>

        <section>

            <h2 class="text-xl font-bold mt-4">Section 1: Customers</h2>

            <p class="mt-4">Please tell us about yourself and your partner; whoever is on your mortgage or lease agreement.</p>

            <ValidationWrapper :form="form" field="customers" class="mt-4">
                <button type="button"
                        class="rounded bg-blue-600 px-2 py-1 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                        @click="addCustomer">
                    Add Customer
                </button>
            </ValidationWrapper>

            <div class="grid grid-cols-2 gap-6 mt-4">

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

                        <div class="grid grid-cols-3 gap-6">

                            <div>
                                <label :for="`customers.${index}.firstName`" class="block text-sm/6 font-medium text-gray-900">First name</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.firstName`" class="mt-2">
                                    <input type="text" v-model="customer.firstName" :id="`customers.${index}.firstName`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                                </ValidationWrapper>
                            </div>

                            <div>
                                <label :for="`customers.${index}.middleName`" class="block text-sm/6 font-medium text-gray-900">Middle name</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.middleName`" class="mt-2">
                                    <input type="text" v-model="customer.middleName" :id="`customers.${index}.middleName`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                                </ValidationWrapper>
                            </div>

                            <div>
                                <label :for="`customers.${index}.lastName`" class="block text-sm/6 font-medium text-gray-900">Last name</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.lastName`" class="mt-2">
                                    <input type="text" v-model="customer.lastName" :id="`customers.${index}.lastName`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                                </ValidationWrapper>
                            </div>

                        </div>

                        <div class="grid grid-cols-3 gap-6 mt-5">

                            <div>
                                <label :for="`customers.${index}.email`" class="block text-sm/6 font-medium text-gray-900">E-mail</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.email`" class="mt-2">
                                    <input type="text" v-model="customer.email" :id="`customers.${index}.email`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                                </ValidationWrapper>
                            </div>
                            <div>
                                <label :for="`customers.${index}.phone`" class="block text-sm/6 font-medium text-gray-900">Phone</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.phone`" class="mt-2">
                                    <input type="text" v-model="customer.phone" :id="`customers.${index}.phone`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                                </ValidationWrapper>
                            </div>
                            <div>
                                <label :for="`customers.${index}.dateOfBirth`" class="block text-sm/6 font-medium text-gray-900">Date of Birth</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.dateOfBirth`" class="mt-2">
                                    <input type="date" v-model="customer.dateOfBirth" :id="`customers.${index}.dateOfBirth`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                                </ValidationWrapper>
                            </div>

                        </div>

                        <div class="grid grid-cols-2 gap-6 mt-5">

                            <div>
                                <label :for="`customers.${index}.grossAnnualIncome`" class="block text-sm/6 font-medium text-gray-900">Gross Annual Income</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.grossAnnualIncome`" class="mt-2">
                                    <div class="flex items-center rounded-md bg-white pl-3 outline outline-1 -outline-offset-1 outline-gray-300 hover:outline-gray-300 has-[input:focus-within]:outline has-[input:focus-within]:outline-2 has-[input:focus-within]:-outline-offset-2 has-[input:focus-within]:outline-blue-600">
                                        <div class="shrink-0 select-none text-base text-gray-700 sm:text-sm/6">&pound;</div>
                                        <input type="number" v-model="customer.grossAnnualIncome" :id="`customers.${index}.grossAnnualIncome`" class="block min-w-0 grow py-1.5 pl-1 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline focus:outline-0 sm:text-sm/6 border-0" placeholder="0.00" />
                                    </div>
                                </ValidationWrapper>
                            </div>



                            <div>
                                <label :for="`customers.${index}.netMonthlyIncome`" class="block text-sm/6 font-medium text-gray-900">Net Monthly Income</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.netMonthlyIncome`" class="mt-2">
                                    <div class="flex items-center rounded-md bg-white pl-3 outline outline-1 -outline-offset-1 outline-gray-300 hover:outline-gray-300 has-[input:focus-within]:outline has-[input:focus-within]:outline-2 has-[input:focus-within]:-outline-offset-2 has-[input:focus-within]:outline-blue-600">
                                        <div class="shrink-0 select-none text-base text-gray-700 sm:text-sm/6">&pound;</div>
                                        <input type="number" v-model="customer.netMonthlyIncome" :id="`customers.${index}.netMonthlyIncome`" class="block min-w-0 grow py-1.5 pl-1 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline focus:outline-0 sm:text-sm/6 border-0" placeholder="0.00" />
                                    </div>
                                </ValidationWrapper>
                            </div>

                        </div>

                        <div class="grid grid-cols-2 gap-6 mt-5">

                            <div>
                                <label :for="`customers.${index}.employmentStatus`" class="block text-sm/6 font-medium text-gray-900">Employment Status</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.employmentStatus`" class="mt-2">
                                    <select v-model="customer.employmentStatus" :id="`customers.${index}.employmentStatus`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                                        <option></option>
                                        <option v-for="employmentStatus in employmentStatuses" :key="employmentStatuses.id" :value="employmentStatus.value">
                                            {{ employmentStatus.name }}
                                        </option>
                                    </select>
                                </ValidationWrapper>
                            </div>

                            <div>
                                <label :for="`customers.${index}.dependants`" class="block text-sm/6 font-medium text-gray-900">Dependants</label>
                                <ValidationWrapper :form="form" :field="`customers.${index}.dependants`" class="mt-2">
                                    <input type="number" v-model="customer.dependants" :id="`customers.${index}.dependants`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
                                </ValidationWrapper>
                            </div>

                        </div>

                    </div>
                </div>

            </div>

        </section>

        <section>

            <h2 class="text-xl font-bold mt-4">Section 2: Addresses</h2>

            <p class="mt-4">Please tell us where you're living now, and if you've not long moved in, where you were before that.<br>We need a total of 3 years' address history.</p>

            <ValidationWrapper :form="form" field="addresses" class="mt-4">
                <button type="button"
                        class="rounded bg-blue-600 px-2 py-1 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                        @click="addAddress">
                    Add Address
                </button>
            </ValidationWrapper>

            <div class="grid grid-cols-3 gap-6 mt-4">

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

                        <ValidationWrapper :form="form" :field="[`addresses.${index}.houseNumber`, `addresses.${index}.street`, `addresses.${index}.address1`, `addresses.${index}.address2`, `addresses.${index}.town`, `addresses.${index}.county`, `addresses.${index}.postcode`]" class="mt-2">
                            <AddressInput v-model:address="form.addresses[index]" :index="index" />
                        </ValidationWrapper>

                        <div class="mt-4">
                            <label :for="`addresses.${index}.dateMovedIn`" class="block text-sm/6 font-medium text-gray-900">Date moved in</label>
                            <ValidationWrapper :form="form" :field="`addresses.${index}.dateMovedIn`" class="mt-2">
                                <input type="date" v-model="address.dateMovedIn" :id="`addresses.${index}.dateMovedIn`" class="block w-full rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 border-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6" />
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

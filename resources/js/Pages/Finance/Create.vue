<script setup>

import {Head, router, useForm} from "@inertiajs/vue3";
import OverpaymentCalculator from "../../Components/OverpaymentCalculator.vue";
import { makeNumeric } from "../../Helpers/Number.js";
import {diffInMonths, formatDate, fromNow, monthsYears} from "../../Helpers/Date.js";
import {cleanUrl} from "../../Helpers/Strings.js";
import RepresentativeExample from "../../Components/RepresentativeExample.vue";
import {ExclamationTriangleIcon} from "@heroicons/vue/20/solid/index.js";
import ValidationWrapper from "../../Components/ValidationWrapper.vue";
import ValidationBanner from "../../Components/ValidationBanner.vue";

const props = defineProps({
    parentModel: Object,
    survey: Object,
    offer: Object,
    totalCost: Number,
    deposit: Number,
    companyDetails: Object,
    lenders: Array,
    maritalStatuses: Array,
    employmentStatuses: Array,
    residentialStatuses: Array,
    nationalities: Array,
});

const form = useForm({
    offerId: props.offer.id,
    maritalStatus: props.survey.customers[0].maritalStatus,
    residentialStatus: props.survey.customers[0].residentialStatus,
    nationality: props.survey.customers[0].nationality,
    accountNumber: null,
    sortCode: null,
    readTermsConditions: false,
    eligible: false,
    gdprOptIn: false,
});

function submit()
{
    form.post(route('payment.finance.store', {
        parent: props.parentModel
    }));
}

</script>

<template>

    <Head>
        <title>Finance</title>
    </Head>

    <div class="p-4">

        <ValidationBanner :form="form"/>

        <h1 class="text-4xl font-bold mb-6">
            Finance
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-8 gap-4">
            <div class="md:col-span-5 order-2 md:order-1">
                <h2 class="text-2xl mb-4">Key Information</h2>
                <p class="mb-4">
                    {{ companyDetails.legalName }}
                    is a broker and works with a number of lenders to help customers apply for finance to assist their purchase.
                    Credit is provided from selection of lenders:
                    <span v-for="(lender, index) in lenders">
                        {{ lender.name }}<span v-if="index < lenders.length - 2">, </span><span v-else-if="index < lenders.length - 1"> and </span>
                    </span>.
                </p>
                <h2 class="text-2xl mb-4">Eligibility</h2>
                <p class="mb-4">
                    To apply you will need to be:
                </p>
                <ul class="list-disc list-inside mb-4">
                    <li>18 years or over</li>
                    <li>A UK resident</li>
                    <li>Employed/self-employed for 16 hours or more a week or retired with pension</li>
                    <li>Able to demonstrate the loan is affordable</li>
                    <li>Have a UK bank account that accepts Direct Debits</li>
                </ul>

                <ValidationWrapper :form="form" field="eligible" class="mb-4">
                    <input type="checkbox" v-model="form.eligible" id="eligible" class="mr-2">
                    <label for="eligible"><b>I confirm that I meet these eligibility requirements.</b></label>
                </ValidationWrapper>

            </div>
            <div class="md:col-span-3 order-1 md:order-2">
                <h2 class="text-2xl mb-4">Overpayments Estimator</h2>
                <OverpaymentCalculator
                    :loan_amount="totalCost - deposit"
                    :apr="makeNumeric(offer.apr)"
                    :total_payable="makeNumeric(offer.total_payable)"
                    :default_loan_term="makeNumeric(offer.term)"
                    :default_monthly_payment="makeNumeric(offer.monthly_payment)"
                    :deferred_period="makeNumeric(offer.deferred)"/>
            </div>
        </div>

        <h2 class="text-2xl mb-4">How will my data be used?</h2>

        <p class="mb-4">
            We may share information about you with credit reference and fraud preventions agencies. This includes
            requesting verification of information provided by you to assist in making future lending decisions and may
            include up to two credit searches.
        </p>
        <p class="mb-4">
            Credit reference agencies will keep a record of any searches or information shared with then. This information
            may be shared with other lenders, and other organisations. Fraud prevention agencies may share the information
            with other organisations including law enforcement to prevent fraud and money laundering.
        </p>

        <h3 class="text-xl mb-4">Privacy policies</h3>

        <table class="mb-4 w-1/2">
            <tbody>
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">{{ companyDetails.commonName }}</th>
                    <td class="bg-gray-100 p-1"><a :href="companyDetails.privacyPolicy" target="_blank">{{ cleanUrl(companyDetails.privacyPolicy) }}</a></td>
                </tr>
                <tr v-for="(lender, index) in lenders">
                    <th class="p-1 mr-2" :class="{ 'bg-gray-100': index % 2 === 1 }">{{ lender.name }}</th>
                    <td class="p-1" :class="{ 'bg-gray-100': index % 2 === 1 }"><a :href="lender.privacy_policy" target="_blank">{{ cleanUrl(lender.privacy_policy) }}</a></td>
                </tr>
            </tbody>
        </table>


        <ValidationWrapper :form="form" field="gdprOptIn" class="mb-4">
            <input type="checkbox" v-model="form.gdprOptIn" id="gdpr_opt_in" class="mr-2">
            <label for="gdpr_opt_in"><b>I agree to my personal data being used as part of my loan application as described above.</b></label>
        </ValidationWrapper>

        <h2 class="text-2xl mb-4">Your Loan Application</h2>

        <p class="mb-4">Please take a moment to review your application details and fill in the missing information.</p>


        <table class="mb-4 w-1/2">
            <tbody>
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">Your name</th>
                    <td class="bg-gray-100 p-1">
                        {{ survey.customers[0].title }}
                        {{ survey.customers[0].firstName }}
                        {{ survey.customers[0].lastName }}
                    </td>
                </tr>
                <tr>
                    <th class="p-1 mr-2">Marital status</th>
                    <td class="p-1">
                        <ValidationWrapper :form="form" field="maritalStatus">
                            <select v-model="form.maritalStatus" id="maritalStatus" class="block w-1/2 rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                                <option></option>
                                <option v-for="maritalStatus in maritalStatuses" :key="maritalStatuses.id" :value="maritalStatus.value">
                                    {{ maritalStatus.name }}
                                </option>
                            </select>
                        </ValidationWrapper>
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">Homeowner</th>
                    <td class="bg-gray-100 p-1">
                        <ValidationWrapper :form="form" field="residentialStatus">
                            <select v-model="form.residentialStatus" id="residentialStatus" class="block w-1/2 rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                                <option></option>
                                <option v-for="residentialStatus in residentialStatuses" :key="residentialStatus.id" :value="residentialStatus.value">
                                    {{ residentialStatus.name }}
                                </option>
                            </select>
                        </ValidationWrapper>
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">Nationality</th>
                    <td class="bg-gray-100 p-1">
                        <ValidationWrapper :form="form" field="nationality">
                            <select v-model="form.nationality" id="nationality" class="block w-1/2 rounded-md bg-white px-2 py-1 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                                <option></option>
                                <option v-for="nationality in nationalities" :key="nationality.id" :value="nationality.value">
                                    {{ nationality.name }}
                                </option>
                            </select>
                        </ValidationWrapper>
                    </td>
                </tr>
                <tr>
                    <th class="p-1 mr-2">Date of birth</th>
                    <td class="p-1">
                        {{ formatDate(survey.customers[0].dateOfBirth, 'DD/MM/YYYY') }}
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">Dependants</th>
                    <td class="bg-gray-100 p-1">
                        {{ survey.customers[0].dependants }}
                    </td>
                </tr>
                <tr>
                    <th class="p-1 mr-2">Telephone</th>
                    <td class="p-1">
                        {{ survey.customers[0].phone }}
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">E-mail</th>
                    <td class="bg-gray-100 p-1">
                        {{ survey.customers[0].email }}
                    </td>
                </tr>
                <tr>
                    <th class="p-1 mr-2 align-top">Address</th>
                    <td class="p-1">
                        <div v-if="survey.addresses[0].houseNumber || survey.addresses[0].street">
                            {{ survey.addresses[0].houseNumber }} {{ survey.addresses[0].street }}
                        </div>
                        <div v-if="survey.addresses[0].address1">{{ survey.addresses[0].address1 }}</div>
                        <div v-if="survey.addresses[0].address2">{{ survey.addresses[0].address2 }}</div>
                        <div v-if="survey.addresses[0].town">{{ survey.addresses[0].town }}</div>
                        <div v-if="survey.addresses[0].county">{{ survey.addresses[0].county }}</div>
                        <div v-if="survey.addresses[0].postCode">{{ survey.addresses[0].postCode }}</div>
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">Time at address</th>
                    <td class="bg-gray-100 p-1">
                        {{ fromNow(survey.addresses[0].dateMovedIn, true) }}
                    </td>
                </tr>
                <tr>
                    <th class="p-1 mr-2">Employment status</th>
                    <td class="p-1">
                        {{ employmentStatuses.find(status => status.value === survey.customers[0].employmentStatus)?.name }}
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">Account number</th>
                    <td class="bg-gray-100 p-1">
                        <ValidationWrapper :form="form" field="accountNumber">
                            <input type="text" v-model="form.accountNumber" class="p-1 border-gray-500 rounded invalid:bg-red-100 placeholder:text-gray-300" pattern="\d{8}" placeholder="12345678">
                        </ValidationWrapper>
                    </td>
                </tr>
                <tr>
                    <th class="p-1 mr-2">Sort code</th>
                    <td class="p-1">
                        <ValidationWrapper :form="form" field="sortCode">
                            <input type="text" v-model="form.sortCode" class="p-1 border-gray-500 rounded invalid:bg-red-100 placeholder:text-gray-300" pattern="\d{2}-\d{2}-\d{2}|\d{6}" placeholder="12-34-56">
                        </ValidationWrapper>
                    </td>
                </tr>
            </tbody>
        </table>

        <h2 class="text-2xl mb-4">Important Information</h2>

        <p class="mb-4">
            Please read the following important information before submitting your loan application.
        </p>
        <ul class="list-disc list-inside mb-4">
            <li>You are satisfied that the monthly payment fits your budget and is affordable over the life of the loan.</li>
            <li>You have considered any potential changes to your personal circumstances when considering the affordability of the loan e.g. redundancy, retirement, starting a family, etc.</li>
            <li>The application details which you have entered above are correct.</li>
            <li>The minimum payment must be made every month. If you miss payments you may incur additional charges and your ability to obtain credit in the future could be negatively impacted.</li>
            <li>When you click to submit your loan application, your credit file will be searched by {{ lenders[0].name }} and a record left of the search.</li>
        </ul>

        <ValidationWrapper :form="form" field="readTermsConditions" class="mb-4">
            <input type="checkbox" v-model="form.readTermsConditions" id="read_terms_conditions" class="mr-2">
            <label for="read_terms_conditions"><b>I confirm that I have read and understood the important information.</b></label>
        </ValidationWrapper>

        <RepresentativeExample class="w-3/4"
                               :amount="makeNumeric(offer.amount)"
                               :deposit="makeNumeric(deposit)"
                               :term="makeNumeric(offer.term)"
                               :apr="makeNumeric(offer.apr)"
                               :first-payment="makeNumeric(offer.first_payment)"
                               :monthly-payment="makeNumeric(offer.monthly_payment)"
                               :final-payment="makeNumeric(offer.final_payment)"
                               :total-payable="makeNumeric(offer.total_payable)" />

        <div class="text-right">
            <button @click="submit" class="mt-10 rounded-md bg-blue-600 px-3 py-2 text-center text-sm/6 font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                Submit Finance Application
            </button>
        </div>

    </div>

</template>

<style scoped>

</style>

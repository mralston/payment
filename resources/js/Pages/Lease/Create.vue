<script setup>

import {Head, router} from "@inertiajs/vue3";
import {cleanUrl} from "../../Helpers/Strings.js";
import {formatDate, fromNow} from "../../Helpers/Date.js";
import {makeNumeric} from "../../Helpers/Number.js";
import RepresentativeExample from "../../Components/RepresentativeExample.vue";

const props = defineProps({
    parentModel: Object,
    survey: Object,
    offer: Object,
    totalCost: Number,
    deposit: Number,
    companyDetails: Object,
    lenders: Array,
    employmentStatuses: Array,
});

function submit()
{
    router.post(route('payment.lease.store', {
        parent: props.parentModel
    }));
}

</script>

<template>

    <Head>
        <title>Lease</title>
    </Head>

    <div class="p-4">

        <h1 class="text-4xl font-bold mb-6">
            {{ offer.payment_provider.name }}
        </h1>

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

        <div class="mb-4">
            <input type="checkbox" id="eligible" class="mr-2">
            <label for="eligible"><b>I confirm that I meet these eligibility requirements.</b></label>
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

        <table class="mb-4 w-full md:w-1/2">
            <tbody>
                <tr>
                    <th class="bg-gray-100 p-1 mr-2">{{ companyDetails.commonName }}</th>
                    <td class="bg-gray-100 p-1"><a :href="companyDetails.privacyPolicy" target="_blank">{{ cleanUrl(companyDetails.privacyPolicy) }}</a></td>
                </tr>
                <tr>
                    <th class="p-1 mr-2">{{ offer.payment_provider.name }}</th>
                    <td class="p-1"><a :href="offer.payment_provider.privacy_policy" target="_blank">{{ cleanUrl(offer.payment_provider.privacy_policy) }}</a></td>
                </tr>
            </tbody>
        </table>


        <div class="mb-4">
            <input type="checkbox" id="gdpr_opt_in" class="mr-2">
            <label for="gdpr_opt_in"><b>I agree to my personal data being used as part of my loan application as described above.</b></label>
        </div>

        <h2 class="text-2xl mb-4">Your Lease Application</h2>

        <p class="mb-4">Please take a moment to review your application details and important information below.</p>


        <table class="mb-4 w-full md:w-1/2">
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
            </tbody>
        </table>

        <h2 class="text-2xl mb-4">Important Information</h2>

        <p class="mb-4">
            Please read the following important information before submitting your loan application.
        </p>
        <ul class="list-disc list-inside mb-4">
            <li>You are satisfied that the monthly repayment fits your budget and is affordable over the life of the loan.</li>
            <li>You have considered any potential changes to your personal circumstances when considering the affordability of the loan e.g. redundancy, retirement, starting a family, etc.</li>
            <li>The application details which you have entered above are correct.</li>
            <li>The minimum payment must be made every month. If you miss payments you may incur additional charges and your ability to obtain credit in the future could be negatively impacted.</li>
            <li>When you click to submit your loan application, your credit file will be searched by {{ offer.payment_provider.name }} and a record left of the search.</li>
        </ul>

        <div class="mb-4">
            <input type="checkbox" id="read_terms_conditions" class="mr-2">
            <label for="read_terms_conditions"><b>I confirm that I have read and understood the important information.</b></label>
        </div>

        <RepresentativeExample class=" w-full md:w-3/4"
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
                Submit Lease Application
            </button>
        </div>

    </div>

</template>

<style scoped>

</style>

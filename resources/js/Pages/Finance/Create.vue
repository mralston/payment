<script setup>

import {Head} from "@inertiajs/vue3";

const props = defineProps({
    companyName: {
        type: String,
        default: 'Project Solar UK Ltd'
    },
    lenders: {
        type: Array,
        default: [
            {
                name: 'Tandem',
                privacyPolicy: 'https://www.tandem.co.uk/privacy-policy'
            },
            {
                name: 'Propensio',
                privacyPolicy: 'https://www.propensio.co.uk/privacy-policy'
            },
            {
                name: 'HSBC',
                privacyPolicy: 'https://www.hsbc.co.uk/privacy-policy'
            },
            {
                name: 'JN Bank',
                privacyPolicy: 'https://www.jnbank.co.uk/privacy-policy'
            }
        ]
    },
    privacyPolicy: {
        type: String,
        default: 'https://www.projectsolaruk.com/privacy-policy'
    }
});

</script>

<template>

    <Head>
        <title>Finance</title>
    </Head>

    <div class="p-4">

        <h1 class="text-4xl font-bold">
            Finance
        </h1>

        <div class="grid grid-cols-2">
            <div>
                <h1 class="mb-4">Key Information</h1>

                <p>
                    {{ companyName }}
                    is a broker and works with a number of lenders to help customers apply for finance to assist their purchase.
                    Credit is provided from selection of lenders:
                    <span v-for="(lender, index) in lenders">
                        {{ lender.name }}<span v-if="index < lenders - 1">, </span>
                    </span>.
                </p>

                <h2 class="mb-4">Eligibility</h2>

                <p>
                    To apply you will need to be:
                </p>
                <ul>
                    <li>18 years or over</li>
                    <li>A UK resident</li>
                    <li>Employed/self-employed for 16 hours or more a week or retired with pension</li>
                    <li>Able to demonstrate the loan is affordable</li>
                    <li>Have a UK bank account that accepts Direct Debits</li>
                </ul>
                <p>
                    Please confirm your meet these eligibility requirements.
                </p>

                <div class="btn-group btn-group-toggle pull-right" data-toggle="buttons">
                    <label class="btn btn-primary form-check-label" for="eligible_true">
                        <input type="radio" autocomplete="off" name="eligible" id="eligible_true" value="1">
                        Yes
                    </label>
                    <label class="btn btn-primary form-check-label" for="eligible_false">
                        <input type="radio" autocomplete="off" name="eligible" id="eligible_false" value="0">
                        No
                    </label>
                </div>

            </div>
            <div>

                Repayments calculator here.

            </div>
        </div>



        <h2 class="mb-4">How will my data be used?</h2>

        <p>
            We may share information about you with credit reference and fraud preventions agencies. This includes
            requesting verification of information provided by you to assist in making future lending decisions and may
            include up to two credit searches.
        </p>
        <p>
            Credit reference agencies will keep a record of any searches or information shared with then. This information
            may be shared with other lenders, and other organisations. Fraud prevention agencies may share the information
            with other organisations including law enforcement to prevent fraud and money laundering.
        </p>
        <p>
            Information on our privacy policy can be found at
            <a :href="privacyPolicy" target="_blank">{{ privacyPolicy }}</a>
            and our lenders
            <span v-for="(lender, index) in lenders">
                {{ lender.name }} at
                <a :href="lender.privacy_policy" target="_blank">{{ lender.privacy_policy }}</a><span v-if="index < lenders.length - 2">, </span><span v-else-if="index < lenders.length - 1"> and </span>
            </span>
            and are inline with the General Data Protection Regulation (GDPR).
        </p>

        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="gdpr_opt_in">
            <label class="custom-control-label" for="gdpr_opt_in"><b>I agree to my personal data being used as part of my loan application as described above.</b></label>
        </div>

        <div class="card mb-4">
            <div class="card-header">Your Loan Application</div>
            <div class="card-body">
                <p>
                    Please take a moment to review your application details and important information below.
                </p>

                <div class="row">
                    <div class="col-8">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                            <tr>
                                <th>Your name</th>
                                <td>

                                </td>
                            </tr>
                            <tr>
                                <th>Marital status</th>
                                <td>

                                </td>
                            </tr>
                            <tr>
                                <th>Homeowner</th>
                                <td>

                                </td>
                            </tr>
                            <tr>
                                <th>Mortgage</th>
                                <td>

                                </td>
                            </tr>
                            <tr>
                                <th>Nationality</th>
                                <td>

                                </td>
                            </tr>
                            <tr>
                                <th>Date of birth</th>
                                <td>

                                </td>
                            </tr>
                            <tr>
                                <th nowrap="nowrap">Number of dependants</th>
                                <td>

                                </td>
                            </tr>
                            <tr>
                                <th>Telephone</th>
                                <td>

                                </td>
                            </tr>
                            <tr>
                                <th>E-mail</th>
                                <td>

                                </td>
                            </tr>
                            <tr>
                                <th>Address</th>
                                <td>

                                </td>
                            </tr>
                            <tr>
                                <th>Time at address</th>
                                <td>

                                </td>
                            </tr>
                            <tr>
                                <th>Employment status</th>
                                <td>

                                </td>
                            </tr>
                            <tr>
                                <th>Account number</th>
                                <td>

                                </td>
                            </tr>
                            <tr>
                                <th>Sort code</th>
                                <td>

                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Important Information</div>
            <div class="card-body">
                <p>
                    Please read the following important information before submitting your loan application.
                </p>
                <ul class="mb-0">
                    <li>You are satisfied that the monthly repayment fits your budget and is affordable over the life of the loan.</li>
                    <li>You have considered any potential changes to your personal circumstances when considering the affordability of the loan e.g. redundancy, retirement, starting a family, etc.</li>
                    <li>The application details which you have entered above are correct.</li>
                    <li>The minimum payment must be made every month. If you miss payments you may incur additional charges and your ability to obtain credit in the future could be negatively impacted.</li>
                    <li>When you click to submit your loan application, your credit file will be searched by {{ lenders[0].name }} and a record left of the search.</li>
                </ul>
            </div>
        </div>

        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="read_terms_conditions">
            <label class="custom-control-label" for="read_terms_conditions"><b>I confirm that I have read and understood the important information.</b></label>
        </div>

        <p>
            Representative example here.
        </p>

        <button class="mt-10 rounded-md bg-blue-600 px-3 py-2 text-center text-sm/6 font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
            Submit Finance Application
        </button>

    </div>

</template>

<style scoped>

</style>

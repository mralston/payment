<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import DetailsRow from '../../Components/Details/DetailsRow.vue';
import moment from 'moment';
import { Icon } from '@iconify/vue';
import {formatCurrency} from "../../Helpers/Currency.js";
import Card from '../../Components/Card.vue';
import Button from '../../Components/Button.vue';
import Modal from '../../Components/Modal.vue';
import Banner from '../../Components/Details/Banner.vue';

const props = defineProps({
    payment: Object,
    products: Array,
});

const payment = ref(props.payment);

const cancelModal = ref(null);

const loan = props.payment.term + ' months' + (props.payment.apr > 0 ? ' at ' + props.payment.apr + '%' : ' Interest free') + ' APR'
        + (props.payment.deferred !== null ? ' + ' + props.payment.deferred + ' months deferred' : '');

const status = ref(props.payment.payment_status.identifier);

function cancelPayment()
{
    fetch(route('payment.cancel', { 
        parent: props.payment.id,
        payment: props.payment.id
    }), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            payment_status_identifier: 'cancelled',
        }),
    })
    .then(response => response.json())
    .then(data => {
        status.value = 'cancelled'; // Update the local ref instead of the prop
    })
    .catch(error => {
        console.error('Failed to cancel payment:', error);
    });
}

function getSigningLink()
{
    fetch(route('payment.finance.signing-link', { 
        payment: props.payment.id 
    }), {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
        .then(response => response.json())
        .then(data => {
            window.open(data.url, '_blank');
        });
}

</script>

<template>
    <Head>
        <title>Payment</title>
    </Head>

    <Modal
        ref="cancelModal"
        type="question"
        title="Cancel Payment"
        :buttons="['ok', 'cancel']"
        @ok="cancelPayment"
    />

    <div class="flex max-w-7xl mx-auto max-md:flex-col text-sm p-10 gap-4">
        <div class="w-1/4 max-md:w-full">
            <div class="p-10 bg-white border border-[#ed6058] rounded-lg">
                <h1 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:file-invoice" /> Summary</h1>
                <div class="mt-10 flex flex-col gap-8">
                    <Button v-if="payment.payment_status.identifier === 'accepted'" type="success" @click="getSigningLink()">Get Signing Link</Button>
                    <DetailsRow 
                        icon="fa6-solid:file-invoice"
                        :stack="true"
                        label="Application #"
                        :value="payment.reference"
                    />
                    <DetailsRow
                        icon="fa6-solid:user"
                        :stack="true" label="Customer"
                        :value="payment.first_name + ' ' + payment.last_name" />
                    <DetailsRow
                        icon="fa6-solid:hand-holding-dollar"
                        :stack="true"
                        label="Loan"
                        :value="loan"
                    />
                    <DetailsRow
                        icon="fa6-solid:building"
                        :stack="true"
                        label="Lender"
                        :value="payment.payment_provider.name"
                    />
                    <DetailsRow
                        icon="fa6-solid:circle-check"
                        :stack="true"
                        label="Status"
                        :value="payment.payment_status.name"
                    />
                    <DetailsRow
                        icon="fa6-solid:solar-panel"
                        :stack="true"
                        label="Purpose of loan"
                        value="Home Improvements"
                    />
                    <DetailsRow
                        icon="fa6-solid:user"
                        :stack="true"
                        label="Consultant ref"
                        :value="payment.parentable?.user?.name" />
                    <Button type="delete" @click="cancelModal.show()">Cancel</Button>
                </div>
            </div>

        </div>
        <div class="w-3/4 max-md:w-full flex flex-col gap-4">

            <Banner :type="status === 'cancelled' ? 'error' : 'success'">
                <span v-if="status === 'cancelled'">Payment cancelled</span>
                <span v-else-if="payment.signed_at">Agreement signed on: <span class="font-bold">{{ moment(payment.signed_at).format('DD/MM/YYYY') }}</span></span>
                <span v-else>Agreement not signed</span>
            </Banner>
            
            <Card class="border border-[#ed6058]" header-class="border-[#ed6058]">
                <template #header>
                    <div class="flex flex-row gap-2 items-center">
                        <h2 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:money-bill-wave" /> Loan</h2>
                    </div>
                </template>
                <div class="bg-white">
                    <div class="flex flex-row gap-4 max-md:flex-col">
                        <div class="w-1/2 max-md:w-full">
                            <DetailsRow
                                label="Advance amount"
                                :value="formatCurrency(payment.amount)"
                            />
                            <DetailsRow
                                label="Term (months)"
                                :value="String(payment.term)"
                            />
                            <DetailsRow
                                label="Cash price"
                                :value="formatCurrency(payment.amount)"
                            />
                            <DetailsRow
                                label="Deposit"
                                :value="formatCurrency(payment.deposit)"
                            />
                            <DetailsRow
                                label="Goods"
                                :value="products.map(product => product.name).join(', ')"
                            />
                            <DetailsRow
                                label="Subsidy"
                                :value="formatCurrency(payment.subsidy)"
                            />
                            <DetailsRow
                                label="Monthly repayment"
                                :value="formatCurrency(payment.monthly_payment)"
                            />
                            <DetailsRow
                                label="Monthly interest rate"
                                :value="String(payment.apr / 12) + '&percnt;'"
                            />
                            <DetailsRow
                                label="APR"
                                :value="String(payment.apr) + '&percnt;'" />
                            <DetailsRow
                                label="Total charge for credit"
                                :value="formatCurrency(payment.total_payable - payment.amount)"
                            />
                            <DetailsRow
                                label="Total amount repayable"
                                :value="formatCurrency(payment.total_payable)"    
                            />
                        </div>
                        <div class="w-1/2 max-md:w-full">
                            <DetailsRow
                                label="Application submitted date"
                                :value="moment(payment.submitted_at).format('DD/MM/YYYY')"
                            />
                            <DetailsRow
                                label="Agreement signed"
                                :value="payment.signed_at ? 'Yes' : 'No'"
                            />
                            <DetailsRow
                                label="Sat note signed"
                                :value="payment.sat_note_file_id ? 'Yes' : 'No'"
                            />
                            <DetailsRow
                                label="Offer expiry date"
                                :value="moment(payment.offer_expiration_date).format('DD/MM/YYYY')"
                            />
                            <DetailsRow
                                label="Agreement signed date"
                                :value="moment(payment.signed_at).format('DD/MM/YYYY')"
                            />
                            <DetailsRow
                                label="Cancellation expiry date"
                                :value="payment.decision_received_at && payment.payment_status.identifier !== 'declined' ?
                                    moment(payment.decision_received_at).add(12, 'days').format('DD/MM/YYYY') : ''"
                            />
                        </div>
                    </div>
                </div>
            </Card>
            
            <Card class="border border-[#ed6058]" header-class="border-[#ed6058]">
                <template #header>
                    <div class="flex flex-row gap-2 items-center">
                        <h2 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:user" /> Applicant Details</h2>
                    </div>
                </template>
                <div class="flex flex-row gap-4 max-md:flex-col">
                    <div class="w-1/2 max-md:w-full">
                        <DetailsRow
                            label="Title"
                            :value="payment.title"
                        />
                        <DetailsRow
                            label="First name"
                            :value="payment.first_name"
                        />
                        <DetailsRow
                            label="Middle name"
                            :value="payment.middle_name"
                        />
                        <DetailsRow
                            label="Last name"
                            :value="payment.last_name"
                        />
                        <DetailsRow
                            label="Email"
                            :value="payment.email"
                        />
                        <DetailsRow
                            label="Primary phone"
                            :value="payment.primary_telephone"
                        />
                        <DetailsRow
                            label="Secondary phone"
                            :value="payment.secondary_telephone"
                        />
                    </div>
                    <div class="w-1/2 max-md:w-full">
                        <DetailsRow
                            label="Date of birth"
                            :value="moment(payment.date_of_birth).format('DD/MM/YYYY')"
                        />
                        <DetailsRow
                            label="Marital status"
                            :value="payment.marital_status"
                        />
                        <DetailsRow
                            label="Number of dependents"
                            :value="payment.dependents" />
                    </div>
                </div>
            </Card>

            <Card class="border border-[#ed6058]" header-class="border-[#ed6058]">
                <template #header>
                    <div class="flex flex-row gap-2 items-center">
                        <h2 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:briefcase" /> Employment</h2>
                    </div>
                </template>
                <div class="bg-white">
                    <div class="flex flex-row gap-4 max-md:flex-col">
                        <div class="w-1/2 max-md:w-full">
                            <DetailsRow
                                label="Employment status"
                                :value="payment.employment_status"
                            />
                            <DetailsRow
                                label="Employer name"
                                :value="payment.employer_name"
                            />
                            <DetailsRow
                                label="Occupation"
                                :value="payment.occupation" />
                        </div>
                        <div class="w-1/2 max-md:w-full">
                            <DetailsRow
                                label="Time at employer - years"
                                :value="String(Math.floor(payment.time_with_employer / 12))"
                            />
                            <DetailsRow
                                label="Time at employer - months"
                                :value="String(payment.time_with_employer % 12)"
                            />
                        </div>
                    </div>
                </div>
            </Card>

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
                                :value="formatCurrency(payment.mortgage_monthly ?? 0)"
                            />
                            <DetailsRow
                                label="Rent (monthly)"
                                :value="formatCurrency(payment.rent_monthly ?? 0)"
                            />
                        </div>
                    </div>
                </div>
            </Card>

            <Card class="border border-[#ed6058]" header-class="border-[#ed6058]">
                <template #header>
                    <div class="flex flex-row gap-2 items-center">
                        <h2 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:house" /> Address History (Billing Address)</h2>
                    </div>
                </template>
                <div class="flex flex-row gap-4 max-md:flex-col">
                    <div
                        v-for="(address, key) in payment.addresses"
                        class="max-md:w-full"
                        :class="{
                            'w-full': payment.addresses.length === 1,
                            'w-1/2': payment.addresses.length === 2,
                            'w-1/3': payment.addresses.length === 3,
                        }"
                    >
                        <h2 v-if="key === 0" class="text-xl font-bold">Current Address</h2>
                        <h2 v-else class="text-xl font-bold">Previous Address</h2>
                        <DetailsRow
                            label="House"
                            :value="address.house_number"
                        />
                        <DetailsRow
                            label="Line 1"
                            :value="address.address1"
                        />
                        <DetailsRow
                            label="Line 2"
                            :value="address.address2"
                        />
                        <DetailsRow
                            label="Town"
                            :value="address.town"
                        />
                        <DetailsRow
                            label="County"
                            :value="address.county"
                        />
                        <DetailsRow
                            label="Post code"
                            :value="address.postcode"
                        />
                    </div>
                </div>
            </Card>

            <Card class="border border-[#ed6058]" header-class="border-[#ed6058]">
                <template #header>
                    <div class="flex flex-row gap-2 items-center">
                        <h2 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:credit-card" /> Bank Account</h2>
                    </div>
                </template>
                <div class="flex flex-row gap-4 max-md:flex-col">
                    <div class="w-1/2 max-md:w-full">
                        <DetailsRow label="Account Holder" :value="payment.bank_account_holder_name" />
                        <DetailsRow label="Sort code" :value="payment.bank_sort_code" />
                        <DetailsRow label="Account Number" :value="payment.bank_account_number" />
                    </div>
                    <div class="w-1/2 max-md:w-full">
                        <DetailsRow label="Bank name" :value="payment.bank_name" />
                    </div>
                </div>
            </Card>

            <Card class="border border-[#ed6058]" header-class="border-[#ed6058]">
                <template #header>
                    <div class="flex flex-row gap-2 items-center">
                        <h2 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:bullhorn" /> Marketing & Consent</h2>
                    </div>
                </template>
                <div class="flex flex-row gap-4 max-md:flex-col">
                    <div class="w-1/2 max-md:w-full">
                        <DetailsRow
                            label="Is over 21 years of age?"
                            :value="moment(payment.date_of_birth).isBefore(moment().subtract(21, 'years')) ? 'Yes' : 'No'"
                        />
                        <DetailsRow
                            label="Is UK resident?"
                            value="####"
                        />
                        <DetailsRow
                            label="Owns own property?"
                            value="###"
                        />
                        <DetailsRow
                            label="Is employed, self employed, or..."
                            :value="payment.employment_status"
                        />
                        <DetailsRow
                            label="Has a UK bank account"
                            :value="payment.bank_account_number ? 'Yes' : 'No'"
                        />
                    </div>
                    <div class="w-1/2 max-md:w-full">
                        <DetailsRow
                            label="Consent to credit search"
                            :value="payment.payment_status.name != 'new' ? 'Yes' : 'No'" />
                        <DetailsRow
                            label="Consent to terms"
                            :value="payment.read_terms_conditions ? 'Yes' : 'No'" />
                        <DetailsRow
                            label="Consent to marketing"
                            :value="payment.gdpr_opt_in ? 'Yes' : 'No'" />
                    </div>
                </div>
            </Card>
        </div>
    </div>

</template>

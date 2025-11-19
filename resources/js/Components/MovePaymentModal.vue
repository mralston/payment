<script setup>
import {computed, ref} from 'vue';
import axios from 'axios';
import { formatCurrency } from "../Helpers/Currency.js";
import Modal from "./Modal.vue";
import {router, useForm} from "@inertiajs/vue3";
import {ExclamationTriangleIcon, CheckCircleIcon} from "@heroicons/vue/20/solid/index.js";
import Banner from "./Banner.vue";

const props = defineProps({
    parentModelDescription: {
        type: String,
        default: 'Parent',
    },
});

const movePaymentModal = ref(null);

const payment = ref(null);

const parentableId = ref(null);
const isLoading = ref(false);
const responseMessages = ref([]);
const responseMessagesFoundPayments = ref([]);
const submitDisabled = ref(true);
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function findParentable() {
    if (!parentableId.value) {
        return;
    }

    isLoading.value = true;
    responseMessages.value = [{ type: 'loading', message: 'loading, please wait...' }];
    responseMessagesFoundPayments.value = [];
    submitDisabled.value = true;

    axios.get(route('payment.payments.move-check', {payment: payment.value.id, parentableId: parentableId.value}))
        .then(response => {
            isLoading.value = false;
            responseMessages.value = [];
            responseMessagesFoundPayments.value = [];
            if (response.data.error) {
                responseMessages.value.push({
                    type: 'danger',
                    message: response.data.error
                });

                if (response.data.compatibility) {
                    const compat = response.data.compatibility;

                    // Payment amount compatibility
                    const paymentMatch = compat.payment_amount == compat.parentable_total_cost;
                    responseMessages.value.push({
                        type: paymentMatch ? 'success' : 'danger',
                        message: 'Payment Amount: ' + formatCurrency(compat.payment_amount) + ' - ' + props.parentModelDescription + ' Total Cost: ' + formatCurrency(compat.parentable_total_cost)
                    });
                }

                return;
            }

            // Add success message
            responseMessages.value.push({
                type: 'success',
                message: 'Payment is compatible with the ' + props.parentModelDescription.toLowerCase() + '.'
            });

            // Display found finance applications
            if (Array.isArray(response.data)) {
                response.data.forEach(item => {
                    const isProblemStatus = item.payment_status.identifier === 'accepted' || item.payment_status.identifier === 'live';
                    responseMessagesFoundPayments.value.push({
                        type: isProblemStatus ? 'danger' : 'success',
                        message: `Found Payment: #${item.reference} (status: ${item.payment_status.identifier})`
                    });
                });
            }

            submitDisabled.value = false;
        })
        .catch(error => {
            isLoading.value = false;
            let errorMessage = 'Check failed.';

            if (error.response?.data?.error) {
                errorMessage = error.response.data.error;
            }

            if (error.response?.status === 404) {
                errorMessage = props.parentModelDescription + ' not found';
            }

            responseMessages.value = [{
                type: 'danger',
                message: errorMessage
            }];
            submitDisabled.value = true;
        });
}

function movePayment()
{
    router.post(route('payment.payments.move', {payment: payment.value.id, parentableId: parentableId.value}));
}

function show(p) {
    // Store payment
    payment.value = p;

    // Reset state
    parentableId.value = null;
    responseMessages.value = [];
    responseMessagesFoundPayments.value = [];
    submitDisabled.value = true;

    // Open modal component
    movePaymentModal.value.show();
}

defineExpose({ show });

</script>

<template>
    <Modal ref="movePaymentModal" title="Move Payment" :buttons="['ok', 'cancel']" @ok="movePayment" :disabled-buttons="submitDisabled ? ['ok'] : []">

        <p class="mb-2">
            <label for="parentable_id" class="font-normal">
                Which {{ parentModelDescription.toLowerCase() }} would you like to move the payment to?
            </label>
        </p>

        <div class="flex mb-4">
            <input
                type="text"
                :placeholder="parentModelDescription + ' ID'"
                class="border rounded border-gray-300 text-sm p-1 flex-1 mr-2"
                name="parentable_id"
                id="parentable_id"
                v-model="parentableId"
                @keyup.enter="findParentable">
            <button
                type="button"
                class="w-14 flex-none bg-blue-500 hover:bg-blue-400 disabled:bg-blue-300 text-white px-2 py-1 rounded text-sm"
                @click="findParentable"
                :disabled="isLoading || !parentableId">
                {{ isLoading ? 'Loading...' : 'Find' }}
            </button>
        </div>

        <Banner v-for="(msg, index) in responseMessages"
                :key="index"
                :type="msg.type === 'loading' ? 'info' : msg.type"
                class="mb-2">
            <CheckCircleIcon v-if="msg.type === 'success'" class="h-6 w-6 inline mr-2" aria-hidden="true"/>
            <ExclamationTriangleIcon v-if="msg.type === 'danger'" class="h-6 w-6 inline mr-2" aria-hidden="true"/>
            {{ msg.message }}
        </Banner>

        <div v-if="responseMessagesFoundPayments.length > 0">
            <p class="alert alert-warning">
                <ExclamationTriangleIcon class="h-6 w-6 inline mr-2" aria-hidden="true"/>
                If you proceed, all current payments listed below for {{ parentModelDescription.toLowerCase() }} #{{ parentableId }} will be cancelled.
            </p>
            <table class="w-full divide-y divide-gray-300">
                <thead>
                    <tr>
                        <th class="p-2 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="p-2 text-left text-sm font-semibold text-gray-900">Message</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="(msg, index) in responseMessagesFoundPayments" :key="index">
                        <td class="p-1" :class="{ 'bg-sky-100': msg.type === 'loading', 'bg-green-100': msg.type === 'success', 'bg-red-100': msg.type === 'danger', 'text-sky-600': msg.type === 'loading', 'text-green-600': msg.type === 'success', 'text-red-600': msg.type === 'danger' }">{{ msg.type === 'loading' ? 'Loading...' : msg.type }}</td>
                        <td class="p-1" :class="{ 'bg-sky-100': msg.type === 'loading', 'bg-green-100': msg.type === 'success', 'bg-red-100': msg.type === 'danger', 'text-sky-600': msg.type === 'loading', 'text-green-600': msg.type === 'success', 'text-red-600': msg.type === 'danger' }" v-html="msg.message"></td>
                    </tr>
                </tbody>
            </table>
        </div>

    </Modal>
</template>

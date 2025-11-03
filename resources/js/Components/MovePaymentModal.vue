<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { formatCurrency } from "../Helpers/Currency.js";

const props = defineProps({
    payment: {
        type: Object,
        required: true,
    },
    parentableName: {
        type: String,
        required: true,
    },
});

const parentableId = ref('');
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

    axios.get(route('payment.payments.move-check', {payment: props.payment.id, parentableId: parentableId.value}))
        .then(response => {
            console.log(response);
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
                        message: `${paymentMatch ? '<i class="fa fa-check-circle"></i> ' :
                            '<i class="fa fa-exclamation-triangle"></i> '}Payment Amount: ${formatCurrency(compat.payment_amount)}  -  Parentable Total Cost: ${formatCurrency(compat.parentable_total_cost)}`
                    });
                }

                return;
            }

            // Add success message
            responseMessages.value.push({
                type: 'success',
                message: '<i class="fa fa-check-circle"></i> Payment is compatible with the ' + props.parentableName.toLowerCase() + '.'
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
            
            responseMessages.value = [{
                type: 'danger',
                message: errorMessage
            }];
            submitDisabled.value = true;
        });
}

function submitForm() {
    const form = document.getElementById(`movePaymentForm_${props.payment.id}`);
    if (form) {
        form.submit();
    }
}

</script>

<template>
    <div class="modal fade" :id="`movePaymentModal${payment.id}`" tabindex="-1" role="dialog" aria-labelledby="movePaymentModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header flex items-center">
                    <h5 class="modal-title flex-1" id="formModalLabel">Move Payment {{ payment.reference }}</h5>
                    <button type="button" class="close ml-2" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form
                        :id="`movePaymentForm_${payment.id}`"
                        method="post"
                        :action="route('payment.payments.move', {payment: payment.id, parentableId: parentableId})">
                        <input type="hidden" name="_token" :value="csrfToken">
                        <div class="form-group">
                            <label for="field1">Which {{ parentableName }} would you like to move the payment to?</label>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-10">
                                        <input
                                            type="text"
                                            :placeholder="`${parentableName} ID`"
                                            class="form-control"
                                            name="parentable_id"
                                            :id="`${payment.id}_parentable_id`"
                                            v-model="parentableId">
                                    </div>
                                    <div class="col-md-2">
                                        <button 
                                            type="button" 
                                            class="btn btn-block btn-primary"
                                            @click="findParentable"
                                            :disabled="isLoading || !parentableId">
                                            {{ isLoading ? 'Loading...' : 'Find' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" class="form-control" name="payment_id" :value="payment.id">
                        </div>

                        <div :id="`movePaymentFormResponse_${payment.id}`">
                            <div 
                                v-for="(msg, index) in responseMessages" 
                                :key="index"
                                :class="`alert alert-${msg.type === 'loading' ? 'info' : msg.type}`"
                                v-html="msg.message">
                            </div>
                        </div>
                        <div v-if="responseMessagesFoundPayments.length > 0" :id="`movePaymentFormResponseFoundPayments_${payment.id}`">
                            <p class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> By clicking submit, all current payments listed below for {{ parentableName.toLowerCase() }} #{{ parentableId }} will be cancelled.</p>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(msg, index) in responseMessagesFoundPayments" :key="index">
                                        <td :class="`alert alert-${msg.type === 'loading' ? 'info' : msg.type}`">{{ msg.type === 'loading' ? 'Loading...' : msg.type }}</td>
                                        <td :class="`alert alert-${msg.type === 'loading' ? 'info' : msg.type}`" v-html="msg.message"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button
                        :id="`movePaymentFormSubmitButton_${payment.id}`"
                        :disabled="submitDisabled"
                        type="button"
                        class="btn btn-success"
                        @click="submitForm">
                        Proceed with payment move
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
.modal-title,
.modal-body {
    text-align: left !important;
}

.modal-body .alert {
    text-align: left !important;
}

.modal-body .form-group {
    text-align: left !important;
}
</style>



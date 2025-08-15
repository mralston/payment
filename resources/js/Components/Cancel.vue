<script setup>
import {onMounted, ref} from 'vue';
import Button from './Button.vue';
import { router } from '@inertiajs/vue3';
import Modal from "./Modal.vue";

const props = defineProps({
    payment: {
        type: Object,
        required: true,
    },
});

const cancelModal = ref(null);
const cancellationReason = ref();

function cancelPrompt()
{
    cancelModal.value.show();
}

function cancel(reason)
{
    if (!cancellationReason.value) {
        alert('You must provide a reason for cancelling this payment.');
        return;
    }

    router.post(route('payment.cancel', {
        parent: props.payment.parentable_id,
        payment: props.payment.id
    }), {
        payment_status_identifier: 'cancelled',
        cancellation_reason: cancellationReason.value,
        source: 'office',
    }/*, {
        onSuccess: () => {
            console.log('Payment cancelled');
        },
        onError: (errors) => {
            console.error('Failed to cancel payment:', errors);
        },
    }*/);
}

</script>

<template>
    <Modal title="Cancel Application" :buttons="['yes', 'no']" ref="cancelModal" @yes="cancel">
        <p class="text-sm text-gray-500">
            Are you sure you want to cancel this payment?
        </p>
        <div class="mt-4">
            <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                Reason
            </label>
            <textarea
                id="reason"
                v-model="cancellationReason"
                placeholder="Please provide a reason for cancelling this payment..."
                rows="3"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm"
            ></textarea>
        </div>
    </Modal>

    <Button
        v-if="!payment.payment_status.cancelled"
        type="delete"
        @click="cancelPrompt">
        Cancel
    </Button>
</template>

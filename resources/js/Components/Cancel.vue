<script setup>
import { ref } from 'vue';
import Button from './Button.vue';
import CancellationModal from './CancellationModal.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    payment: {
        type: Object,
        required: true,
    },
});

const showCancelModal = ref(false);
const isCancelling = ref(false);

function cancelPayment(reason) {
    isCancelling.value = true;
    router.post(route('payment.cancel', { 
        parent: props.payment.id,
        payment: props.payment.id
    }), {
        payment_status_identifier: 'cancelled',
        cancellation_reason: reason || null,
        source: 'rep',
    }, {
        onSuccess: () => {
            console.log('Payment cancelled');
            closeCancelModal();
            isCancelling.value = false;
        },
        onError: (errors) => {
            console.error('Failed to cancel payment:', errors);
            isCancelling.value = false;
        },
        onFinish: () => {
            isCancelling.value = false;
        }
    });
}
</script>

<template>
    <Button
        v-if="payment.payment_status.identifier !== 'cancelled'"
        type="delete"
        @click="showCancelModal = true">Cancel</Button>

    <CancellationModal
        :open="showCancelModal"
        @close="showCancelModal = false"
        @confirm="cancelPayment"
        :loading="isCancelling"/>
</template>
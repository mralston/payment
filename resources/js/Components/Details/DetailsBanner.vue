<script setup>
import Banner from '../Banner.vue';
import moment from 'moment';
import {computed} from "vue";

const props = defineProps({
    payment: Object,
});

const bannerType = computed(() => {
    if (props.payment.payment_status.identifier === 'cancelled') {
        return 'error';
    } else if (!props.payment.signed_at) {
        return 'warning';
    } else {
        return 'success';
    }
});

</script>

<template>
    <Banner :type="bannerType">
        <div v-if="payment.payment_status.identifier === 'cancelled'">
            Payment cancelled
            <div v-for="cancellation in payment.payment_cancellations" :key="cancellation.id">
                <b>{{ moment(cancellation.created_at).format('DD/MM/YYYY HH:mm') }}</b> {{ cancellation.user ? 'by ' + cancellation.user.name : '' }} - {{ cancellation.reason }}
            </div>
        </div>
        <span v-else-if="payment.signed_at">Agreement signed on: <span class="font-bold">{{ moment(payment.signed_at).format('DD/MM/YYYY') }}</span></span>
        <span v-else>Agreement not signed</span>
    </Banner>
</template>

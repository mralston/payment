<script setup>
import { Icon } from '@iconify/vue';
import DetailsRow from './DetailsRow.vue';
import SigningLink from '../SigningLink.vue';
import Cancel from '../Cancel.vue';
import SatNote from "../SatNote.vue";
import MovePaymentModal from "../MovePaymentModal.vue";
import Button from "../Button.vue";
import {ref} from "vue";

const props = defineProps({
    payment: {
        type: Object,
        required: true,
    },
    paymentType: {
        type: String,
        required: true,
    },
    paymentProviderSupportsRemoteSigning: Boolean,
    parentModelDescription: String,
});

const movePaymentModal = ref(null);

function movePayment(payment)
{
    movePaymentModal.value.show(payment);
}

</script>

<template>

    <MovePaymentModal ref="movePaymentModal" :parentModelDescription="parentModelDescription"/>

    <div class="p-10 bg-white border border-[#ed6058] rounded-lg">
        <h1 class="text-xl font-bold flex flex-row gap-2 items-center"><Icon icon="fa6-solid:file-invoice" /> Summary</h1>
        <div class="mt-10 flex flex-col gap-8">
            <SigningLink v-if="paymentProviderSupportsRemoteSigning" :payment="payment" />

            <SatNote :payment="payment"/>

            <DetailsRow
                icon="fa6-solid:file-invoice"
                :stack="true"
                label="Reference"
                :value="payment.reference"
            />
            <DetailsRow
                icon="fa6-solid:user"
                :stack="true" label="Customer"
                :value="payment.first_name + ' ' + payment.last_name" />
            <DetailsRow
                v-if="payment.payment_offer"
                icon="fa6-solid:hand-holding-dollar"
                :stack="true"
                :label="paymentType"
                :value="payment.payment_offer?.name ?? payment.apr + '% ' + payment.term + ' months' + (payment.deferred ? ' (' + payment.deferred + ' months deferred)' : '')"
            />
            <DetailsRow
                icon="fa6-solid:building"
                :stack="true"
                label="Provider"
                :value="payment.payment_provider.name"
            />
            <DetailsRow
                :type="payment.payment_status.identifier === 'cancelled' ? 'danger' : 'success'"
                icon="fa6-solid:circle-check"
                :stack="true"
                label="Status"
                :value="payment.payment_status.name"
            />
            <DetailsRow
                icon="fa6-solid:solar-panel"
                :stack="true"
                :label="'Purpose of ' + paymentType"
                value="Home Improvements"
            />
            <DetailsRow
                icon="fa6-solid:user"
                :stack="true"
                label="Consultant ref"
                :value="payment.parentable?.user?.name" />

            <Button
                type="warning"
                @click="movePayment(payment)"
                title="Move Payment">
                Move {{ parentModelDescription }}
            </Button>

            <Cancel :payment="payment" />
        </div>
    </div>
</template>

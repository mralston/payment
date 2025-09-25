<script setup>
import Button from './Button.vue';
import Modal from './Modal.vue';
import { ref } from 'vue';
import axios from "axios";

const props = defineProps({
    payment: {
        type: Object,
        required: true,
    },
});

const isGettingSigningLink = ref(false);
const signingLink = ref({ success: null, url: '' });
const signingLinkModal = ref(null);
const copyStatus = ref(null);
const sendStatus = ref(null);
const error = ref(null);

function getSigningLink()
{
    isGettingSigningLink.value = true;
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
            signingLink.value = {
                success: data.success,
                url: data.url,
            };
            error.value = data.error,
            signingLinkModal.value.show();
            isGettingSigningLink.value = false;
        })
        .catch(error => {
            console.error('Failed to get signing link:', error);
            signingLink.value = {
                success: false,
                url: ''
            };
            isGettingSigningLink.value = false;
        });
}

async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        copyStatus.value = 'Copied!';
        setTimeout(() => {
            copyStatus.value = '';
        }, 2000);
    } catch (err) {
        console.error('Failed to copy: ', err);
        copyStatus.value = 'Failed to copy';
        setTimeout(() => {
            copyStatus.value = '';
        }, 2000);
    }
}

function send()
{
    sendStatus.value = 'Sending...';

    axios.post(route('payment.send-remote-sign-link', props.payment))
        .then(response => {
            sendStatus.value = 'Sent';
        })
        .catch(error => {
            sendStatus.value = 'Failed';
        });
}

</script>

<template>
    <Button
        v-if="payment.payment_status.pending_signature"
        type="success"
        :loading="isGettingSigningLink"
        @click="getSigningLink()">Get Signing Link</Button>

    <Modal
        ref="signingLinkModal"
        type="info"
        title="Signing Link"
        class=""
        :buttons="['ok']"
    >
        <div class="flex flex-col gap-4">

            <div v-if="signingLink.success" class="w-full flex items-center gap-2 ">
                <div class="font-mono text-sm bg-gray-100 p-2 border rounded overflow-auto">{{ signingLink.url }}</div>
                <Button
                    class="bg-blue-500 text-white"
                    type="secondary"
                    size="sm"
                    @click="copyToClipboard(signingLink.url)">
                    {{ copyStatus || 'Copy' }}
                </Button>
                <Button
                    class="bg-green-500 text-white"
                    type="secondary"
                    size="sm"
                    @click="send"
                    :disabled="sendStatus === 'Sent'">
                    {{ sendStatus || 'Send' }}
                </Button>
            </div>
            <div v-else>
                <p v-if="!signingLink.success" class="mb-4">Failed to get signing link</p>
                <p v-if="error" class="text-red-500">{{ error }}</p>
            </div>

            <Button
                v-if="signingLink.success === null"
                type="success"
                :loading="isGettingSigningLink"
                @click="getSigningLink">Get Signing Link</Button>
        </div>
    </Modal>
</template>

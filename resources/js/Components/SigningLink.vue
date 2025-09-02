<script setup>
import Button from './Button.vue';
import Modal from './Modal.vue';
import { ref } from 'vue';

const props = defineProps({
    payment: {
        type: Object,
        required: true,
    },
});

const isGettingSigningLink = ref(false);
const signingLink = ref({ success: null, url: '' });
const signingLinkModal = ref(null);
const copyStatus = ref('');

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
                url: data.url
            };
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
</script>

<template>
    <Button
        v-if="payment.payment_status.identifier == 'accepted'"
        type="success"
        :loading="isGettingSigningLink"
        @click="getSigningLink()">Get Signing Link</Button>

    <Modal
        ref="signingLinkModal"
        type="question"
        title="Signing Link"
        class=""
        :buttons="['ok']"
    >
        <div class="flex flex-col gap-4">
            <p v-if="!signingLink.success">Failed to get signing link</p>
            <div v-else class="w-full flex items-center gap-2 border">
                <div class="w-2/3 font-mono text-sm bg-gray-100 p-2 rounded">{{ signingLink.url }}</div>
                <div class="w-1/3">
                    <Button
                        class="bg-blue-500 text-white"
                        type="secondary"
                        size="sm"
                        @click="copyToClipboard(signingLink.url)"
                    >
                        {{ copyStatus || 'Copy' }}
                    </Button>
                </div>
            </div>
            <Button
                v-if="signingLink.success === null" 
                type="success" 
                :loading="isGettingSigningLink"
                @click="getSigningLink">Get Signing Link</Button>
        </div>
    </Modal>
</template>
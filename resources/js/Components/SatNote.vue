<script setup>
import Button from './Button.vue';
import Modal from './Modal.vue';
import { ref } from 'vue';
import axios from "axios";
import {useForm} from "@inertiajs/vue3";

const props = defineProps({
    payment: Object,
});

const satNoteModal = ref(null);

const uploadFileField = ref(null);
const uploading = ref(false);
const uploadProgress = ref(0);
const uploadComplete = ref(false);

const satNoteFile = ref(props.payment.sat_note_file);

function prepareFile () {
    // Grab the file from the input field
    const file = uploadFileField.value.files[0];

    // Check it's not blank (possibly triggered when form element is reset)
    if (!file) {
        return;
    }

    // Check it's a video
    if (
        !file.type.match(/^image\//i) &&
        !file.type.match(/^application\/pdf/i)
    ) {
        alert('File must be an image or a PDF.\n\n' + 'File name: ' + file.name + '\n' + 'File type: ' + file.type);
        // Clear file upload field (yes, the .value.value is deliberate)
        uploadFileField.value.value = null;
        return false;
    }

    // Upload file
    uploadFile(file.name, file);
}

function uploadFile (filename, content) {
    // Create FormData object which will be uploaded
    const formData = new FormData();
    formData.append('sat_note', content, content.name);

    // Reset progress & show uploading spinner
    uploadProgress.value = 0;
    uploading.value = true;

    // Post the form data
    axios.request({
        method: 'POST', // Must be a POST request for PHP to read form data
        url: route('payments.upload_sat_note', props.payment),
        data: formData,
        headers: {
            'Content-Type': 'multipart/form-data'
        },
        onUploadProgress: function(progressEvent) {
            uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
        }
    }).then((response) => {
        console.log(response);

        // Upload complete
        uploadComplete.value = true;

        // Remove spinner
        uploading.value = false;

        satNoteFile.value = response.data;

        window.setTimeout(() => {
            satNoteModal.value.hide();
        }, 2000);
    }).catch((error) => {
        // Upload failed

        // Remove spinner
        uploading.value = false;

        // Extract message from error
        let errorMessage = error.message;

        // Set a more user-friendly message for known errors
        if (error.response.status === 413) {
            errorMessage = 'File was too large.';
        }

        // Show error message
        alert('Whoops!\n\n' + errorMessage);
        //handleAjaxError(error);

        // Clear file upload field (yes, the .value.value is deliberate)
        uploadFileField.value.value = null;
    });
}



</script>

<template>

    <a v-if="satNoteFile"
       :href="satNoteFile.url" target="_blank"
       class="px-4 py-2 rounded-lg flex items-center justify-center gap-2 bg-green-500 text-white">
        View Satisfaction Note
    </a>

    <Button v-else-if="payment.payment_status.identifier === 'parked'"
        type="success"
        @click="satNoteModal.show()">
        Upload Satisfaction Note
    </Button>

    <Modal
        ref="satNoteModal"
        type="info"
        title="Upload Satisfaction Note"
        class=""
        :buttons="['close']"
    >
        <div class="flex flex-col gap-4">

            <div v-if="uploading">{{ uploadProgress}}%</div>
            <div v-else-if="uploadComplete">Upload Complete!</div>
            <input v-else type="file" name="satNote" ref="uploadFileField" @change="prepareFile">

        </div>
    </Modal>
</template>

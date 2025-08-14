<script setup>
import { ref } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { QuestionMarkCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    open: Boolean,
});

const emit = defineEmits(['close', 'confirm']);

const reasonText = ref('');

function close() {
    reasonText.value = '';
    emit('close');
}

function confirm() {
    emit('confirm', reasonText.value);
    reasonText.value = '';
}
</script>

<template>
    <TransitionRoot as="template" :show="open">
        <Dialog as="div" class="relative z-50" @close="close">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:p-6">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10 bg-stone-100">
                                    <QuestionMarkCircleIcon class="h-6 w-6 text-stone-600" aria-hidden="true" />
                                </div>
                                <div class="mt-3 sm:ml-4 sm:mt-0">
                                    <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                                        Cancel Payment
                                    </DialogTitle>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Are you sure you want to cancel this payment?
                                        </p>
                                        <div class="mt-4">
                                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                                                Reason for cancellation
                                            </label>
                                            <textarea
                                                id="reason"
                                                v-model="reasonText"
                                                placeholder="Please provide a reason for cancelling this payment..."
                                                rows="3"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm"
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                <button
                                    type="button"
                                    class="inline-flex w-full justify-center rounded-md bg-sky-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-600 sm:col-start-2 ml-1" @click="confirm">Confirm</button>
                                <button
                                    type="button"
                                    class="inline-flex w-full justify-center rounded-md bg-stone-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-stone-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-stone-600 sm:col-start-2 ml-1" @click="close">Cancel</button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

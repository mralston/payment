<script setup>
import {computed, ref} from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import {
    InformationCircleIcon, // info
    CheckIcon, // success
    ExclamationTriangleIcon, // warning
    XMarkIcon, // danger
    QuestionMarkCircleIcon, // question
} from '@heroicons/vue/24/outline';

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    type: {
        type: String,
        default: 'info', // info, success, warning, danger, question
    },
    title: String,
    buttons: {
        type: Array,
        default: ['ok'],
    },
    disabledButtons: {
        type: Array,
        default: () => [], // e.g. ['ok'] to disable OK
    },
    custom1Text: String,
    custom2Text: String,
});

const emit = defineEmits([
    'ok',
    'cancel',
    'close',
    'yes',
    'no',
    'send',
    'submit',
    'custom1',
    'custom2',
]);

const computedTitle = computed(() => {
    if (props.title) {
        return props.title;
    }

    switch (props.type) {
        case 'success':
            return 'Success';
        case 'warning':
            return 'Warning';
        case 'danger':
            return 'Error';
        case 'question':
            return 'Question';
        default:
            return 'Information';
    }
});

const open = ref(false);

function show()
{
    open.value = true;
}

function hide()
{
    open.value = false;
}

function ok()
{
    open.value = false;
    emit('ok');
}

function cancel()
{
    open.value = false;
    emit('cancel');
}

function close()
{
    open.value = false;
    emit('close');
}

function yes()
{
    open.value = false;
    emit('yes');
}

function no()
{
    open.value = false;
    emit('no');
}

function send()
{
    open.value = false;
    emit('send');
}

function submit()
{
    open.value = false;
    emit('submit');
}

function custom1()
{
    open.value = false;
    emit('custom1');
}

function custom2()
{
    open.value = false;
    emit('custom2');
}

defineExpose({
    show,
    hide,
});

</script>

<template>
    <TransitionRoot as="template" :show="open">
        <Dialog as="div" class="relative z-50" @close="open = false">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel v-bind="$attrs" class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:p-6">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10"
                                     :class="{
                                        'bg-sky-100': type === 'info',
                                        'bg-emerald-100': type === 'success',
                                        'bg-amber-100': type === 'warning',
                                        'bg-red-100': type === 'danger',
                                        'bg-stone-100': type === 'question',
                                    }">
                                    <InformationCircleIcon v-if="type === 'info'" class="h-6 w-6 text-sky-600" aria-hidden="true" />
                                    <CheckIcon v-if="type === 'success'" class="h-6 w-6 text-emerald-600" aria-hidden="true" />
                                    <ExclamationTriangleIcon v-if="type === 'warning'" class="h-6 w-6 text-amber-600" aria-hidden="true" />
                                    <XMarkIcon v-if="type === 'danger'" class="h-6 w-6 text-red-600" aria-hidden="true" />
                                    <QuestionMarkCircleIcon v-if="type === 'question'" class="h-6 w-6 text-stone-600" aria-hidden="true" />
                                </div>
                                <div class="mt-3 sm:ml-4 sm:mt-0">
                                    <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                                        {{ computedTitle }}
                                    </DialogTitle>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            <slot/>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 sm:mt-4 sm:flex sm:justify-between">
                                <div class="flex">
                                    <button v-if="buttons.includes('custom1')" type="button" class="inline-flex w-full justify-center rounded-md bg-gray-400 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-stone-600 sm:w-auto" @click="custom1">{{ custom1Text }}</button>
                                    <button v-if="buttons.includes('custom2')" type="button" class="ml-2 inline-flex w-full justify-center rounded-md bg-gray-400 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-stone-600 sm:w-auto" @click="custom2">{{ custom2Text }}</button>
                                </div>

                                <div class="mt-3 flex sm:mt-0 sm:flex-row-reverse">
                                    <button v-if="buttons.includes('cancel')" type="button" class="inline-flex xw-full justify-center rounded-md bg-stone-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-stone-500 disabled:bg-stone-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-stone-600 sm:col-start-2 ml-1 disabled:tw-opacity-50 disabled:cursor-not-allowed" :disabled="disabledButtons.includes('cancel')" @click="cancel">Cancel</button>
                                    <button v-if="buttons.includes('close')" type="button" class="inline-flex xw-full justify-center rounded-md bg-stone-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-stone-500 disabled:bg-stone-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-stone-600 sm:col-start-2 ml-1 disabled:tw-opacity-50 disabled:cursor-not-allowed" :disabled="disabledButtons.includes('close')" @click="close">Close</button>
                                    <button v-if="buttons.includes('ok')" type="button" class="inline-flex xw-full justify-center rounded-md bg-sky-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-500 disabled:bg-sky-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-600 sm:col-start-2 ml-1 disabled:tw-opacity-50 disabled:cursor-not-allowed" :disabled="disabledButtons.includes('ok')" @click="ok">OK</button>
                                    <button v-if="buttons.includes('no')" type="button" class="inline-flex xw-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 disabled:bg-red-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 sm:col-start-2 ml-1 disabled:tw-opacity-50 disabled:cursor-not-allowed" :disabled="disabledButtons.includes('no')" @click="no">No</button>
                                    <button v-if="buttons.includes('yes')" type="button" class="inline-flex xw-full justify-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:bg-emerald-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:col-start-2 ml-1 disabled:tw-opacity-50 disabled:cursor-not-allowed" :disabled="disabledButtons.includes('yes')" @click="yes">Yes</button>
                                    <button v-if="buttons.includes('send')" type="button" class="inline-flex xw-full justify-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:bg-emerald-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:col-start-2 ml-1 disabled:tw-opacity-50 disabled:cursor-not-allowed" :disabled="disabledButtons.includes('send')" @click="send">Send</button>
                                    <button v-if="buttons.includes('submit')" type="button" class="inline-flex xw-full justify-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:bg-emerald-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:col-start-2 ml-1 disabled:tw-opacity-50 disabled:cursor-not-allowed" :disabled="disabledButtons.includes('submit')" @click="submit">Submit</button>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>

import {ref, watch} from 'vue';
import { Listbox, ListboxButton, ListboxOption, ListboxOptions } from '@headlessui/vue';
import { ChevronUpDownIcon } from '@heroicons/vue/16/solid';
import { CheckIcon } from '@heroicons/vue/20/solid';
import { CheckBadgeIcon } from '@heroicons/vue/20/solid';
import {formatCurrency} from "../Helpers/Currency.js";

const props = defineProps({
    offers: Array,
    modelValue: {
        type: Object, // Assuming 'offer' objects are passed
        default: null,
    },
});

const emit = defineEmits(['update:modelValue']);

// Internal ref to hold the currently selected item.
// It's initialized with the modelValue from props.
const selected = ref(props.modelValue);

// Watch for changes in the internal selection and emit to the parent
watch(selected, (newValue) => {
    emit('update:modelValue', newValue);
});

// Watch for changes in the parent's modelValue and update the internal selection
// This handles cases where the parent updates the v-model programmatically
watch(() => props.modelValue, (newValue) => {
    selected.value = newValue;
});

// Initialize selected if modelValue is provided initially
// This ensures the component reflects the initial v-model state
if (props.modelValue) {
    selected.value = props.modelValue;
}

</script>

<template>
    <Listbox as="div" v-model="selected">
        <div class="relative mt-2">
            <ListboxButton class="grid w-full cursor-default grid-cols-1 rounded-md bg-white py-1.5 pl-3 pr-2 text-left text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus-visible:outline focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-blue-600 sm:text-sm/6 min-h-9">
                <span class="col-start-1 row-start-1 flex items-center gap-3 pr-6">
                    <CheckBadgeIcon v-if="selected?.status == 'preapproved'" class="size-5 text-green-400" aria-hidden="true" />
                    <span v-else class="bg-orange-400 inline-block size-3 mx-1 shrink-0 rounded-full border border-transparent" aria-hidden="true" />

                    <span class="block truncate">{{ selected?.name }} hey</span>
                </span>
                <ChevronUpDownIcon class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4" aria-hidden="true" />
            </ListboxButton>

            <transition leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <ListboxOptions class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg outline outline-1 outline-black/5 sm:text-sm">
                    <ListboxOption as="template" v-for="offer in offers" :key="offer.id" :value="offer" v-slot="{ active, selected }">
                        <li :class="[active ? 'bg-blue-600 text-white outline-none' : 'text-gray-900', 'relative cursor-default select-none py-2 pl-3 pr-9']">
                            <div class="flex items-center">

                                <CheckBadgeIcon v-if="offer.status == 'preapproved'" class="size-5 text-green-400" aria-hidden="true" />
                                <span v-else class="bg-orange-400 inline-block size-3 mx-1 shrink-0 rounded-full border border-transparent" aria-hidden="true" />

                                <span :class="[selected ? 'font-semibold' : 'font-normal', 'ml-3 block truncate']">

                                    {{ offer.payment_provider.name }}
                                    <span v-if="offer.apr">{{ offer.apr }}%</span>
                                    {{ offer.term / 12 }} years

                                    <div v-if="offer.upfront_payment != 0">
                                        {{ formatCurrency(offer.upfront_payment, 0) }} up front
                                    </div>

                                    <div v-if="offer.deferred">
                                        {{ offer.deferred }} months deferred
                                    </div>


                                    <span class="sr-only"> is {{ offer.status == 'preapproved' ? 'Pre-approved' : 'Tentative' }}</span>
                                </span>
                            </div>

                            <span v-if="selected" :class="[active ? 'text-white' : 'text-blue-600', 'absolute inset-y-0 right-0 flex items-center pr-4']">
                                <CheckIcon class="size-5" aria-hidden="true" />
                            </span>
                        </li>
                    </ListboxOption>
                </ListboxOptions>
            </transition>
        </div>
    </Listbox>
</template>

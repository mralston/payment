<script setup>
import { ref, watch, computed } from 'vue';
import { MagnifyingGlassIcon } from '@heroicons/vue/16/solid';
import Modal from "./Modal.vue";

defineOptions({
    inheritAttrs: false
});

const props = defineProps({
    address: {
        type: Object,
        default: () => ({
            houseNumber: '',
            street: '',
            address1: '',
            address2: '',
            town: '',
            county: '',
            postCode: ''
        })
    },
    index: {
        type: [String, Number],
        default: ''
    },
    showHouseNumber: {
        type: Boolean,
        default: true
    },
    allowManualEntry: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:address']);

// Use a unique ID for the input elements to avoid conflicts if multiple instances are used
const uniqueId = computed(() => props.index || Math.random().toString(36).substring(2, 9));

// Use a computed property with a setter for the v-model binding.
// This allows AddressInput to read from `props.address` and write back
// to the parent via `emit('update:address')` without creating a recursive loop.
const addressModel = computed({
    // The 'get' function is called when the component needs to display the address data.
    // It simply returns the 'address' prop received from the parent.
    get() {
        return props.address;
    },
    // The 'set' function is called when any of the v-model bound inputs *within this component* change.
    // It receives the new value for the specific field that changed.
    set(newValue) {
        // When an input inside AddressInput changes, this setter is called.
        // We need to emit the full address object back to the parent.
        // We achieve this by merging the existing 'props.address'
        // with the 'newValue' (which contains the updated fields from this component's inputs).
        emit('update:address', {
            ...props.address, // Keep all existing properties from the original address object
            ...newValue // Merge in the updated fields from this component's inputs
        });
    }
});

const addresses = ref([]);

const addressLookupModal = ref(null);

const selectedAddress = ref();

const addressesFound = computed(() => Object.keys(addresses.value).length > 0);

function lookup()
{
    axios.get(route('payment.address.lookup', addressModel.value.postCode))
        .then(response => {
            addresses.value = response.data;
            addressLookupModal.value.show();
        });
}

function selectAddress()
{
    if (selectedAddress.value === undefined) {
        return;
    }

    if (props.showHouseNumber) {
        // We've been asked to show the house number & street
        if (selectedAddress.value.houseNumber) {
            // Pull directly from the lookup data's house number and street (if present)
            addressModel.value.houseNumber = selectedAddress.value.houseNumber;
            addressModel.value.street = selectedAddress.value.street;
            addressModel.value.address1 = selectedAddress.value.address1;
            addressModel.value.address2 = selectedAddress.value.address2;
        } else {
            // If the lookup data's house number and street are blank, use address1 & 2 instead
            addressModel.value.houseNumber = selectedAddress.value.address1;
            addressModel.value.street = selectedAddress.value.address2;
            addressModel.value.address1 = null;
            addressModel.value.address2 = null;
        }
    } else {
        // We've been asked not to show the house number & street (good for businesses)
        addressModel.value.houseNumber = null;
        addressModel.value.street = null;

        if (selectedAddress.value.houseNumber) {
            // Pull the address1 & 2 from the house number & street if present in the lookup data
            // Based on UAT, we might need to remove this logic and always use address1 & 2
            addressModel.value.address1 = selectedAddress.value.houseNumber;
            addressModel.value.address2 = selectedAddress.value.street;
        } else {
            // Otherwise use the actual address1 & 2 lines
            addressModel.value.address1 = selectedAddress.value.address1;
            addressModel.value.address2 = selectedAddress.value.address2;
        }
    }

    // The remaining fields are easier to come by
    addressModel.value.town = selectedAddress.value.city;
    addressModel.value.county = selectedAddress.value.county;
    addressModel.value.postCode = selectedAddress.value.postCode;
    addressModel.value.uprn = selectedAddress.value.uprn;
}

</script>

<template>

    <Modal ref="addressLookupModal" type="question" title="Select Address" class="w-1/2" :buttons="['ok', 'cancel']" @ok="selectAddress">
        <select v-if="addressesFound > 0" v-model="selectedAddress" class="w-full rounded-lg" size="10">
            <option v-for="address in addresses" :key="address.uprn" :value="address">
                {{ address.summary }}
            </option>
        </select>
        <div v-else>No addresses matched the post code.</div>
    </Modal>

    <div v-bind="$attrs" class="border border-gray-300 rounded">
        <div v-if="showHouseNumber">
            <input type="text"
                   v-model="addressModel.houseNumber"
                   :id="'houseNumber.' + uniqueId"
                   placeholder="House"
                   :disabled="!allowManualEntry"
                   class="w-[4rem] border-0 border-r-[1px] border-b-[1px] border-r-gray-300 border-b-gray-300 rounded-tl bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6 disabled:bg-gray-50"/>

            <input type="text"
                   v-model="addressModel.street"
                   :id="'street.' + uniqueId"
                   placeholder="Street"
                   :disabled="!allowManualEntry"
                   class="w-[calc(100%-4rem)] border-0 border-b-[1px] border-b-gray-300 rounded-tr bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6 disabled:bg-gray-50"/>
        </div>

        <input v-if="!showHouseNumber"
               type="text"
               v-model="addressModel.address1"
               :id="'address1.' + uniqueId"
               :disabled="!allowManualEntry"
               :placeholder="showHouseNumber ? 'Additional Line' : 'Line 1'"
               class="w-full rounded-t border-0 border-b-[1px] border-b-gray-300 bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6 disabled:bg-gray-50"
               :class="{'border-t-[1px]': showHouseNumber, 'rounded-t': !showHouseNumber}"/>

        <input v-if="!showHouseNumber"
               type="text"
               v-model="addressModel.address2"
               :id="'address2.' + uniqueId"
               :disabled="!allowManualEntry"
               :placeholder="showHouseNumber ? 'Additional Line' : 'Line 2'"
               class="w-full border-0 border-b-[1px] border-b-gray-300 bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6 disabled:bg-gray-50"/>
        <input type="text"
               v-model="addressModel.town"
               :id="'town.' + uniqueId"
               placeholder="Town"
               :disabled="!allowManualEntry"
               class="w-full border-0 border-b-[1px] border-b-gray-300 bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6 disabled:bg-gray-50"
        />

        <input type="text"
               v-model="addressModel.county"
               :id="'county.' + uniqueId"
               placeholder="County"
               :disabled="!allowManualEntry"
               class="w-full border-0 border-b-[1px] border-b-gray-300 bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6 disabled:bg-gray-50"/>

        <div class="flex items-center">
            <input type="text"
                   v-model="addressModel.postCode"
                   :id="'postCode.' + uniqueId"
                   placeholder="Post Code"
                   class="flex-grow border-0 rounded-bl bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6 disabled:bg-gray-50"/>

            <button type="button"
                    class="flex-shrink-0 self-stretch rounded-br border-l-[1px] border-l-gray-300 bg-gray-50 disabled:bg-gray-50 px-3 hover:bg-gray-100 disabled:bg-inherit focus:outline-2 focus:outline-offset-2 focus:outline-blue-600"
                    @click="lookup"
                    :disabled="!addressModel.postCode">
                <MagnifyingGlassIcon class="h-4 w-4 text-gray-600" aria-hidden="true"/>
            </button>
        </div>
    </div>
</template>

<style scoped>

</style>

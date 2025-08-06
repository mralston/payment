<script setup>
import { ref, watch, computed } from 'vue';

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
        // We achieve this by merging the existing 'props.address' (which includes 'dateMovedIn')
        // with the 'newValue' (which contains the updated fields from this component's inputs).
        // This ensures that 'dateMovedIn' is preserved and only the relevant address fields are updated.
        emit('update:address', {
            ...props.address, // Keep all existing properties from the original address object
            ...newValue // Merge in the updated fields from this component's inputs
        });
    }
});
</script>

<template>
    <div class="border border-gray-600 rounded">
        <input type="text" v-model="addressModel.houseNumber" :id="'houseNumber.' + uniqueId" placeholder="House" class="w-[4rem] border-0 border-r-[1px] border-r-gray-600 rounded-tl bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
        <input type="text" v-model="addressModel.street" :id="'street.' + uniqueId" placeholder="Street" class="w-[calc(100%-4rem)] border-0 rounded-tr bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" /><br>
        <input type="text" v-model="addressModel.address1" :id="'address1.' + uniqueId" placeholder="Additional Line" class="w-full border-0 border-t-[1px] border-t-gray-600 bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" /><br>
        <input type="text" v-model="addressModel.address2" :id="'address2.' + uniqueId" placeholder="Additional Line" class="w-full border-0 border-t-[1px] border-t-gray-600 bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" /><br>
        <input type="text" v-model="addressModel.town" :id="'town.' + uniqueId" placeholder="Town" class="w-full border-0 border-t-[1px] border-t-gray-600 bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" /><br>
        <input type="text" v-model="addressModel.county" :id="'county.' + uniqueId" placeholder="County" class="w-full border-0 border-t-[1px] border-t-gray-600 bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" /><br>
        <input type="text" v-model="addressModel.postCode" :id="'postCode.' + uniqueId" placeholder="Post Code" class="w-full border-0 border-t-[1px] border-t-gray-600 rounded-b bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
    </div>
</template>

<style scoped>

</style>

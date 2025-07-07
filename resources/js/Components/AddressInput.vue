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

// Create a reactive copy of the address prop to allow v-model to work internally
const internalAddress = ref({ ...props.address });

// Watch for changes in the internalAddress and emit an update event
watch(internalAddress, (newValue) => {
    emit('update:address', newValue);
}, { deep: true });

// Watch for changes in the parent's address prop and update the internal copy
watch(() => props.address, (newValue) => {
    internalAddress.value = { ...newValue };
}, { deep: true });
</script>

<template>
    <div class="border border-gray-600 rounded">
        <input type="text" v-model="internalAddress.houseNumber" :id="'houseNumber.' + uniqueId" placeholder="House" class="w-[4rem] border-0 border-r-[1px] border-r-gray-600 rounded-tl bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
        <input type="text" v-model="internalAddress.street" :id="'street.' + uniqueId" placeholder="Street" class="w-[calc(100%-4rem)] border-0 rounded-tr bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" /><br>
        <input type="text" v-model="internalAddress.address1" :id="'address1.' + uniqueId" placeholder="Additional Line" class="w-full border-0 border-t-[1px] border-t-gray-600 bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" /><br>
        <input type="text" v-model="internalAddress.address2" :id="'address2.' + uniqueId" placeholder="Additional Line" class="w-full border-0 border-t-[1px] border-t-gray-600 bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" /><br>
        <input type="text" v-model="internalAddress.town" :id="'town.' + uniqueId" placeholder="Town" class="w-full border-0 border-t-[1px] border-t-gray-600 bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" /><br>
        <input type="text" v-model="internalAddress.county" :id="'county.' + uniqueId" placeholder="County" class="w-full border-0 border-t-[1px] border-t-gray-600 bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" /><br>
        <input type="text" v-model="internalAddress.postCode" :id="'postCode.' + uniqueId" placeholder="Post Code" class="w-full border-0 border-t-[1px] border-t-gray-600 rounded-b bg-white px-2 text-base text-gray-900 outline-1 -outline-offset-1 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
    </div>
</template>

<style scoped>

</style>

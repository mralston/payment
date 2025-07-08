<script setup>
import { CheckCircleIcon } from '@heroicons/vue/20/solid';
import {formatCurrency} from "../../Helpers/Currency.js";
import { ChevronDownIcon } from '@heroicons/vue/16/solid';
import {ref} from "vue";
import Column from "./Column.vue";

const props = defineProps({
    columns: Array,
});

const emit = defineEmits(['proceed']);

const selectedOption = ref();

function proceed(e) {
    emit('proceed', e);
}

</script>

<template>
    <div class="bg-white py-16">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="flow-root">
                <div class="isolate -mt-16 grid max-w-sm grid-cols-1 gap-y-16 divide-y divide-gray-100 sm:mx-auto lg:-mx-8 lg:mt-0 lg:max-w-none lg:grid-cols-3 lg:divide-x lg:divide-y-0 xl:-mx-4">
                    <Column v-for="column in columns" :key="column.id" :column="column" @proceed="proceed" />
                </div>
            </div>
        </div>
    </div>
</template>

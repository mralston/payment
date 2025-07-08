<script setup>
import { CheckCircleIcon } from '@heroicons/vue/20/solid';
import {formatCurrency} from "../../Helpers/Currency.js";
import { ChevronDownIcon } from '@heroicons/vue/16/solid';
import {onMounted, ref} from "vue";

const props = defineProps({
    column: Object,
});

const emit = defineEmits(['proceed']);

const selectedOption = ref();

function proceed() {
    emit('proceed', {column: props.column, option: selectedOption.value});
}

onMounted(() => {
    if ((props.column.options?.length ?? 0) === 0) {
        return;
    }

    for (let i = 0; i < props.column.options.length; i++) {
        if (props.column.options[i].default) {
            selectedOption.value = props.column.options[i].value;
            break;
        }
    }
});

</script>

<template>

    <div class="pt-16 lg:px-8 lg:pt-0 xl:px-14">
        <h3 :id="column.id" class="text-4xl font-semibold text-blue-800">{{ column.name }}</h3>
        <p class="mt-6 flex items-baseline gap-x-1">
            <span class="text-5xl font-semibold tracking-tight text-gray-900">{{ formatCurrency(column.price.total ?? column.price.monthly) }}</span>
            <span v-if="column.price.monthly" class="text-sm/6 font-semibold text-gray-600">/month</span>
        </p>

        <select class="mt-8 w-full rounded-md bg-white py-1.5 pl-3 pr-8 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                :class="{ 'invisible': (column.options?.length ?? 0) == 0 }"
                v-model="selectedOption">
            <option v-for="option in column.options" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>

        <button @click="proceed" :aria-describedby="column.id" class="mt-10 w-full rounded-md bg-blue-600 px-3 py-2 text-center text-sm/6 font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
            Proceed
        </button>
        <p class="mt-10 text-sm/6 font-semibold text-gray-900">{{ column.description }}</p>
        <ul role="list" class="mt-6 space-y-3 text-sm/6 text-gray-600">
            <li v-for="feature in column.features" :key="feature" class="flex gap-x-3">
                <CheckCircleIcon class="h-6 w-5 flex-none text-indigo-600" aria-hidden="true" />
                {{ feature }}
            </li>
        </ul>
    </div>

</template>

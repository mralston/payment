<script setup>
import { computed } from 'vue';

const props = defineProps({
    form: Object,
    field: {
        type: [String, Array],
        required: true,
        validator: (value) => {
            // Ensure that if it's an array, all items are strings.
            if (Array.isArray(value)) {
                return value.every(item => typeof item === 'string');
            }
            return typeof value === 'string';
        }
    },
});

// Computed property to check if there are any errors for the given field(s)
const hasErrors = computed(() => {
    if (Array.isArray(props.field)) {
        return props.field.some(f => props.form.errors[f]);
    }
    return !!props.form.errors[props.field];
});

// Computed property to get all error messages for the given field(s)
const errorMessages = computed(() => {
    let messages = [];
    if (Array.isArray(props.field)) {
        props.field.forEach(f => {
            if (props.form.errors[f]) {
                messages.push(props.form.errors[f]);
            }
        });
    } else {
        if (props.form.errors[props.field]) {
            messages.push(props.form.errors[props.field]);
        }
    }
    return messages;
});
</script>

<template>
    <div :class="{ 'border-red-500 bg-red-100 border-2 rounded-lg p-2 pb-1': hasErrors }">
        <slot/>
        <div v-if="hasErrors" class="text-red-500 mt-1">
            <div v-for="error in errorMessages" :key="error">{{ error }}</div>
        </div>
    </div>
</template>

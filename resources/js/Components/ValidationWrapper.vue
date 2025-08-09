<script setup>

const props = defineProps({
    form: Object,
    field: String,
})

</script>

<template>
    <div :class="{ 'border-red-500 bg-red-100 border-2 rounded-lg p-2 pb-1': form.errors[field] }">
        <slot/>
        <div v-if="form.errors[field]" class="text-red-500 mt-1">{{ form.errors[field] }}</div>
    </div>
</template>

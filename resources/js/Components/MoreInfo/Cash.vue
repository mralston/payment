<script setup>
import CashInfo from "../../Pages/Cash/Create.vue";

const props = defineProps({
    totalCost: Number,
    deposit: Number,
});
</script>

<template>
    <CashInfo :totalCost="totalCost" :deposit="deposit" :minimal="true" />
</template>

<style scoped>

</style>

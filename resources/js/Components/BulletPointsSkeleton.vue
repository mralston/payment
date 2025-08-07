<script setup>

import SkeletonItem from "./SkeletonItem.vue";
</script>

<template>
    <div>

        <SkeletonItem class="mt-10 rounded-md h-5 w-1/2"/>

        <div class="mt-7 w-full">
            <SkeletonItem class="inline-block rounded-full h-5 w-5 mr-3"/>
            <SkeletonItem class="inline-block rounded-md h-5 w-3/4"/>
        </div>

        <div class="mt-3 w-full">
            <SkeletonItem class="inline-block rounded-full h-5 w-5 mr-3"/>
            <SkeletonItem class="inline-block rounded-md h-5 w-1/2"/>
        </div>

        <div class="mt-3 w-full">
            <SkeletonItem class="inline-block rounded-full h-5 w-5 mr-3"/>
            <SkeletonItem class="inline-block rounded-md h-5 w-5/6"/>
        </div>

        <div class="mt-3 w-full">
            <SkeletonItem class="inline-block rounded-full h-5 w-5 mr-3"/>
            <SkeletonItem class="inline-block rounded-md h-5 w-4/6"/>
        </div>

    </div>
</template>

<style scoped>

</style>

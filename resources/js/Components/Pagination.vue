<script setup>
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/20/solid';
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    records: Object,
});
</script>

<template>

    <div class="flex items-center justify-between border-t border-gray-200 bg-white py-3">
        <div class="flex flex-1 justify-between md:hidden">

            <Link v-if="records.prev_page_url != null"
                  :href="records.prev_page_url"
                  class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Previous
            </Link>
            <span v-else
                  class="relative inline-flex items-center rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed">
                Previous
            </span>

            <Link v-if="records.next_page_url != null"
                  :href="records.next_page_url"
                  class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Next
            </Link>
            <span v-else
                  class="relative inline-flex items-center rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed">
                Previous
            </span>

        </div>
        <div class="hidden md:flex sm:flex-1 md:items-center md:justify-between">
            <div>
                <p class="text-sm text-gray-700 mr-2">
                    Showing
                    {{ ' ' }}
                    <span class="font-medium">{{ records.from }}</span>
                    {{ ' ' }}
                    to
                    {{ ' ' }}
                    <span class="font-medium">{{ records.to }}</span>
                    {{ ' ' }}
                    of
                    {{ ' ' }}
                    <span class="font-medium">{{ records.total }}</span>
                    {{ ' ' }}
                    results
                </p>
            </div>
            <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    <template v-for="(link, index) in records.links" :key="index">
                        <Link v-if="link.url"
                              :href="link.url"
                              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 focus:z-20 focus:outline-offset-0"
                              :class="{
                                'z-10 bg-blue-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600': link.active,
                                'text-gray-900 hover:bg-gray-50': !link.active,
                                'hover:bg-blue-700': link.active
                              }">
                            <span v-if="index == 0">
                                <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
                            </span>
                            <span v-else-if="index == records.links.length - 1">
                                <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
                            </span>
                            <span v-else>
                                {{ link.label }}
                            </span>
                        </Link>
                        <span v-else
                              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 cursor-not-allowed"
                              :class="{
                                'z-10 bg-blue-600 text-white': link.active, // Keep text white if active (even if disabled)
                                'text-gray-400': !link.active // Disabled text color if not active
                              }">
                            <span v-if="index == 0">
                                <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
                            </span>
                            <span v-else-if="index == records.links.length - 1">
                                <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
                            </span>
                            <span v-else>
                                {{ link.label }}
                            </span>
                        </span>
                    </template>
                </nav>
            </div>
        </div>
    </div>
</template>



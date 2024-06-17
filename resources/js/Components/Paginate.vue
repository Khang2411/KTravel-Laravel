<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Object
})
</script>

<template>
    <div>
        <div>

            <div class="flex flex-wrap -mb-1 justify-end">
                <template v-for="(link, index) in links" :key="index">
                    <div v-if="link.url === null"
                        class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded cursor-pointer
                         dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
                        v-html="link.label" />
                    <Link v-if="link.url" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded cursor-pointer
                        hover:bg-white focus:border-indigo-500 focus:text-indigo-500 dark:border-gray-700 
                        dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
                        :class="{ 'bg-blue-700 text-white hover:bg-blue-700': link.active }" :href="link.url">
                    {{ link.label }}</Link>
                </template>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped></style>
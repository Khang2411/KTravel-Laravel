<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div>
        <Link :href="route('dashboard')" class="text-center dark:text-white text-2xl">
        <p
            class="animate-text bg-gradient-to-r from-teal-500 via-purple-500 to-orange-500 bg-clip-text text-transparent text-4xl font-black w-full">
            KTravel
        </p>
        </Link>
    </div>
</template>

<style lang="scss" scoped></style>
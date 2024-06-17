<script setup>
import ChartCard from '@/Components/chart/ChartCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import axios from "axios";
import moment from "moment";
import 'moment/dist/locale/vi';
import 'moment/min/locales.min.js';
moment.locale('vi')

defineProps({
    orders: Object,
    reviews: Object,
    order_number: Number,
    sumTotal: Number,
    product_number: Number,
    user_number: Number,
    revenueByMonths: Array,
    orderByMonths: Array,
})

const handleClickOrder = () => {
    router.visit(route('admin.order.list'), { preserveScroll: true })
}
const handleClickReview = () => {
    console.log('no reviews')
    //  router.visit(route('admin.review.list'), { preserveScroll: true })
}
const handleClickPdf = async () => {
    const pdfDownload = await axios({
        url: route('admin.statistic.pdf'),
        method: 'GET',
        responseType: 'blob', // important
    })
    const url = window.URL.createObjectURL(new Blob([pdfDownload.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'Thongkedoanhthu.pdf');
    document.body.appendChild(link);
    link.click();
}
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <ChartCard :product_number="product_number" :order_number="order_number" :user_number="user_number"
            :orderByMonths="orderByMonths" :revenueByMonths="revenueByMonths"></ChartCard>

        <div class="mt-4 rounded-lg dark:border-gray-700">
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="rounded p-3 bg-gray-50 dark:bg-gray-800 dark:text-white col-span-3 md:col-span-1">
                    <div class="mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </div>
                    <div class="text-2xl font-bold mb-2">
                        Đơn đặt hàng
                    </div>
                    <div class="text-2xl font-bold mb-2">
                        {{ order_number }}
                    </div>
                    <div class="text-green-400	">
                        So với tháng trước tăng 100%
                    </div>
                </div>

                <div class="rounded bg-gray-50 dark:bg-gray-800 dark:text-white p-3 col-span-3 md:col-span-1">
                    <div class="mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                    </div>
                    <div class="text-2xl font-bold mb-2">
                        Doanh thu
                    </div>
                    <div class="text-2xl font-bold mb-2">
                        {{ sumTotal }} $
                    </div>
                    <div class="text-green-400	">
                        So với tháng trước tăng 100%
                    </div>
                </div>

                <div class="rounded bg-gray-50 dark:bg-gray-800 dark:text-white p-3 col-span-3 md:col-span-1">
                    <div class="flex justify-between items-center mb-2">
                        <div class="text-2xl font-bold mb-2">
                            Đánh giá gần đây
                        </div>
                        <div class="text-orange-400 cursor-pointer" @click="handleClickReview">
                            Xem tất cả
                        </div>
                    </div>
                    <div class="font-bold mb-2">
                        <div id="default-carousel" class="relative w-full" data-carousel="slide">
                            <div class="relative overflow-hidden rounded-lg h-48 md:h-36">
                                <!-- Item 1 -->
                                <div v-for="(review, index) in reviews" :key="index"
                                    class="hidden duration-700 ease-in-out" data-carousel-item>
                                    <article>
                                        <div class="flex items-center mb-4">
                                            <div
                                                class="relative w-10 h-10 overflow-hidden bg-gray-100 rounded-full me-4">
                                                <svg class="absolute w-12 h-12 text-gray-400 -left-1"
                                                    fill="currentColor" viewBox="0 0 20 20"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd"
                                                        d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                        clip-rule="evenodd">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div>
                                                <div>{{ review.user.name }}</div>
                                                <div class="font-medium dark:text-white">
                                                    <div class="flex items-center mb-1 space-x-1 rtl:space-x-reverse">
                                                        <svg class="w-4 h-4 text-yellow-300" aria-hidden="true"
                                                            xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                            viewBox="0 0 22 20">
                                                            <path
                                                                d="M20.924 7.625a1.523 1.523 0 0 0-1.238-1.044l-5.051-.734-2.259-4.577a1.534 1.534 0 0 0-2.752 0L7.365 5.847l-5.051.734A1.535 1.535 0 0 0 1.463 9.2l3.656 3.563-.863 5.031a1.532 1.532 0 0 0 2.226 1.616L11 17.033l4.518 2.375a1.534 1.534 0 0 0 2.226-1.617l-.863-5.03L20.537 9.2a1.523 1.523 0 0 0 .387-1.575Z" />
                                                        </svg>
                                                        <svg class="w-4 h-4 text-yellow-300" aria-hidden="true"
                                                            xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                            viewBox="0 0 22 20">
                                                            <path
                                                                d="M20.924 7.625a1.523 1.523 0 0 0-1.238-1.044l-5.051-.734-2.259-4.577a1.534 1.534 0 0 0-2.752 0L7.365 5.847l-5.051.734A1.535 1.535 0 0 0 1.463 9.2l3.656 3.563-.863 5.031a1.532 1.532 0 0 0 2.226 1.616L11 17.033l4.518 2.375a1.534 1.534 0 0 0 2.226-1.617l-.863-5.03L20.537 9.2a1.523 1.523 0 0 0 .387-1.575Z" />
                                                        </svg>
                                                        <svg class="w-4 h-4 text-yellow-300" aria-hidden="true"
                                                            xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                            viewBox="0 0 22 20">
                                                            <path
                                                                d="M20.924 7.625a1.523 1.523 0 0 0-1.238-1.044l-5.051-.734-2.259-4.577a1.534 1.534 0 0 0-2.752 0L7.365 5.847l-5.051.734A1.535 1.535 0 0 0 1.463 9.2l3.656 3.563-.863 5.031a1.532 1.532 0 0 0 2.226 1.616L11 17.033l4.518 2.375a1.534 1.534 0 0 0 2.226-1.617l-.863-5.03L20.537 9.2a1.523 1.523 0 0 0 .387-1.575Z" />
                                                        </svg>
                                                        <svg class="w-4 h-4 text-yellow-300" aria-hidden="true"
                                                            xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                            viewBox="0 0 22 20">
                                                            <path
                                                                d="M20.924 7.625a1.523 1.523 0 0 0-1.238-1.044l-5.051-.734-2.259-4.577a1.534 1.534 0 0 0-2.752 0L7.365 5.847l-5.051.734A1.535 1.535 0 0 0 1.463 9.2l3.656 3.563-.863 5.031a1.532 1.532 0 0 0 2.226 1.616L11 17.033l4.518 2.375a1.534 1.534 0 0 0 2.226-1.617l-.863-5.03L20.537 9.2a1.523 1.523 0 0 0 .387-1.575Z" />
                                                        </svg>
                                                        <svg class="w-4 h-4 text-yellow-300" aria-hidden="true"
                                                            xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                            viewBox="0 0 22 20">
                                                            <path
                                                                d="M20.924 7.625a1.523 1.523 0 0 0-1.238-1.044l-5.051-.734-2.259-4.577a1.534 1.534 0 0 0-2.752 0L7.365 5.847l-5.051.734A1.535 1.535 0 0 0 1.463 9.2l3.656 3.563-.863 5.031a1.532 1.532 0 0 0 2.226 1.616L11 17.033l4.518 2.375a1.534 1.534 0 0 0 2.226-1.617l-.863-5.03L20.537 9.2a1.523 1.523 0 0 0 .387-1.575Z" />
                                                        </svg>
                                                        <h3
                                                            class="ms-2 text-sm font-semibold text-gray-900 dark:text-white">
                                                            (4)
                                                        </h3>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <p class="mb-2 text-gray-500 dark:text-gray-400">{{ review.content }}
                                        </p>
                                    </article>
                                </div>
                            </div>
                            <!-- Slider controls -->
                            <button type="button"
                                class="absolute top-10 start-0 z-30 opacity-50 flex items-center justify-center h-full cursor-pointer group focus:outline-none"
                                data-carousel-prev>
                                <span
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50  group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none">
                                    <svg class="w-4 h-4 text-white dark:text-gray-800 rtl:rotate-180" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M5 1 1 5l4 4" />
                                    </svg>
                                    <span class="sr-only">Previous</span>
                                </span>
                            </button>
                            <button type="button"
                                class="absolute top-10 end-0 z-30 opacity-50 flex items-center justify-center h-full cursor-pointer group focus:outline-none"
                                data-carousel-next>
                                <span
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30  group-hover:bg-white/50  group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none">
                                    <svg class="w-4 h-4 text-white dark:text-gray-800 rtl:rotate-180" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m1 9 4-4-4-4" />
                                    </svg>
                                    <span class="sr-only">Next</span>
                                </span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="text-left">
                <button @click="handleClickPdf"
                    class="relative inline-flex items-center justify-center p-0.5 mb-2 me-2 overflow-hidden text-sm font-medium text-gray-900 rounded-lg group bg-gradient-to-br from-cyan-500 to-blue-500 group-hover:from-cyan-500 group-hover:to-blue-500 hover:text-white dark:text-white focus:ring-4 focus:outline-none focus:ring-cyan-200 dark:focus:ring-cyan-800">
                    <span
                        class="relative px-5 py-2.5 transition-all ease-in duration-75 bg-white dark:bg-gray-900 rounded-md group-hover:bg-opacity-0">
                        Xuất File PDF
                    </span>
                </button>
            </div>
            <div class="text-right">
                <span class="text-orange-400 cursor-pointer" @click="handleClickOrder">Xem tất cả</span>
            </div>

            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-8 py-3">
                                Mã đơn
                            </th>

                            <th scope="col" class="px-6 py-3">
                                Thời gian
                            </th>

                            <th scope="col" class="px-6 py-3">
                                Trạng thái
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Nhà/Căn hộ
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Tổng tiền
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Thanh toán
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(order, index) in orders" :key="index"
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">

                            <th scope="row">
                                <div
                                    class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap dark:text-white">
                                    <div class="ps-3">
                                        <span class="text-xs font-semibold">#{{ order.id }}</span>
                                    </div>
                                    <div class="ps-3">
                                        <span class="text-xs font-semibold">#{{ order.user_name }}</span>
                                    </div>
                                </div>
                            </th>

                            <td class="px-6 py-4">
                                {{ moment(order.updated_at).fromNow() }}
                            </td>

                            <td class="px-6 py-4">
                                <span v-if="order.status === 'pending'"
                                    class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">
                                    Chờ thanh toán
                                </span>

                                <span v-else-if="order.status === 'completed'"
                                    class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">
                                    Hoàn thành
                                </span>

                            </td>

                            <td class="px-2 py-4 w-52">
                                {{ order.listing.name }}
                            </td>

                            <td class="px-6 py-4">
                                {{ order.price }} $
                            </td>
                            <td class="px-6 py-4">
                                {{ order.price - (order.price * 0.2) }} $
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
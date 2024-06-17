<script setup>
import Paginate from '@/Components/Paginate.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import 'moment/min/locales.min.js';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

import moment from "moment";
import 'moment/dist/locale/vi';
moment.locale('vi')


const props = defineProps({
    orders: Object,
    list_action: Object,
    count: Array,
})

const page = ref(new URLSearchParams(window.location.search).get('page'))
const queryParam = ref(new URLSearchParams(window.location.search).get('status'))
const toastId = ref('');

const form = useForm({
    search: new URLSearchParams(window.location.search).get('search') || '',
    id: '',
    name: '',
    email: '',
    method: '',
    status: '',
    phone: '',
    discount: '',
    listing: "",
    list_check: [],
    all_selected: false,
});

const handleSelectAll = () => {
    console.log(form.all_selected)
    if (form.all_selected === true) {
        for (let i in props.orders.data) {
            form.list_check.push(props.orders.data[i].id)
            console.log(props.orders.data[i].id)
        }
    } else {
        form.list_check = []
    }
}

const handleModal = (order) => {
    form.defaults({
        id: order.id,
        name: order.payment.payer_name,
        email: order.payment.payer_email,
        price: order.price,
        method: order.payment.payment_method,
        phone: order.user.phone,
        listing: order.listing
    })
    form.reset();
}

const handleSearch = debounce((e) => {
    router.get('', { search: e.target.value }, { replace: true })
}, 500)

window.addEventListener("message", function (e) {
    router.reload(['orders,count'])
    toast.success('Thanh toán thành công!');
}, false);

const submit = async (orderID, payerEmail, price) => {
    router.reload({ only: ['orders,count'] })
    toastId.value = toast.loading('Khởi tạo trang thanh toán ...')
    try {
        const payment = await axios.post(route('admin.payment.transfer'), {
            orderID: orderID,
            payerEmail: payerEmail,
            price: price
        });
        window.open(payment.data.redirect, "", "width=560,height=700")
        toast.remove(toastId.value)
    } catch (error) {
        console.log(error);
    }
};

</script>

<template>

    <Head title="Danh sách thanh toán" />
    <AuthenticatedLayout>
        <div>
            <p class="mb-5 dark:text-white text-2xl">Thanh toán</p>
        </div>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg flex-1">
            <div v-if="$page.props.flash.status"
                class="p-4 mb-4 text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300"
                role="alert">
                <span class="font-medium">{{ $page.props.flash.status }}</span>
            </div>

            <div class="text-blue-400 dark:text-purple-50 text-sm flex gap-2">
                <Link :class="{ 'active': queryParam === 'unpaid' }" class="[&.active]:border-b-4 border-indigo-500"
                    href="?status=unpaid" :only="['orders,list_action,count']">
                Chưa hoàn trả ({{ count[0] }})</Link>
                <span class="after:content-['_|']"></span>
                <Link :class="{ 'active': queryParam === 'paid' }" class="[&.active]:border-b-4 border-indigo-500"
                    href="?status=paid" :only="['orders,list_action,count']">Đã hoàn trả ({{ count[1] }})</Link>
            </div>
            <div
                class="flex items-center justify-between flex-column md:flex-row flex-wrap space-y-4 md:space-y-0 py-4 bg-white dark:bg-gray-900 px-2">
                <div>
                    <!-- <button id="dropdownActionButton" data-dropdown-toggle="dropdownAction"
                        class="inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                        type="button">
                        <span class="sr-only">Action button</span>
                        Tủy chọn
                        <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 4 4 4-4" />
                        </svg>
                    </button> -->
                    <!-- Dropdown menu -->
                    <!-- <div id="dropdownAction"
                        class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700 dark:divide-gray-600">
                        <ul class="py-1 text-sm text-gray-700 dark:text-gray-200"
                            aria-labelledby="dropdownActionButton">
                            <li v-for="( [key, value], index ) in Object.entries(list_action) " :key="index">
                                <a href="#" @click="handleAction(key)"
                                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">{{
                value }}</a>
                            </li>
                        </ul>
                    </div> -->
                </div>
                <label for="table-search" class="sr-only">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                        </svg>
                    </div>
                    <TextInput type="text"
                        class="block pt-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        v-model="form.search" placeholder="Tìm kiếm" autocomplete="search"
                        @keyup="handleSearch($event)" />
                </div>
            </div>
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="p-4">
                                <div class="flex items-center">
                                    <input id="checkbox-all" type="checkbox" @change="handleSelectAll()"
                                        v-model="form.all_selected"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="checkbox-all" class="sr-only">checkbox</label>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-center">
                                Mã đơn
                            </th>

                            <th scope="col" class="px-6 py-3 text-center">
                                Thời gian
                            </th>

                            <th scope="col" class="px-6 py-3 text-center">
                                Trạng thái
                            </th>
                            <th scope="col" class="px-6 py-3 text-center">
                                Địa chỉ
                            </th>
                            <th scope="col" class="px-6 py-3 text-center">
                                Thanh toán
                            </th>
                            <th scope="col" class="px-6 py-3 text-center" v-if="queryParam != 'paid'">
                                Tác vụ
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(order, index) in orders.data" :key="index"
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="w-4 p-4 text-center">
                                <div class="flex items-center">
                                    <input id="checkbox-table-search-1" type="checkbox" v-model="form.list_check"
                                        :value="order.id"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="checkbox-table-search-1" class="sr-only">checkbox</label>
                                </div>
                            </td>
                            <th scope="row">
                                <div
                                    class="flex items-center justify-center px-6 py-4 text-gray-900 whitespace-nowrap dark:text-white">
                                    <div class="ps-3">
                                        <span class="text-xs font-semibold">#{{ order.id }}</span>
                                    </div>

                                    <div class="ps-3">
                                        <span class="text-xs font-semibold">{{ order.user_name }}</span>
                                    </div>
                                </div>
                            </th>

                            <td class="px-6 py-4 text-center">
                                {{ moment(order.updated_at).fromNow() }}
                            </td>

                            <td class="text-center">
                                <span v-if="order.status === 'completed'"
                                    class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">
                                    Đã hoàn trả
                                </span>

                                <span v-else-if="order.status === 'pending'"
                                    class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">
                                    Chưa hoàn trả
                                </span>
                            </td>

                            <td class="px-2 py-4 w-52 text-center">
                                {{ order.listing.name }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                $ {{ order.price - (order.price * 0.2) }}
                            </td>
                            <td class="px-6 py-4" v-if="queryParam != 'paid'">
                                <!-- Modal toggle -->
                                <div class="flex items-center justify-center">
                                    <a v-if="queryParam != 'paid'" type="button" @click="handleModal(order)"
                                        data-modal-target="editPaymentModal" data-modal-show="editPaymentModal"
                                        class="font-medium text-blue-600 dark:text-blue-500 hover:underline cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" data-slot="icon" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-2 mb-2 px-2">
                <Paginate :links="orders.links"></Paginate>
            </div>

            <div id="editPaymentModal" tabindex="-1" aria-hidden="true"
                class="fixed top-0 left-0 right-0 z-50 items-center justify-center hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                <div class="relative w-full max-w-4xl max-h-full">
                    <!-- Modal content -->
                    <form @submit.prevent="submit(form.id, form.email, form.price)" target="blank"
                        class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                        <!-- Modal header -->
                        <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                Chi tiết thanh toán #{{ form.id }}
                            </h3>
                            <button type="button"
                                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                data-modal-hide="editPaymentModal">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                        </div>
                        <!-- Modal boy -->
                        <div class="p-3 dark:text-white">
                            <div class="mb-3">
                                <div class="text-lg font-bold">Tên thụ hưởng</div>
                                <div class="italic">{{ form.name }}</div>
                            </div>

                            <div class="mb-3">
                                <div class="text-lg font-bold">Email thanh toán</div>
                                <div class="italic">{{ form.email }}</div>
                            </div>

                            <div class="mb-3">
                                <div class="text-lg font-bold">Số điện thoại</div>
                                <div class="italic">{{ form.phone }}</div>
                            </div>

                            <div class="mb-3">
                                <div class="text-lg font-bold">Thanh toán</div>
                                <div class="italic">{{ form.method }}</div>
                            </div>
                        </div>
                        <div>
                            <div class="relative overflow-x-auto">
                                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                    <thead
                                        class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                        <tr>
                                            <th scope="col" class="px-6 py-3">
                                                Tên phòng/căn hộ
                                            </th>

                                            <th scope="col" class="px-6 py-3 text-center">
                                                Giá
                                            </th>

                                            <th scope="col" class="px-6 py-3 text-center">
                                                Thanh toán
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                            <th scope="row"
                                                class="w-40 px-2 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                <div
                                                    class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap dark:text-white">
                                                    <img class="w-14 h-14" :src=form.listing.images?.[0].image
                                                        alt="product" />
                                                    <div class="ps-3">
                                                        <span class="text-xs font-semibold">{{ form.listing.name
                                                            }}</span>
                                                    </div>
                                                </div>
                                            </th>
                                            <td class="px-6 py-4 text-center">
                                                {{ form.price }} $
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                {{ form.price - (form.price * 0.2) }} $
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="text-right pr-2 p-5">
                            <PrimaryButton data-modal-hide="editPaymentModal" class="ms-4"
                                :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Thanh toán
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped></style>

<script setup>
import Paginate from '@/Components/Paginate.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import moment from "moment";
import { ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const props = defineProps({
    products: Object,
    list_action: Object,
    status: String,
    count: Array
})
const page = ref(new URLSearchParams(window.location.search).get('page'))
const queryParam = ref(new URLSearchParams(window.location.search).get('status'))
const toastId = ref('');

const form = useForm({
    search: new URLSearchParams(window.location.search).get('search') || '',
    list_check: [],
    all_selected: false
});

const handleSelectAll = () => {
    console.log(form.all_selected)
    if (form.all_selected === true) {
        for (let i in props.products.data) {
            form.list_check.push(props.products.data[i].id)
            console.log(props.products.data[i].id)
        }
    } else {
        form.list_check = []
    }
}

const handleAction = (action) => {
    console.log(action)
    router.post(route('admin.room.action'), {
        action: action,
        list_check: form.list_check
    }, {
        onSuccess: () => {
            toast.remove(toastId.value)
            toast.success('Thao tác thành công!');
        },
        onStart: () => { toastId.value = toast.loading('Loading...') }
    });
}

const handleRemove = (id) => {
    if (confirm("Bạn có muốn xóa?")) {
        router.post(route('admin.room.delete', id), {
        }, {
            preserveScroll: true,
            onSuccess: () => {
                toast.remove(toastId.value)
                toast.success('Xóa thành công!');
            },
            onStart: () => { toastId.value = toast.loading('Loading...') }
        });
    }
}

const handleSearch = debounce((e) => {
    router.get('', { search: e.target.value }, { replace: true })
}, 500)

</script>

<template>

    <Head title="Danh sách phòng ở/căn hộ" />
    <AuthenticatedLayout>
        <div>
            <p class="mb-5 dark:text-white text-2xl">Phòng ở/căn hộ</p>
        </div>

        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <div v-if="$page.props.flash.error"
                class="p-4 mb-4 text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300"
                role="alert">
                <span class="font-medium">{{ $page.props.flash.error }}</span>
            </div>

            <div v-if="$page.props.flash.success"
                class="p-4 mb-4 text-green-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-green-300"
                role="alert">
                <span class="font-medium">{{ $page.props.flash.success }}</span>
            </div>

            <div class="text-blue-400 dark:text-purple-50 text-sm flex gap-2">
                <Link :class="{ 'active': queryParam === 'active' }" class="[&.active]:border-b-4 border-indigo-500"
                    href="?status=active" :only="['products,list_action']">
                Tất cả ({{ count[0] }})</Link>
                <span class="after:content-['_|']"></span>
                <Link :class="{ 'active': queryParam === 'trash' }" class="[&.active]:border-b-4 border-indigo-500"
                    href="?status=trash" :only="['products,list_action']">Rác ({{ count[1] }})</Link>
            </div>
            <div
                class="flex items-center justify-between flex-column md:flex-row flex-wrap space-y-4 md:space-y-0 py-4 bg-white dark:bg-gray-900 px-2">
                <div>
                    <button id="dropdownActionButton" data-dropdown-toggle="dropdownAction"
                        class="inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                        type="button">
                        <span class="sr-only">Action button</span>
                        Tùy chọn
                        <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 4 4 4-4" />
                        </svg>
                    </button>
                    <!-- Dropdown menu -->
                    <div id="dropdownAction"
                        class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700 dark:divide-gray-600">
                        <ul class="py-1 text-sm text-gray-700 dark:text-gray-200"
                            aria-labelledby="dropdownActionButton">
                            <li v-for="( [key, value], index ) in Object.entries(list_action) " :key="index">
                                <a href="#" @click="handleAction(key)"
                                    class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                    {{ value }}</a>
                            </li>
                        </ul>
                    </div>
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
                    <input type="text" id="table-search-users" v-model="form.search" @keyup="handleSearch($event)"
                        class="block pt-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="Tim kiếm">
                </div>
            </div>
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="p-4">
                                <div class="flex items-center">
                                    <input id="checkbox-all-search" type="checkbox" @change="handleSelectAll()"
                                        v-model="form.all_selected"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="checkbox-all-search" class="sr-only">checkbox</label>
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Tên
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Giá
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Thể loại
                            </th>

                            <th scope="col" class="px-6 py-3">
                                Thời gian
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Tác vụ
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="( product, index ) in  products.data" :key="index"
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer">
                            <td class="w-4 p-4">
                                <div class="flex items-center">
                                    <input id="checkbox-table-search-1" type="checkbox" v-model="form.list_check"
                                        :value="product.id"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="checkbox-table-search-1" class="sr-only">checkbox</label>
                                </div>
                            </td>
                            <th scope="row">
                                <a :href="$inertia.page.props.client_app_url + '/room/' + product.id" target="_blank">
                                    <div
                                        class="flex items-center px-6 py-4 text-gray-900 whitespace-nowrap dark:text-white">
                                        <img class="w-16 h-16 rounded " :src="product.images[0].image"
                                            alt="img-product">
                                        <div class="ps-3">
                                            <span class="text-xs font-semibold">{{ product.name }}</span>
                                        </div>
                                    </div>
                                </a>
                            </th>
                            <td class="px-11 py-4">
                                <a :href="$inertia.page.props.client_app_url + '/room/' + product.id" target="_blank">
                                    {{ product.price }} $
                                </a>
                            </td>

                            <td class="px-6 py-4">
                                <a :href="$inertia.page.props.client_app_url + '/room/' + product.id" target="_blank">
                                    {{ product.cate_name }}
                                </a>
                            </td>

                            <td class="px-6 py-4">
                                <a :href="$inertia.page.props.client_app_url + '/room/' + product.id" target="_blank">
                                    {{ moment(product.updated_at).format("DD-MM-YYYY") }}
                                </a>
                            </td>
                            <td class="px-6 py-4 ">
                                <div class="flex items-center">
                                    <a @click="handleRemove(product.id)" class="cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" data-slot="icon" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <div class="mt-2 mb-2 px-2">
                <Paginate :links="products.links"></Paginate>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped></style>

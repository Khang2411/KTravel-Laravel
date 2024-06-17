
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref } from 'vue'
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

defineProps({
    permissions: Object
})

const toastId = ref('');

const form = useForm({
    name: '',
    description: '',
    list_permission: []
});

const submit = () => {
    form.post(route('admin.role.store'), {
        onProgress: () => toastId.value = toast.loading('Loading...'),
        onSuccess: () => {
            toast.remove(toastId.value)
            toast.success('Thêm thành công!')
        }
    });
};
</script>

<template>

    <Head title="Thêm vai trò" />
    <AuthenticatedLayout>
        <div>
            <p class="px-5 dark:text-white text-2xl">Thêm vai trò</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 w-10/12 m-auto mt-5">
            <form @submit.prevent="submit" class="max-w-xl mx-auto">
                <div class="mb-5">
                    <InputLabel for="name" value="Tên vai trò" />
                    <TextInput type="text"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        v-model="form.name" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="mb-5">
                    <InputLabel for="description" value="Mô tả" />
                    <TextInput type="text"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        v-model="form.description" />
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>

                <div class="mb-5">
                    <p class="dark:text-white text-lg">Vai trò này có quyền gì?</p>
                    <div class="flex items-center gap-2" v-for="(permission, index) in permissions" :key="index">
                        <input type="checkbox" :id="permission.id"
                            v-model="form.list_permission" :value="permission.id"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label :for="permission.id" class="dark:text-white cursor-pointer">{{ permission.name }}</label>
                    </div>
                </div>

                <div class="text-right pr-2">
                    <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Thêm vai trò
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped></style>

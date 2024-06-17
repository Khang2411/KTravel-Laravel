<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

defineProps({
    roles: Object
})

const toastId = ref('');

const form = useForm({
    name: '',
    email: '',
    phone:'',
    password: '',
    password_confirmation: '',
    role_id: '',
});

const submit = () => {
    form.post(route('admin.user.store'), {
        onProgress: () => toastId.value = toast.loading('Loading...'),
        onSuccess: () => {
            toast.remove(toastId.value)
            toast.success('Thêm thành công!')
        }, onError: () => { toast.remove(toastId.value) },
    });
};
</script>

<template>

    <Head title="Thêm bộ thành viên" />
    <AuthenticatedLayout>
        <div>
            <p class="px-5 dark:text-white text-2xl">Thêm thành viên</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 w-1/2 m-auto mt-5">
            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="name" value="Name" />

                    <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name"
                        autocomplete="name" />

                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="mt-4">
                    <InputLabel for="email" value="Email" />

                    <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email"
                        autocomplete="username" />

                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-4">
                    <InputLabel for="phone" value="SĐT" />

                    <TextInput id="phone" type="text" class="mt-1 block w-full" v-model="form.phone"/>
                        
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password" value="Mật khẩu" />

                    <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password"
                        autocomplete="new-password" />

                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password_confirmation" value="Nhập lại mật khẩu" />

                    <TextInput id="password_confirmation" type="password" class="mt-1 block w-full"
                        v-model="form.password_confirmation" autocomplete="new-password" />

                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>

                <div class="mt-4">
                    <InputLabel for="role" value="Vai trò" />

                    <div class="flex flex-wrap gap-5">
                        <div v-for="(role, index) in roles" :key="index">
                            <input type="radio" :value="role.id" v-model="form.role_id" />
                            <label class="ml-2 dark:text-[#B6C6C2]">{{ role.name }}</label>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.role_id" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Thêm
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped></style>
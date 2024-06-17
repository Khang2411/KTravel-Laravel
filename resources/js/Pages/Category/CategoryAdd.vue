
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
    collections: Object
})
const previewIconUrl = ref('')
const toastId = ref('');

const form = useForm({
    name: '',
    icon: '',
});

const previewIcon = (e) => {
    const file = e.target.files[0];
    previewIconUrl.value = URL.createObjectURL(file);
}

const submit = () => {
    form.post(route('admin.category.store'), {
        onProgress: () => toastId.value = toast.loading('Loading...'),
        onSuccess: () => {
            toast.remove(toastId.value)
            toast.success('Thêm thành công!')
        },
        onError: () => { toast.remove(toastId.value) },
    });
}
</script>

<template>

    <Head title="Thêm thể loại" />
    <AuthenticatedLayout>
        <div>
            <p class="px-5 dark:text-white text-2xl">Thêm Thể loại</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 w-10/12 m-auto mt-5">
            <form @submit.prevent="submit" class="max-w-xl mx-auto">
                <div class="mb-5">
                    <InputLabel for="name" value="Tên thể loại" />
                    <TextInput type="text"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        v-model="form.name" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="mt-5">
                    <InputLabel for="icon" value="Chọn ảnh thể loại" class="cursor-pointer" />
                    <input type="file" id="icon" @input="form.icon = $event.target.files[0]" class="hidden"
                        @change="previewIcon" />
                    <InputError class="mt-2" :message="form.errors.icon" />
                    <img v-if="previewIconUrl" :src="previewIconUrl" class="w-52 mt-4 h-52" />
                </div>

                <div class="text-right pr-2 mt-2">
                    <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Thêm thể loại
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style lang="scss" scoped></style>

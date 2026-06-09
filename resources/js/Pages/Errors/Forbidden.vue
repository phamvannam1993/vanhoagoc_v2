<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded-2xl shadow-xl text-center max-w-md w-full">
            <h1 class="text-5xl font-bold text-red-500 mb-4">403</h1>
            <h2 class="text-xl font-semibold text-gray-800 mb-2">Truy cập bị từ chối</h2>
            <p class="text-gray-600 mb-6">
                Bạn không có quyền truy cập vào trang này.
            </p>
            <button
                @click="goBack"
                class="bg-red-500 text-white px-5 py-2 rounded-lg hover:bg-red-600 transition duration-200 mb-4"
            >
                Quay lại trang trước
            </button>
        </div>
    </div>
</template>

<script>
export default {
    methods: {
        goBack() {
            window.history.back();
        }
    }
}
</script>

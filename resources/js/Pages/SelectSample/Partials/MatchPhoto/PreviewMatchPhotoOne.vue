<script setup>
import { ref, defineProps, defineEmits, computed } from 'vue';

const props = defineProps({
    openPreview: Boolean, // Nhận v-model:openPreview từ cha
    data: Object, // Nhận v-model:data từ cha
});
const open = computed({
    get: () => props.openPreview, // Lấy giá trị từ cha
    set: (value) => emit('update:openPreview', value) // Gửi sự kiện để cập nhật
});
const emit = defineEmits(['update:openPreview']);
const handleOk = () => {
    open.value = false; // Đóng modal
};
const data = computed({
    get: () => props.data,
    set: (value) => emit('update:data', value),
});
const videoUrl = ref(data.value.video_title_show);
const handleClose = () => {
    openVideo.value = false;
    const saveVideo = data.value.video_title_show;
    videoUrl.value = ''; // Xóa src để reset video
    setTimeout(() => {
        videoUrl.value = saveVideo;
    }, 300);
}
</script>

<template>
    <a-modal v-model:open="openVideo" :width="1000" @cancel="handleClose">
        <div class="flex justify-center">
            <div v-if="data.video_title_show">
                <div v-if="data.isYouTubeVideo">
                    <!-- Nhúng video YouTube -->
                    <iframe
                        :src="videoUrl"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                        width="600"
                        height="300"
                    >
                    </iframe>
                </div>
                <div v-else>
                    <!-- Nếu không phải YouTube, hiển thị video bình thường -->
                    <video
                        :src="videoUrl"
                        controls
                        width="600"
                        height="300"
                    />
                </div>
            </div>
            <div v-else>Không có video để hiển thị</div>
        </div>
        <template #footer> </template>
    </a-modal>
</template>
<style>
.custom-height-sample {
    min-height: 455px;
    padding: 2.5rem;
}
</style>

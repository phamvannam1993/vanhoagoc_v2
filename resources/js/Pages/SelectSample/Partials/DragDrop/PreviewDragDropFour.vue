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
const listAnswer = computed({
    get: () => (props.data.answer ? JSON.parse(props.data.answer) : null),
    set: (value) => emit('update:data', value),
});
const openVideo = ref(false);
const openVideoTitle = () => {
    openVideo.value = true;
};
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
    <a-modal v-model:open="open" @ok="handleOk" :width="800">
        <template #title></template>
        <div class="flex gap-2">
            <div class="w-2/4 font-bold text-[18px]">
                {{ data.title || "Bạn điền tên" }}
            </div>
            <div v-if="data.audio_title_show" class="flex items-center">
                <img
                    class="h-[30px] w-[30px] cursor-pointer"
                    src="/images/icon-sound.png"
                    alt=""
                />
                <audio
                    ref="audioPlayer"
                    :src="data.audio_title_show"
                    controls
                ></audio>
            </div>
            <div v-if="data.video_title_name" class="flex gap-2 items-center">
                <img @click="openVideoTitle" class="h-[30px] w-[30px] cursor-pointer" src="/images/icon-game-choose-correct/icon-video-new.png">
                <div>{{ data.video_title_name }}</div>
            </div>
        </div>
        <div class="flex items-center gap-4 mt-4">
            <img class="w-1/3" v-if="data.image_question_show" :src="data.image_question_show" alt="">
            <div
                class="min-h-[240px] rounded-2xl border-2 border-solid border-[#E5E5E5] bg-white p-4 text-[16px] max-md:max-w-full"
            >
                {{ data.content }}
            </div>
        </div>
        <hr class="mt-4" />
        <div class="m-auto mt-4 w-4/5">
            <div class="dots-line mt-20"></div>
        </div>
        <div class="m-auto mt-10 flex flex-wrap gap-4">
            <div
                class="rounded-2xl border-2 border-solid border-[#E5E5E5] w-fit bg-white p-4 text-[16px] max-md:max-w-full"
                v-for="(item, index) in data.listAnswer"
                :key="index"
            >
                {{ item.inputAnswer }}
            </div>
        </div>
        <template #footer> </template>
    </a-modal>
    <a-modal v-model:open="openVideo" :width="800" @cancel="handleClose">
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
.dots-line {
    width: 100%;
    height: 1px;
    color: #dadada;
    background: repeating-linear-gradient(
        to right,
        grey 0,
        grey 1px,
        transparent 5px
    );
}
</style>

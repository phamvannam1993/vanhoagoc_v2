<script setup>
import { ref, defineProps, defineEmits, computed, onMounted } from "vue";
import { Field } from "vee-validate";

const props = defineProps({
    openTemplateModal: Boolean, // Nhận v-model:openPreview từ cha
    data: Object, // Nhận v-model:data từ cha
});
const open = computed({
    get: () => props.openTemplateModal, // Lấy giá trị từ cha
    set: (value) => emit('update:openTemplateModal', value) // Gửi sự kiện để cập nhật
});
const data = computed({
    get: () => props.data,
    set: (value) => emit('update:data', value),
});
const emit = defineEmits(['update:openTemplateModal', 'update:data']);
const updateData = (newData) => {
  emit('update:data', newData);
};
const handleOk = () => {
    const newData = {
        new_type: type.value,
        new_template_id: template_id.value
    };
    updateData(newData)
    open.value = false; // Đóng modal
};

const type = ref(props.data.new_type);
const list = ref([]);
const types = ref([]);
const template_id = ref(parseInt(props.data.template_current));
const previewImg = ref('');
const errorMessage = ref('');
onMounted(() => {
    loadData();
});
const loadData = async () => {
    const res = await axios.get(route("templates.json.listTemplateApp", {app_id: props.data.app_id}));
        list.value = res.data.list;
        list.value = res.data.list.map((v) => {
            return {
                id: v.id,
                type: v.type,
                image: v.image ? v.image : null,
                image_url: v.image_url ? v.image_url : null,
            };
        });
        types.value = res.data.types;
};
const handleChange = (value) => {
    type.value = value;
    template_id.value = 0;
};
const selectSample = (id, img) => {
    template_id.value = id;
    previewImg.value = getImageSrc(img);
};

const getImageSrc = (item) => {
    return `/images/games/${item}`;
};
</script>

<template>
    <a-modal v-model:open="open" @ok="handleOk" :width="1000">
        <template #title>Danh sách các mẫu</template>
        <div class="flex gap-2">
            <Field name="type" v-model="type" class="w-1/2">
                <a-select
                    class="input-search w-1/2"
                    v-model:value="type"
                    show-search
                    placeholder="Tất cả mẫu"
                    size="large"
                    :options="types"
                    disabled
                ></a-select>
            </Field>
        </div>
        <hr class="mt-4" />
        <div class="relative mt-6">
            <div class="grid grid-cols-4 gap-4">
                <img
                    v-for="(item, index) in list"
                    v-show="item.type === type"
                    :key="item.id"
                    :src="item.image_url"
                    @click="selectSample(item.id, item.image)"
                    :alt="'Image ' + index"
                    :class="`image-sample h-auto w-full cursor-pointer rounded shadow ${item.id === template_id ? 'active' : ''}`"
                />
            </div>
        </div>
        <template #footer>
            <a-button
                key="submit"
                type="primary"
                @click="handleOk"
            >Xác nhận
            </a-button>
        </template>
    </a-modal>
</template>
<style>
.custom-height-sample {
    min-height: 455px;
    padding: 2.5rem;
}
.active {
    border: 2px solid red;
}
</style>

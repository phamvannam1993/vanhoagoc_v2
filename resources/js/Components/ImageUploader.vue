<template>
  <div class="max-w-lg w-full bg-white rounded-lg shadow-md p-6">
    <h2 class="text-lg font-semibold text-gray-700 mb-4 text-center">
      {{ title }}
    </h2>

    <label
      for="file-upload"
      class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition"
      @dragover.prevent
      @drop.prevent="handleDrop"
    >
      <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center">
        <svg
          class="w-10 h-10 mb-3 text-gray-400"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M7 16V4a1 1 0 011-1h8a1 1 0 011 1v12m-4 4h.01M12 12l-3 3m3-3l3 3"
          ></path>
        </svg>
        <p class="mb-2 text-sm text-gray-500">
          <span class="font-semibold text-gray-700">Nhấn để chọn ảnh</span> hoặc kéo thả tại đây
        </p>
        <p class="text-xs text-gray-400">PNG, JPG (có thể chọn nhiều)</p>
      </div>
      <input id="file-upload" type="file" accept="image/*" multiple class="hidden" @change="handleFileChange" />
    </label>

    <!-- Danh sách ảnh (cũ + mới) -->
    <div v-if="allImages.length" class="mt-6">
      <h3 class="text-gray-700 font-semibold mb-3">Ảnh đã chọn:</h3>
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <div
          v-for="(img, index) in allImages"
          :key="img.id || index"
          class="relative group border rounded-lg overflow-hidden"
        >
          <img :src="img.url" class="w-full h-32 object-cover" />
          <button
            @click="removeImage(index)"
            class="absolute top-1 right-1 bg-black bg-opacity-60 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition"
            title="Xóa ảnh"
          >
            ✕
          </button>
        </div>
      </div>
    </div>

    <!-- Nút upload -->
    <button
      v-if="images.length || removedOld.length"
      class="w-full mt-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md transition"
      @click="emitImages"
    >
      Lưu ( {{ images.length }} mới | Xóa {{ removedOld.length }} )
    </button>
  </div>
</template>

<script setup>
import { ref, computed, defineEmits, defineProps, watch } from "vue";

// --- Props ---
const props = defineProps({
  title: {
    type: String,
    default: "Upload Ảnh"
  },
  oldImages: {   // ảnh cũ từ server
    type: Array,
    default: () => []
  }
});

// --- Emits ---
const emit = defineEmits(["update:images", "remove:old"]);

// --- State ---
const images = ref([]); // ảnh mới upload
const removedOld = ref([]); // lưu id hoặc path ảnh cũ bị xóa

// --- Kết hợp ảnh mới + cũ ---
const allImages = computed(() => {
  const mappedOldImages = props.oldImages
    .filter(img => !removedOld.value.includes(img.id))
    .map(img => ({
      ...img,
      url: img.full_url
    }));

  return [...mappedOldImages, ...images.value];
});

// --- Xử lý file ---
const previewFiles = (files) => {
  Array.from(files).forEach(file => {
    if (file.type.startsWith("image/")) {
      const reader = new FileReader();
      reader.onload = e => {
        images.value.push({ file, url: e.target.result });
      };
      reader.readAsDataURL(file);
    }
  });
};

const handleFileChange = (event) => {
  previewFiles(event.target.files);
  event.target.value = "";
};

const handleDrop = (event) => {
  previewFiles(event.dataTransfer.files);
};

// --- Xóa ảnh ---
const removeImage = (index) => {
  if (index < props.oldImages.length) {
    // ảnh cũ
    removedOld.value.push(props.oldImages[index].id);
    emit("remove:old", props.oldImages[index]);
  } else {
    // ảnh mới
    const newIndex = index - props.oldImages.length;
    images.value.splice(newIndex, 1);
  }
};

// --- Emit ảnh mới upload ---
const emitImages = () => {
  emit("update:images", images.value);
};
</script>

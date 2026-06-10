<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from 'vue-toastification';
import Editor from '@tinymce/tinymce-vue';

const props = defineProps({
    practiceId: {
        type: Number,
        required: true
    }
});

const emit = defineEmits(['approve']);

const isOpen = ref(false);
const drafts = ref([]);
const loading = ref(false);
const selectedDraft = ref(null);
const previewMode = ref(false);
const confirmModal = ref(false);
const confirmAction = ref(null);
const confirmMessage = ref('');
const editingText = ref(false);
const editingItem = ref(null);
const editingValue = ref('');
const toast = useToast();
const apiKey = import.meta.env.VITE_TINY_MCE_API_KEY;

const editorConfig = {
    height: '500px',
    plugins: 'lists link image table code help wordcount',
    menubar: 'file edit view format tools table',
    statusbar: true,
    language: 'vi',
    toolbar: 'undo redo | formatselect fontselect fontsizeselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image',
    font_family_formats:
        'Arial=arial,helvetica,sans-serif;' +
        'Times New Roman=times new roman,times,serif;' +
        'Courier New=courier new,courier,monospace;' +
        'Georgia=georgia,serif;' +
        'Verdana=verdana,sans-serif;',
};

const openModal = async () => {
    isOpen.value = true;
    await loadDrafts();
};

const closeModal = () => {
    isOpen.value = false;
    selectedDraft.value = null;
    previewMode.value = false;
};

const loadDrafts = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('lessons.json.draftHistory'), {
            params: { practice_id: props.practiceId }
        });

        if (response.data.success) {
            drafts.value = response.data.data;
        } else {
            toast.error(response.data.message || 'Lỗi tải lịch sử');
        }
    } catch (error) {
        console.error('Error loading drafts:', error);
        toast.error('Lỗi tải lịch sử');
    } finally {
        loading.value = false;
    }
};

const viewPreview = async (draft) => {
    try {
        const response = await axios.get(route('lessons.json.draftPreview'), {
            params: { draft_id: draft.id }
        });

        if (response.data.success) {
            selectedDraft.value = response.data.data;
            previewMode.value = true;
        } else {
            toast.error(response.data.message || 'Lỗi tải preview');
        }
    } catch (error) {
        console.error('Error loading preview:', error);
        toast.error('Lỗi tải preview');
    }
};

const openConfirmApprove = (draft) => {
    confirmAction.value = () => performApprove(draft);
    confirmMessage.value = 'Bạn có chắc chắn muốn phê duyệt bài giảng này? Nội dung sẽ được cập nhật vào bài học.';
    confirmModal.value = true;
};

const performApprove = async (draft) => {
    try {
        const response = await axios.post(route('lessons.json.draftApprove'), {
            draft_id: draft.id
        });

        if (response.data.success) {
            toast.success(response.data.message || 'Đã phê duyệt bài giảng');
            confirmModal.value = false;
            confirmAction.value = null;
            await loadDrafts();
            previewMode.value = false;
            selectedDraft.value = null;
            emit('approve', draft);
        } else {
            toast.error(response.data.message || 'Lỗi phê duyệt');
        }
    } catch (error) {
        console.error('Error approving draft:', error);
        toast.error('Lỗi phê duyệt');
    }
};

const openConfirmDelete = (draft) => {
    confirmAction.value = () => performDelete(draft);
    confirmMessage.value = 'Bạn có chắc chắn muốn xóa bản nháp này? Hành động này không thể hoàn tác.';
    confirmModal.value = true;
};

const performDelete = async (draft) => {
    try {
        const response = await axios.delete(route('lessons.json.draftDelete'), {
            params: { draft_id: draft.id }
        });

        if (response.data.success) {
            toast.success(response.data.message || 'Đã xóa bản nháp');
            confirmModal.value = false;
            confirmAction.value = null;
            await loadDrafts();
            previewMode.value = false;
            selectedDraft.value = null;
        } else {
            toast.error(response.data.message || 'Lỗi xóa');
        }
    } catch (error) {
        console.error('Error deleting draft:', error);
        toast.error('Lỗi xóa');
    }
};

const getTypeLabel = (type) => {
    const labels = {
        'text': 'Văn bản',
        'audio': 'Âm thanh',
        'video': 'Video',
        'text_audio': 'Văn bản + Âm thanh',
        'text_video': 'Văn bản + Video',
        'audio_video': 'Âm thanh + Video',
        'all': 'Tất cả'
    };
    return labels[type] || type;
};

const getStatusLabel = (status) => {
    return status === 'draft' ? 'Nháp' : 'Đã phê duyệt';
};

const formatBytes = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const onAudioError = (error) => {
    console.error('Audio error:', error);
    toast.error('Lỗi tải tệp âm thanh');
};

const onVideoError = (error) => {
    console.error('Video error:', error);
    toast.error('Lỗi tải tệp video');
};

const startEditItem = (type, index) => {
    const item = selectedDraft.value[`lesson_${type === 'audio' ? 'noi' : type === 'video' ? 'video' : 'doc'}`];
    editingItem.value = { type, index };
    editingValue.value = item || '';
};

const cancelEditItem = () => {
    editingItem.value = null;
    editingValue.value = '';
};

const saveEditItem = (item, newValue) => {
    if (typeof item === 'string') {
        // If it's a string, we can't modify it directly
        // Need to update parent array
        toast.info('Cập nhật trong array');
    } else if (typeof item === 'object') {
        // Update object properties
        item.noi_dung = newValue || item.noi_dung;
        item.value = newValue || item.value;
        item.text = newValue || item.text;
    }
    editingItem.value = null;
    editingValue.value = '';
    toast.success('✓ Đã cập nhật');
};

const getItemText = (item) => {
    if (typeof item === 'string') return item;
    if (typeof item === 'object') {
        return item.noi_dung || item.value || item.text || '';
    }
    return '';
};

const saveTextContent = async () => {
    if (!selectedDraft.value || !selectedDraft.value.lesson_doc) {
        toast.error('Không có nội dung để lưu');
        return;
    }

    try {
        const response = await axios.post(route('lessons.json.saveTextContent'), {
            draft_id: selectedDraft.value.id,
            lesson_doc: selectedDraft.value.lesson_doc,
        });

        if (response.data.success) {
            toast.success('✓ Nội dung văn bản đã cập nhật');
            editingText.value = false;
        } else {
            toast.error(response.data.message || 'Lỗi lưu nội dung');
        }
    } catch (error) {
        console.error('Error saving text:', error);
        toast.error('Lỗi lưu nội dung');
    }
};

// Expose methods for parent components
defineExpose({
    openModal
});
</script>

<template>
    <button
        @click="openModal"
        class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition"
    >
        📋 Lịch sử
    </button>

    <!-- Modal -->
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-5xl max-h-screen overflow-y-auto">
            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 p-4 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-blue-600">
                    {{ previewMode ? 'Xem trước bản nháp' : 'Lịch sử bài giảng' }}
                </h2>
                <button
                    @click="closeModal"
                    class="text-gray-500 hover:text-gray-700 text-2xl"
                >
                    ×
                </button>
            </div>

            <!-- Content -->
            <div class="p-8">
                <div v-if="loading" class="text-center py-8">
                    <p class="text-gray-500">Đang tải...</p>
                </div>

                <!-- Preview Mode -->
                <div v-else-if="previewMode && selectedDraft" class="space-y-3">
                    <div class="bg-gray-50 px-4 py-2 rounded-lg flex items-center gap-8">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 uppercase font-semibold">Loại:</span>
                            <span class="text-sm text-gray-800">{{ getTypeLabel(selectedDraft.type) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 uppercase font-semibold">Trạng thái:</span>
                            <span class="text-sm text-gray-800">{{ getStatusLabel(selectedDraft.status) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 uppercase font-semibold">Tạo lúc:</span>
                            <span class="text-sm text-gray-800">{{ selectedDraft.created_at }}</span>
                        </div>
                    </div>

                    <!-- Text Content -->
                    <div v-if="selectedDraft.lesson_doc" class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-bold text-blue-900">📄 Nội dung văn bản</h3>
                            <button
                                v-if="!editingText"
                                @click="editingText = true"
                                class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600"
                            >
                                ✏️ Sửa
                            </button>
                            <div v-else class="flex gap-2">
                                <button
                                    @click="editingText = false"
                                    class="px-3 py-1 bg-gray-400 text-white text-sm rounded hover:bg-gray-500"
                                >
                                    Hủy
                                </button>
                                <button
                                    @click="saveTextContent"
                                    class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600"
                                >
                                    💾 Lưu
                                </button>
                            </div>
                        </div>
                        <Editor
                            v-model="selectedDraft.lesson_doc"
                            :api-key="apiKey"
                            :init="{ ...editorConfig, readonly: !editingText }"
                        />
                    </div>

                    <!-- Audio Content -->
                    <div v-if="selectedDraft.lesson_noi" class="bg-green-50 p-4 rounded-lg">
                        <h3 class="font-bold text-green-900 mb-2">🔊 Nội dung âm thanh</h3>
                        <div class="bg-white p-3 rounded border border-green-200">
                            <audio
                                controls
                                class="w-full"
                                crossorigin="anonymous"
                                @error="onAudioError"
                            >
                                <source :src="selectedDraft.lesson_noi" type="audio/mpeg" />
                                Trình duyệt của bạn không hỗ trợ phát âm thanh.
                            </audio>
                        </div>
                        <p class="text-xs text-gray-600 mt-2">
                            {{ selectedDraft.lesson_noi }}
                        </p>
                    </div>

                    <!-- Video Content -->
                    <div v-if="selectedDraft.lesson_video" class="bg-purple-50 p-4 rounded-lg">
                        <h3 class="font-bold text-purple-900 mb-2">🎬 Nội dung video</h3>
                        <div class="bg-white p-3 rounded border border-purple-200">
                            <video
                                controls
                                class="w-full rounded"
                                crossorigin="anonymous"
                                @error="onVideoError"
                            >
                                <source :src="selectedDraft.lesson_video" type="video/mp4" />
                                Trình duyệt của bạn không hỗ trợ phát video.
                            </video>
                        </div>
                        <p class="text-xs text-gray-600 mt-2">
                            {{ selectedDraft.lesson_video }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <button
                            @click="previewMode = false"
                            class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400"
                        >
                            Quay lại
                        </button>
                        <button
                            @click="openConfirmApprove(selectedDraft)"
                            class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600"
                        >
                            ✓ Phê duyệt
                        </button>
                        <button
                            @click="openConfirmDelete(selectedDraft)"
                            class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                        >
                            🗑️ Xóa
                        </button>
                    </div>
                </div>

                <!-- List Mode -->
                <div v-else>
                    <div v-if="drafts.length === 0" class="text-center py-8">
                        <p class="text-gray-500">Chưa có bất kỳ bản nháp nào</p>
                    </div>

                    <div v-else class="space-y-2">
                        <div
                            v-for="draft in drafts"
                            :key="draft.id"
                            class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-pointer"
                            @click="viewPreview(draft)"
                        >
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex gap-2 items-center">
                                        <span class="font-semibold">{{ getTypeLabel(draft.type) }}</span>
                                        <span
                                            class="text-xs px-2 py-1 rounded"
                                            :class="draft.status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'"
                                        >
                                            {{ getStatusLabel(draft.status) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ draft.created_at }}
                                    </p>
                                    <div class="flex gap-3 mt-2 text-xs text-gray-600">
                                        <span v-if="draft.has_text" class="bg-blue-100 px-2 py-1 rounded">📄 Văn bản ({{ formatBytes(draft.text_length) }})</span>
                                        <span v-if="draft.has_audio" class="bg-green-100 px-2 py-1 rounded">🔊 Âm thanh</span>
                                        <span v-if="draft.has_video" class="bg-purple-100 px-2 py-1 rounded">🎬 Video</span>
                                    </div>
                                </div>
                                <button
                                    class="text-blue-600 hover:text-blue-800 font-semibold"
                                    @click.stop="viewPreview(draft)"
                                >
                                    Xem →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div v-if="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60]">
        <div class="bg-white rounded-lg shadow-lg max-w-sm w-full mx-4">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">⚠️ Xác nhận hành động</h3>
                <p class="text-gray-600 mb-6">{{ confirmMessage }}</p>

                <div class="flex gap-3">
                    <button
                        @click="confirmModal = false; confirmAction = null"
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 font-semibold"
                    >
                        Hủy
                    </button>
                    <button
                        @click="confirmAction"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold"
                    >
                        Xác nhận
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.editable-item {
    cursor: pointer;
    padding: 8px 12px;
    border-radius: 4px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    transition: all 0.2s;
}

.editable-item:hover {
    background: #eff6ff;
    border-color: #3b82f6;
}

.item-input {
    width: 100%;
    padding: 8px 12px;
    border: 2px solid #3b82f6;
    border-radius: 4px;
    font-size: 1rem;
    font-family: inherit;
}

.item-input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

button {
    transition: all 0.2s ease;
}

button:active {
    transform: scale(0.98);
}
</style>

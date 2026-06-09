<script setup>
import { computed } from 'vue';

const props = defineProps({
  generateTypes: Set,
  contentText: String,
  selectedFiles: Array,
  mucDo: String,
  lop: String,
  mon: String,
  voice: String,
  counts: Object,
  canGenerate: Boolean,
  generatingQuiz: Boolean,
  generatingText: Boolean,
  generatingAudio: Boolean,
  generationStatus: String,
  voices: Array,
});

const emit = defineEmits(['toggle-type', 'update-content', 'update-files', 'update-muc-do', 'update-lop', 'update-mon', 'update-voice', 'update-counts', 'generate']);

const totalCau = computed(() => {
  return (props.counts.chon || 0) + (props.counts.sx || 0) + (props.counts.noi || 0);
});
</script>

<template>
  <div class="space-y-4">
    <!-- Type Selection Checkboxes -->
    <div class="flex flex-wrap gap-3 pb-4 border-b border-gray-200">
      <label class="flex items-center gap-2 px-4 py-2 rounded-full cursor-pointer border transition" :class="generateTypes.has('exercises') ? 'bg-blue-100 border-blue-400' : 'bg-gray-100 border-gray-300'">
        <input
          type="checkbox"
          :checked="generateTypes.has('exercises')"
          @change="$emit('toggle-type', 'exercises')"
          class="w-4 h-4"
        />
        <span class="font-semibold text-sm">✏️ Bài Tập Luyện Tập</span>
      </label>

      <label class="flex items-center gap-2 px-4 py-2 rounded-full cursor-pointer border transition" :class="generateTypes.has('text') ? 'bg-blue-100 border-blue-400' : 'bg-gray-100 border-gray-300'">
        <input
          type="checkbox"
          :checked="generateTypes.has('text')"
          @change="$emit('toggle-type', 'text')"
          class="w-4 h-4"
        />
        <span class="font-semibold text-sm">📖 Bài đọc <span class="text-xs text-gray-500">(10-30s)</span></span>
      </label>

      <label class="flex items-center gap-2 px-4 py-2 rounded-full cursor-pointer border transition" :class="generateTypes.has('audio') ? 'bg-blue-100 border-blue-400' : 'bg-gray-100 border-gray-300'">
        <input
          type="checkbox"
          :checked="generateTypes.has('audio')"
          @change="$emit('toggle-type', 'audio')"
          class="w-4 h-4"
        />
        <span class="font-semibold text-sm">🎙️ Sách nói <span class="text-xs text-gray-500">(2-3 phút)</span></span>
      </label>
    </div>

    <!-- Form Section -->
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-50 to-white border-b border-gray-200 p-4 flex items-start gap-3">
        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">1</div>
        <div class="flex-1">
          <h3 class="text-lg font-bold text-gray-800">Nguồn & thiết lập</h3>
          <p class="text-xs text-gray-600 mt-1">Nội dung, mục độ và tùy chọn tạo bài</p>
        </div>
        <button class="text-gray-500 hover:text-gray-700">▲</button>
      </div>

      <!-- Content -->
      <div class="p-4 space-y-6">
        <!-- Nội dung câu hỏi -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Nội dung câu hỏi / chủ đề bài học</label>
          <textarea
            :value="contentText"
            @input="$emit('update-content', $event.target.value)"
            placeholder="Nhập nội dung câu hỏi..."
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 h-32"
          />
        </div>

        <!-- Mức độ, Lớp, Môn, Giọng nói -->
        <div class="grid grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Mức độ</label>
            <select :value="mucDo" @change="$emit('update-muc-do', $event.target.value)" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
              <option>Dễ</option>
              <option>Trung bình</option>
              <option>Khó</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Lớp</label>
            <input :value="lop" @input="$emit('update-lop', $event.target.value)" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Môn</label>
            <input :value="mon" @input="$emit('update-mon', $event.target.value)" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Giọng nói</label>
            <select :value="voice" @change="$emit('update-voice', $event.target.value)" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
              <option v-for="v in voices" :key="v.value" :value="v.value">{{ v.label }}</option>
            </select>
          </div>
        </div>

        <!-- File uploads -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Hoặc upload file (Ảnh, PDF, Word, Excel)</label>
          <input
            type="file"
            @change="$emit('update-files', $event.target.files)"
            accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
            multiple
            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
          />
          <p class="text-xs text-orange-600 mt-1">⚠️ Giới hạn: 15MB/file, tối đa 20 file</p>
        </div>

        <!-- Số lượng câu hỏi -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <h4 class="font-semibold text-gray-800 mb-3">Số lượng câu hỏi theo loại (Bài tập)</h4>
          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Chọn</label>
              <div class="flex items-center gap-2">
                <button @click="$emit('update-counts', {...counts, chon: Math.max(0, counts.chon - 1)})" class="px-3 py-2 border border-gray-300 rounded">−</button>
                <input :value="counts.chon" type="number" min="0" max="50" @input="$emit('update-counts', {...counts, chon: parseInt($event.target.value) || 0})" class="flex-1 text-center px-3 py-2 border border-gray-300 rounded-lg" />
                <button @click="$emit('update-counts', {...counts, chon: counts.chon + 1})" class="px-3 py-2 border border-gray-300 rounded">+</button>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Sắp xếp</label>
              <div class="flex items-center gap-2">
                <button @click="$emit('update-counts', {...counts, sx: Math.max(0, counts.sx - 1)})" class="px-3 py-2 border border-gray-300 rounded">−</button>
                <input :value="counts.sx" type="number" min="0" max="50" @input="$emit('update-counts', {...counts, sx: parseInt($event.target.value) || 0})" class="flex-1 text-center px-3 py-2 border border-gray-300 rounded-lg" />
                <button @click="$emit('update-counts', {...counts, sx: counts.sx + 1})" class="px-3 py-2 border border-gray-300 rounded">+</button>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nối</label>
              <div class="flex items-center gap-2">
                <button @click="$emit('update-counts', {...counts, noi: Math.max(0, counts.noi - 1)})" class="px-3 py-2 border border-gray-300 rounded">−</button>
                <input :value="counts.noi" type="number" min="0" max="50" @input="$emit('update-counts', {...counts, noi: parseInt($event.target.value) || 0})" class="flex-1 text-center px-3 py-2 border border-gray-300 rounded-lg" />
                <button @click="$emit('update-counts', {...counts, noi: counts.noi + 1})" class="px-3 py-2 border border-gray-300 rounded">+</button>
              </div>
            </div>
          </div>
          <p class="text-sm text-gray-600 mt-3">
            <span v-if="totalCau < 5" class="text-red-600">⚠️ Tối thiểu 5 câu hỏi</span>
            <span v-else-if="totalCau > 50" class="text-red-600">⚠️ Tối đa 50 câu hỏi</span>
            <span v-else class="text-green-600">✓ Hợp lệ</span>
          </p>
        </div>

        <!-- Status & Generate Button -->
        <div class="pt-2">
          <p class="text-xs text-gray-600 mb-3">Sẽ tạo {{ totalCau }} loại bài giảng</p>
          <button
            @click="$emit('generate')"
            :disabled="!canGenerate || generatingQuiz || generatingText || generatingAudio"
            class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 font-semibold transition text-lg"
          >
            {{ generatingQuiz || generatingText || generatingAudio ? 'Đang xử lý...' : 'Tạo bài giảng' }}
          </button>

          <!-- Status Message -->
          <div v-if="generationStatus" class="mt-3 p-4 rounded-lg text-center font-semibold" :class="generationStatus.includes('✓') ? 'bg-green-100 text-green-800' : generationStatus.includes('✗') ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'">
            {{ generationStatus }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

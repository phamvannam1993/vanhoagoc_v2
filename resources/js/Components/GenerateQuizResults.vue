<script setup>
const props = defineProps({
  quizResult: Object,
  selectedQuestionTab: String,
  editingQuestion: Object,
  selectedByType: Object,
  loading: Boolean,
});

const emit = defineEmits(['update-tab', 'add-questions', 'save-questions', 'edit', 'delete', 'update-edit', 'save-edit', 'cancel-edit']);

const getOptionText = (opt) => {
  if (typeof opt === 'string') return opt;
  if (typeof opt === 'object') {
    return opt.noi_dung || opt.value || opt.text || opt.answer_val || '';
  }
  return '';
};

const isCorrectAnswer = (dapAnDung, optKey) => {
  if (!dapAnDung) return false;
  const answers = dapAnDung.split(',').map(a => a.trim());
  return answers.includes(optKey);
};
</script>

<template>
  <div class="bg-white border border-gray-200 rounded-lg overflow-hidden" v-if="quizResult">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-50 to-white border-b border-gray-200 p-4 flex items-center gap-3">
      <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-600 text-white flex items-center justify-center font-bold text-sm">✓</div>
      <div class="flex-1">
        <h3 class="text-lg font-bold text-gray-800">Bài tập luyện tập</h3>
        <p class="text-xs text-gray-600 mt-1">{{ (quizResult.chon?.length || 0) + (quizResult.sx?.length || 0) + (quizResult.noi?.length || 0) }} câu hỏi</p>
      </div>
      <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-bold">
        {{ (quizResult.chon?.length || 0) + (quizResult.sx?.length || 0) + (quizResult.noi?.length || 0) }}
      </span>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 p-4">
      <div class="flex gap-2">
        <button
          @click="$emit('update-tab', 'chon')"
          :class="[
            'px-4 py-2 text-sm font-semibold rounded-lg transition',
            selectedQuestionTab === 'chon'
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          ]"
        >
          Chọn ({{ quizResult.chon?.length || 0 }})
        </button>
        <button
          @click="$emit('update-tab', 'sx')"
          :class="[
            'px-4 py-2 text-sm font-semibold rounded-lg transition',
            selectedQuestionTab === 'sx'
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          ]"
        >
          Sắp xếp ({{ quizResult.sx?.length || 0 }})
        </button>
        <button
          @click="$emit('update-tab', 'noi')"
          :class="[
            'px-4 py-2 text-sm font-semibold rounded-lg transition',
            selectedQuestionTab === 'noi'
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          ]"
        >
          Nối ({{ quizResult.noi?.length || 0 }})
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="p-4 space-y-3 max-h-[600px] overflow-y-auto">
      <!-- Chọn Tab -->
      <div v-if="selectedQuestionTab === 'chon'" class="space-y-3">
        <div v-if="!quizResult.chon?.length" class="text-center py-8 text-gray-500">
          Không có câu hỏi Chọn
        </div>
        <div v-for="(q, idx) in quizResult.chon" :key="`chon-${idx}`" class="border rounded-lg p-4 transition" :class="selectedByType.chon.has(idx) ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
          <div class="flex items-start justify-between gap-3 mb-2">
            <div class="flex items-start gap-3 flex-1">
              <input type="checkbox" :checked="selectedByType.chon.has(idx)" @change="selectedByType.chon.has(idx) ? selectedByType.chon.delete(idx) : selectedByType.chon.add(idx)" class="w-5 h-5 mt-0.5 text-blue-600 cursor-pointer" />
              <div>
                <p class="text-xs text-gray-600 mb-1">{{ q.tieu_de || 'Chọn đáp án đúng' }}</p>
                <p class="font-semibold text-gray-900">{{ idx + 1 }}. {{ q.cau_hoi }}</p>
              </div>
            </div>
            <div class="flex gap-2 min-w-fit">
              <button @click="$emit('edit', 'chon', idx)" class="px-3 py-1 bg-yellow-100 text-yellow-700 text-sm font-semibold rounded hover:bg-yellow-200">Sửa</button>
              <button @click="$emit('delete', 'chon', idx)" class="px-3 py-1 bg-red-100 text-red-700 text-sm font-semibold rounded hover:bg-red-200">Xóa</button>
            </div>
          </div>
          <div class="space-y-1">
            <div v-for="(opt, optIdx) in q.options" :key="optIdx" class="px-4 py-2 rounded text-sm" :class="q.dap_an_dung === (opt.key || String.fromCharCode(65 + optIdx)) ? 'bg-green-200 text-green-900 font-semibold' : 'bg-gray-100 text-gray-700'">
              {{ opt.key || String.fromCharCode(65 + optIdx) }}. {{ getOptionText(opt) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Sắp xếp Tab -->
      <div v-else-if="selectedQuestionTab === 'sx'" class="space-y-3">
        <div v-if="!quizResult.sx?.length" class="text-center py-8 text-gray-500">
          Không có câu hỏi Sắp xếp
        </div>
        <div v-for="(q, idx) in quizResult.sx" :key="`sx-${idx}`" class="border rounded-lg p-4 transition" :class="selectedByType.sx.has(idx) ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
          <div class="flex items-start justify-between gap-3 mb-2">
            <div class="flex items-start gap-3 flex-1">
              <input type="checkbox" :checked="selectedByType.sx.has(idx)" @change="selectedByType.sx.has(idx) ? selectedByType.sx.delete(idx) : selectedByType.sx.add(idx)" class="w-5 h-5 mt-0.5 text-blue-600 cursor-pointer" />
              <div>
                <p class="text-xs text-gray-600 mb-1">{{ q.tieu_de || 'Sắp xếp' }}</p>
                <p class="font-semibold text-gray-900">{{ idx + 1 }}. {{ q.cau_hoi }}</p>
              </div>
            </div>
            <div class="flex gap-2 min-w-fit">
              <button @click="$emit('edit', 'sx', idx)" class="px-3 py-1 bg-yellow-100 text-yellow-700 text-sm font-semibold rounded hover:bg-yellow-200">Sửa</button>
              <button @click="$emit('delete', 'sx', idx)" class="px-3 py-1 bg-red-100 text-red-700 text-sm font-semibold rounded hover:bg-red-200">Xóa</button>
            </div>
          </div>
          <div class="space-y-1">
            <div v-for="(item, itemIdx) in (q.items || q.cot_a || q.options || [])" :key="itemIdx" class="px-4 py-2 bg-gray-100 text-gray-700 rounded text-sm">
              {{ itemIdx + 1 }}. {{ typeof item === 'string' ? item : item.noi_dung || item.value }}
            </div>
          </div>
        </div>
      </div>

      <!-- Nối Tab -->
      <div v-else-if="selectedQuestionTab === 'noi'" class="space-y-3">
        <div v-if="!quizResult.noi?.length" class="text-center py-8 text-gray-500">
          Không có câu hỏi Nối
        </div>
        <div v-for="(q, idx) in quizResult.noi" :key="`noi-${idx}`" class="border rounded-lg p-4 transition" :class="selectedByType.noi.has(idx) ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
          <div class="flex items-start justify-between gap-3 mb-2">
            <div class="flex items-start gap-3 flex-1">
              <input type="checkbox" :checked="selectedByType.noi.has(idx)" @change="selectedByType.noi.has(idx) ? selectedByType.noi.delete(idx) : selectedByType.noi.add(idx)" class="w-5 h-5 mt-0.5 text-blue-600 cursor-pointer" />
              <div>
                <p class="text-xs text-gray-600 mb-1">{{ q.tieu_de || 'Nối' }}</p>
                <p class="font-semibold text-gray-900">{{ idx + 1 }}. {{ q.cau_hoi }}</p>
              </div>
            </div>
            <div class="flex gap-2 min-w-fit">
              <button @click="$emit('edit', 'noi', idx)" class="px-3 py-1 bg-yellow-100 text-yellow-700 text-sm font-semibold rounded hover:bg-yellow-200">Sửa</button>
              <button @click="$emit('delete', 'noi', idx)" class="px-3 py-1 bg-red-100 text-red-700 text-sm font-semibold rounded hover:bg-red-200">Xóa</button>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <span class="text-xs font-semibold text-gray-700">Cột A:</span>
              <div class="space-y-1 mt-1">
                <div v-for="(item, i) in q.cot_a" :key="i" class="px-3 py-2 bg-gray-100 text-gray-700 rounded text-xs">
                  {{ i + 1 }}. {{ typeof item === 'string' ? item : item.noi_dung || item.value }}
                </div>
              </div>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-700">Cột B:</span>
              <div class="space-y-1 mt-1">
                <div v-for="(item, i) in q.cot_b" :key="i" class="px-3 py-2 bg-gray-100 text-gray-700 rounded text-xs">
                  {{ i + 1 }}. {{ typeof item === 'string' ? item : item.noi_dung || item.value }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Save Button -->
    <div class="border-t border-gray-200 p-4 bg-white">
      <button
        @click="$emit('save-questions')"
        :disabled="loading"
        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 font-semibold text-sm transition"
      >
        {{ loading ? 'Đang lưu...' : 'Lưu bài giảng' }}
      </button>
    </div>
  </div>

  <div v-else class="bg-gray-50 p-8 rounded-lg border border-gray-200 text-center">
    <p class="text-sm text-gray-500">Chưa có câu hỏi nào được tạo</p>
    <p class="text-xs text-gray-400 mt-1">Bấm "Tạo bài giảng" để bắt đầu</p>
  </div>
</template>

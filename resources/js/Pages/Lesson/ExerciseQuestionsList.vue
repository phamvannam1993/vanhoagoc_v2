<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MasterLayout from '@/Layouts/MasterLayout.vue';

const props = defineProps({
  query: Object,
  item: Object,
  questions: Array,
});

const localQuestions = ref([]);

onMounted(() => {
  localQuestions.value = props.questions || [];
});
</script>

<template>
  <MasterLayout>
    <Head title="Danh sách câu hỏi" />
    <div>
      <!-- Breadcrumb -->
      <div class="bg-gray-50 px-6 py-3 border-b">
        <div class="max-w-full mx-auto">
          <nav class="flex text-sm">
            <Link href="/lessons" class="text-blue-600 hover:underline">Bài giảng</Link>
            <span class="mx-2 text-gray-400">/</span>
            <Link :href="`/assigned-exercises?app_id=${query.app_id}&book_id=${query.book_id}&week_id=${query.week_id}&practice_id=${query.practice_id}`" class="text-blue-600 hover:underline">Bài tập giao</Link>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-600">Câu hỏi</span>
          </nav>
        </div>
      </div>

      <div class="p-6">
        <div class="mb-6">
          <Link :href="`/assigned-exercises?app_id=${query.app_id}&book_id=${query.book_id}&week_id=${query.week_id}&practice_id=${query.practice_id}`" class="btn btn-secondary mb-4">← Quay lại</Link>
          <h1 class="text-2xl font-bold mb-2">Danh sách câu hỏi</h1>
          <p class="text-gray-600">{{ item?.name }} - {{ item?.level }} - {{ questions?.length || 0 }} câu</p>
        </div>

        <div class="card">
          <div v-if="!localQuestions?.length" class="p-6 text-center text-gray-500">
            Chưa có câu hỏi
          </div>

          <table v-else class="w-full">
            <thead>
              <tr class="border-b bg-gray-50">
                <th class="text-left p-4">STT</th>
                <th class="text-left p-4">Nội dung câu hỏi</th>
                <th class="text-center p-4">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(question, idx) in localQuestions" :key="question.id" class="border-b hover:bg-gray-50">
                <td class="p-4">{{ idx + 1 }}</td>
                <td class="p-4">
                  <div class="font-medium">{{ question.title || question.question_val || 'Câu hỏi' }}</div>
                  <div class="text-sm text-gray-500 mt-1">
                    Loại: {{ question.question_type }} |
                    Đáp án: {{ (question.answers?.length || 0) + (question.answer_connects?.length || 0) }}
                  </div>
                </td>
                <td class="p-4 text-center">
                  <button class="btn btn-sm btn-blue">Xem</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </MasterLayout>
</template>

<style scoped>
.card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  overflow: hidden;
}

.btn {
  padding: 8px 12px;
  border-radius: 4px;
  text-decoration: none;
  display: inline-block;
  cursor: pointer;
  border: none;
  font-size: 14px;
  text-align: center;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-blue {
  background: #3b82f6;
  color: white;
  padding: 4px 8px;
  font-size: 12px;
}

.btn:hover {
  opacity: 0.9;
}

table {
  border-collapse: collapse;
}

thead th {
  font-weight: 600;
  text-align: left;
}

tbody td {
  vertical-align: middle;
}
</style>

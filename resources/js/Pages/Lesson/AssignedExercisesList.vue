<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MasterLayout from '@/Layouts/MasterLayout.vue';

defineProps({
  query: Object,
  practice: Object,
  items: Array,
});

const searchTerm = ref('');
</script>

<template>
  <MasterLayout>
  <Head title="Bài tập giao học sinh" />
  <div>
    <!-- Breadcrumb -->
    <div class="bg-gray-50 px-6 py-3 border-b">
      <div class="max-w-7xl mx-auto">
        <nav class="flex text-sm">
          <Link href="/lessons" class="text-blue-600 hover:underline">Bài giảng</Link>
          <span class="mx-2 text-gray-400">/</span>
          <span class="text-gray-600">Bài tập giao học sinh</span>
        </nav>
      </div>
    </div>

    <div class="container mx-auto p-4">
      <div class="mb-6">
        <Link :href="`/lessons?app_id=${query.app_id}&book_id=${query.book_id}&week_id=${query.week_id}`" class="btn btn-secondary mb-4">← Quay lại</Link>
        <h1 class="text-2xl font-bold mb-2">Bài tập giao học sinh</h1>
        <p class="text-gray-600">{{ practice?.name }} - {{ items?.length || 0 }} bài</p>
      </div>

    <div class="card">
      <table class="w-full">
        <thead>
          <tr class="border-b">
            <th class="text-left p-3">STT</th>
            <th class="text-left p-3">Tên bài</th>
            <th class="text-left p-3">Mức độ</th>
            <th class="text-left p-3">Số câu hỏi</th>
            <th class="text-center p-3">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, idx) in items" :key="item.id" class="border-b hover:bg-gray-50">
            <td class="p-3">{{ idx + 1 }}</td>
            <td class="p-3 font-medium">{{ item.name || `Bài ${idx + 1}` }}</td>
            <td class="p-3">
              <span class="badge" :class="{
                'badge-easy': item.level === 'Dễ',
                'badge-medium': item.level === 'Trung bình',
                'badge-hard': item.level === 'Khó'
              }">{{ item.level }}</span>
            </td>
            <td class="p-3">{{ item.total_questions || 0 }} câu</td>
            <td class="p-3 text-center">
              <Link :href="`/questionEditors?app_id=${query.app_id}&book_id=${query.book_id}&week_id=${query.week_id}&practice_id=${query.practice_id}&exercise_item_id=${item.id}`" class="btn btn-sm btn-blue">Xem câu hỏi</Link>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="!items?.length" class="p-6 text-center text-gray-500">
        Chưa có bài tập giao
      </div>
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
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-blue {
  background: #3b82f6;
  color: white;
}

.btn:hover {
  opacity: 0.9;
}

.badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
}

.badge-easy {
  background: #d1fae5;
  color: #065f46;
}

.badge-medium {
  background: #fef3c7;
  color: #92400e;
}

.badge-hard {
  background: #fee2e2;
  color: #991b1b;
}
</style>

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
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-blue {
  background: #3b82f6;
  color: white;
}

.btn:hover {
  opacity: 0.9;
}
</style>

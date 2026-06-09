<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import HrLayout from '@/Layouts/HrLayout.vue';
import { SearchOutlined } from '@ant-design/icons-vue';
import { ref, onMounted, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import Sortable from 'sortablejs';

const toast = useToast();
const page = usePage();
const user = page.props.auth.user;

const query = page.props.query;
const columns = [
  {
    title: 'STT',
    dataIndex: 'id',
    key: 'id',
    width: 80,
    align: 'center',
  },
  {
    title: 'Tên học sinh',
    dataIndex: 'name',
    key: 'name',
    width: '30%',
  },
  {
    title: 'Thời gian giao',
    dataIndex: 'time_given',
    key: 'time_given',
  },
  {
    title: 'Trạng thái',
    key: 'status',
    dataIndex: 'status',
  },
  {
    title: 'Thành tích',
    key: 'point',
    dataIndex: 'point',
  },
  {
    title: 'Thời gian làm bài',
    key: 'duration',
  },
];
const pagination = ref({
  current: 1,
  pageSize: 1,
  total: 0,
});
const data = ref([]);
const formFilter = ref({
  search: '',
});
const tableRef = ref(null);
onMounted(async () => {
  await loadData();
});
const loadData = async () => {
  const params = {
    page: pagination.value.current,
    search: formFilter.value.search,
  };
  const res = await axios.get(route('hr.point.json.list', params));

  if (res.data.status) {
    data.value = res.data.data.data.map((v) => {
      return {
        id: v.id,
        name: v.user?.student_name,
        time_given: v.time,
        point: v.star_count,
        status: 'Hoàn thành',
        duration: '10:00',
      };
    });

    pagination.value.pageSize = res.data.data.per_page;
    pagination.value.total = res.data.data.total;
    pagination.value.current = res.data.data.current_page;
  }
};

const onPageChange = (page) => {
  pagination.value.current = page;
  loadData();
};
</script>

<template>
  <Head title="Kết quả" />

  <HrLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Kết Quả</h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-10/12">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Kết Quả</h1>

        <div class="mt-4">
          <a-table
            :columns="columns"
            :data-source="data"
            :pagination="false"
            ref="tableRef"
            rowKey="id"
            :scroll="{ x: 'max-content' }"
          >
            <template #bodyCell="{ column, record, index }">
              <template v-if="column.key === 'id'">
                {{ index + 1 + pagination.pageSize * (pagination.current - 1) }}
              </template>
              <template v-if="column.key === 'name'">
                <div class="flex gap-2">
                  <p class="flex items-center font-bold">
                    {{ record.name }}
                  </p>
                </div>
              </template>
              <template v-if="column.key === 'time_given'">
                <p class="flex items-center font-bold">
                  {{ record.time_given }}
                </p>
              </template>
              <template v-if="column.key === 'status'">
                <p
                  :class="`flex items-center font-bold ${record.status == 'Hoàn thành' ? 'text-green-500' : 'text-rose-500'}`"
                >
                  {{ record.status }}
                </p>
              </template>
              <template v-if="column.key === 'point'">
                <p class="flex items-center font-bold">{{ record.point }}/50</p>
              </template>
              <template v-if="column.key === 'duration'">
                <p class="flex items-center font-bold">
                  {{ record.duration }}
                </p>
              </template>
            </template>
            <template #footer>
              <div class="flex items-center justify-end">
                <!-- Pagination -->
                <a-pagination v-bind="pagination" @change="onPageChange" />
                <!-- Icon plus -->
              </div>
            </template>
          </a-table>
        </div>
      </div>
    </div>
  </HrLayout>
</template>
<style lang="scss">
.ant-select-selector,
.ant-input-affix-wrapper {
  border-color: #5fb2ff !important;
}

.ant-btn-primary:disabled {
  background-color: #b1b1b1;
  color: white;
}

.grey-row {
  background-color: darkgray;
}
</style>

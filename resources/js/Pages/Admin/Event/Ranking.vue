<script setup>
import { defineProps } from "vue";
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";

const props = defineProps({
    event: {
        type: Object,
    },
    entities: {
        type: Object,
    }
});

const entities = ref(props.entities);

const columns = [
      {
        title: 'STT',
        dataIndex: 'id',
        key: 'id',
        width: 80,
        align: 'center',
      },
      {
        title: 'Tên',
        dataIndex: 'name',
        key: 'name',
        width: '30%',
      },
      {
        title: 'SDT',
        dataIndex: 'tel',
        key: 'tel',
      },
      {
        title: 'Điểm',
        dataIndex: 'star_count',
        key: 'star_count',
      },
      {
        title: 'Thời gian',
        dataIndex: 'time',
        key: 'time',
      },
      {
        title: 'Lần làm bài',
        dataIndex: 'time_number',
        key: 'time_number',
      },
      {
        title: '',
        key: 'action',
    },
];

const pagination = ref({
  current: props.entities.current_page,
  pageSize: props.entities.per_page,
  total: props.entities.total,
});

const tableRef = ref(null);

const onPageChange = (page) => {
  pagination.value.current = page;
  loadData();
};

const loadData = async () => {
    const params = {
        page: pagination.value.current,
    };
    const res = await axios.get(route('admins.event.ranking', {id: props.event.id, ...params}));

    if (res.data.status) {
        entities.value = res.data.entities

        pagination.value.pageSize = res.data.entities.per_page;
        pagination.value.total = res.data.entities.total;
        pagination.value.current = res.data.entities.current_page;
    }
};

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;

    if (mins === 0) {
        return `${secs} giây`;
    }

    return `${mins} phút ${secs} giây`;
}

</script>

<template>
  <Head :title="`Bảng kết quả - ${props.event.name}`" />

  <SchoolLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Bảng kết quả - {{ props.event.name }}</h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-10/12">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Bảng kết quả - {{ props.event.name }}</h1>
        <div class="filter-page mt-4 flex gap-10">
            <a
                :href="route('admins.event.export-ranking', { id: props.event.id })"
                class="ml-auto"
                >
                <a-button type="primary">Xuất Excel</a-button>
            </a>
        </div>
        <div class="mt-4">
          <a-table
            :columns="columns"
            :data-source="entities.data"
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
                    {{ record.user?.name }}
                </template>
                <template v-if="column.key === 'tel'">
                    {{ record.user?.tel }}
                </template>
                <template v-if="column.key === 'star_count'">
                    {{ record.star_count }}
                </template>
                <template v-if="column.key === 'time'">
                    {{ formatTime(record.time) }}
                </template>
                <template v-if="column.key === 'time_number'">
                    Lần {{ record.time_number ?? 1 }}
                </template>
            </template>
            <template #footer>
              <div class="flex items-center justify-end">
                <!-- Pagination -->
                <a-pagination v-bind="pagination" @change="onPageChange" />
              </div>
            </template>
          </a-table>
        </div>
      </div>
    </div>
  </SchoolLayout>
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

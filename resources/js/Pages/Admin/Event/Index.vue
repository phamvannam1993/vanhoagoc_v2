<script setup>
import { defineProps } from "vue";
import { Head, Link } from '@inertiajs/vue3';
import { SearchOutlined } from '@ant-design/icons-vue';
import { ref } from 'vue';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { useToast } from 'vue-toastification';

const toast = useToast();
const props = defineProps({
    entities: {
        type: Object,
    },
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
        title: 'Tên sự kiện',
        dataIndex: 'name',
        key: 'name',
    },
    {
        title: 'Lớp',
        dataIndex: 'class_name',
        key: 'class_name',
    },
     {
        title: 'Lượt chơi',
        dataIndex: 'number_of_times',
        key: 'number_of_times',
    },
     {
        title: 'Thời lượng',
        dataIndex: 'duration',
        key: 'duration',
    },
    {
        title: 'Thời gian bắt đầu',
        dataIndex: 'start_datetime_label',
        key: 'start_datetime_label',
    },
    {
        title: 'Thời gian kết thúc',
        dataIndex: 'end_datetime_label',
        key: 'end_datetime_label',
    },
    {
        title: 'Trạng thái',
        dataIndex: 'status_label',
        key: 'status_label',
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
const data = ref([]);
const formFilter = ref({
  search: '',
});
const tableRef = ref(null);

const onPageChange = (page) => {
  pagination.value.current = page;
  loadData();
};
const onSearch = () => {
  loadData();
};
const removeFilter = () => {
  formFilter.value.search = '';
  loadData();
};

const loadData = async () => {
    const params = {
        page: pagination.value.current,
        search: formFilter.value.search,
    };
    const res = await axios.get(route('admins.event.index', params));

    if (res.data.status) {
        entities.value = res.data.entities

        pagination.value.pageSize = res.data.entities.per_page;
        pagination.value.total = res.data.entities.total;
        pagination.value.current = res.data.entities.current_page;
    }
};

const confirm = (record) => {
    if (!record.can_delete) {
        toast.warning('Không thể xóa sự kiện đang diễn ra!');
        return;
    }
    axios
        .delete(route('admins.event.destroy', { id: record.id }))
        .then((response) => {
            if (response.status === 200) {
                toast.success('Xóa sự kiện thành công~');
                setTimeout(function () {
                    location.href = route('admins.event.index')
                }, 500);
            }
        })
        .catch(() => {
            toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
        });
};

const activate = (record) => {
    if (record.status !== 0) {
        toast.warning('Chỉ có thể kích hoạt sự kiện ở trạng thái Nháp!');
        return;
    }
    axios
        .patch(route('admins.event.activate', { id: record.id }))
        .then((response) => {
            if (response.data.status) {
                toast.success('Kích hoạt sự kiện thành công!');
                loadData();
            }
        })
        .catch(() => {
            toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
        });
};

const statusColor = (status) => {
    if (status == 0) return 'text-gray-400';   // Nháp
    if (status == 1) return 'text-yellow-500'; // Sắp diễn ra
    if (status == 2) return 'text-green-500';  // Đang diễn ra
    if (status == 3) return 'text-gray-500';   // Đã kết thúc
}

</script>

<template>
  <Head title="App" />

  <SchoolLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">App</h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-10/12">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Danh sách Sự kiện</h1>
        <div class="mt-2">
            <Link :href="route('admins.event.create')">
                <a-button  type="primary">Thêm mới</a-button>
            </Link>
        </div>
        <div class="filter-page mt-4 flex gap-5 flex-col md:flex-row">
            <a-input
                class="md:w-[300px]"
                placeholder="Tìm kiếm"
                :allow-clear="true"
                v-model:value="formFilter.search"
            >
                <!-- Sử dụng slot suffix để đặt icon -->
                <template #suffix>
                <SearchOutlined @click="onSearch" style="cursor: pointer" />
                </template>
            </a-input>
            <a-button @click="removeFilter" size="large">Xóa lọc </a-button>
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
                <div class="flex gap-2">
                    {{ record.name }}
                </div>
              </template>
              <template v-if="column.key === 'status_label'">
                <div class="flex gap-2">
                    <span :class="statusColor(record.status)">{{ record.status_label }}</span>
                </div>
              </template>
              <template v-else-if="column.key === 'action'">
                <div class="flex gap-2">
                    <a :href="route('admins.event.ranking', {id: record.id})">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-bar-chart" viewBox="0 0 16 16">
                            <path d="M4 11H2v3h2zm5-4H7v7h2zm5-5v12h-2V2zm-2-1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM6 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1zm-5 4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1z"/>
                        </svg>
                    </a>
                    <a :href="route('admins.event.edit', { id: record.id })"><img class="delete-icon cursor-pointer" width="30px" height="30px" src="/images/icon-game-choose-correct/icon-edit.svg" alt="" /></a>
                    <a-popconfirm v-if="record.status === 0" placement="topRight" ok-text="Kích hoạt" cancel-text="Bỏ qua" @confirm="activate(record)">
                        <template #title>
                            <p>Kích hoạt sự kiện này?</p>
                        </template>
                        <a-tooltip title="Kích hoạt sự kiện">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" class="cursor-pointer text-blue-500" style="color:#3b82f6">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                <path d="M10 8l6 4-6 4V8z" fill="currentColor"/>
                            </svg>
                        </a-tooltip>
                    </a-popconfirm>
                    <a-tooltip v-if="!record.can_delete" title="Không thể xóa sự kiện đang diễn ra">
                        <img class="delete-icon opacity-30 cursor-not-allowed" src="/images/icon-game-choose-correct/icon-delete.png" alt="" />
                    </a-tooltip>
                    <a-popconfirm v-else placement="topRight" ok-text="Xóa" cancel-text="Bỏ qua" @confirm="confirm(record)" class="text-red-600 cursor-pointer">
                        <template #title>
                            <p>Bạn có chắc chắn muốn xoá sự kiện này?</p>
                        </template>
                        <img class="delete-icon cursor-pointer" src="/images/icon-game-choose-correct/icon-delete.png" alt="" />
                    </a-popconfirm>
                </div>
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

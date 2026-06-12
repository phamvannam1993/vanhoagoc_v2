<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { ref, onMounted, nextTick } from 'vue';
import {SearchOutlined} from '@ant-design/icons-vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

const toast = useToast();
const page = usePage();
const query = page.props.query;
const app_id = query.appId;
const columns = [
  {
    title: 'ID',
    dataIndex: 'id',
    key: 'id',
  },
  {
    title: 'Mã kích hoạt',
    dataIndex: 'code',
    key: 'code',
  },
  {
    title: 'Giá',
    dataIndex: 'price',
    key: 'code',
  },
  {
    title: 'Trạng thái',
    dataIndex: 'status',
    key: 'status',
  },
  {
    title: 'Nguồn',
    dataIndex: 'source',
    key: 'source',
  },
  {
    title: 'Thời gian tạo',
    dataIndex: 'created_at',
    key: 'created_at',
  }
];

const pagination = ref({
  current: 1,
  pageSize: 20,
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

const exportData = () => {
  const params = {
    page: pagination.value.current,
    search: formFilter.value.search,
    pageSize: pagination.value.pageSize,
  };
  const url = route('admins.active-codes.json.exportData', params);
  window.open(url, '_blank');
}

const loadData = async () => {
  const params = {
    page: pagination.value.current,
    search: formFilter.value.search,
    pageSize: pagination.value.pageSize,
  };
  const res = await axios.get(route('admins.active-codes.json.list', params));

  if (res.data.status) {
    data.value = res.data.data.data.map((v) => {
      return {
        id: v.id,
        code: v.code,
        price:v.price,
        source:v.source,
        created_at:v.created_at,
        is_used: v.is_used,
      };
    });

    pagination.value.pageSize = res.data.data.per_page;
    pagination.value.total = res.data.data.total;
    pagination.value.current = res.data.data.current_page;
  }
};

const listRecordDelete = ref([]);
const showDelete = ref(false)
const rowSelection = ref({
    onChange: (selectedRowKeys, selectedRows) => {
        listRecordDelete.value = selectedRows;
        if(listRecordDelete.value.length > 0) {
            showDelete.value = true
        } else {
            showDelete.value = false
        }
    },
    onSelect: (record, selected, selectedRows) => {

    },
    onSelectAll: (selected, selectedRows, changeRows) => {
    },
});
const onSearch = () => {
    loadData();
};
const confirmDeleteMulti = () => {
    const idsToDelete = listRecordDelete.value.map(item => item.id);
    axios.delete(route('admins.active-codes.json.deleteMultiple'), {
        data: { ids: idsToDelete }
    })
    .then(response => {
        if (response.status === 200) {
            toast.success('Xóa tài khoản Hiệu trưởng/Giám đốc thành công');
            setTimeout(function () {
                location.href = route('admins.active-codes.index')
            }, 500);
        }
    })
    .catch(error => {
        toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
}

const onPageChange = (page) => {
  pagination.value.current = page;
  loadData();
};
const listPageSize = ref([
    {
        value: 20,
        label: '20/page'
    },
    {
        value: 50,
        label: '50/page'
    },
    {
        value: 100,
        label: '100/page'
    },
    {
        value: 200,
        label: '200/page'
    },
]);
const handleChangePageSize = () => {
    loadData();
}
</script>

<template>
  <Head title="Active code" />

  <SchoolLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Active code
      </h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-10/12">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Mã kích hoạt</h1>
        <div class="relative mt-6 flex gap-4">
            <Link
                :href="route('admins.active-codes.create')"
            >
                <a-button  type="primary">Thêm mới</a-button>
            </Link>
            <a-button type="primary" @click="exportData">Xuất Excel</a-button>
            <img v-if="showDelete" src="/images/delete.svg" @click="confirmDeleteMulti()" class="cursor-pointer">
        </div>
        <div class="mt-6 flex gap-4">
            <a-input class="md:w-[300px]"
                placeholder="Nhập tên tìm kiếm"
                :allow-clear="true"
                v-model:value="formFilter.search"
            >
                <template #suffix>
                    <SearchOutlined @click="onSearch" style="cursor: pointer"/>
                </template>
            </a-input>
        </div>
        <div class="mt-4">
          <a-table
        :columns="columns"
                        :data-source="data"
                        :pagination="false"
                        ref="tableRef"
                        rowKey="id"
                        :row-selection="rowSelection"
                        :scroll="{ x: 'max-content' }"
          >
            <template #bodyCell="{ column, record, index }">
              <template v-if="column.key === 'id'">
                <div class="flex gap-2">
                  {{ record.id }}
                </div>
              </template>
              <template v-if="column.key === 'status'">
                <div class="flex gap-2">
                 <span v-if="record.is_used === 0" class="">Chưa kích hoạt</span>
                  <span v-else class="color-blue">Đã kích hoạt</span>
                </div>
              </template>
            </template>
            <template #footer>
              <div class="flex items-center justify-end">
                  <!-- Pagination -->
                  <a-pagination
                      v-bind="pagination"
                      @change="onPageChange"
                        :show-size-changer="false"
                  />
                    <a-select
                      ref="select"
                      v-model:value="pagination.pageSize"
                      @focus="focus"
                      @change="handleChangePageSize"
                  >
                      <a-select-option v-for="(item, index) in listPageSize" :value="item.value">{{ item.label }}</a-select-option>
                  </a-select>
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
.gray-btn {
  background-color: #d9d9d9;
  border: 1px solid #d9d9d9;
}
.ant-btn {
    font-size: 14px;
    height: 32px;
    padding: 4px 15px;
    border-radius: 6px;
}
.ant-btn-primary {
    color: #fff;
    background-color: #1677ff;
    box-shadow: 0 2px 0 rgba(5, 145, 255, 0.1);
}
.color-white {
    color:#ffffff !important;
}
.color-blue {
  color:#1677ff !important;
}
</style>

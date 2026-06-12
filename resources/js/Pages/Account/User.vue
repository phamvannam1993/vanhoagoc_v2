<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { ref, onMounted, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

const props = defineProps({
    listApp: {
        type: Array,
    },
});
const toast = useToast();
const page = usePage();
const query = page.props.query;
const type_id = parseInt(query.type_id);
const columns = [
  {
    title: 'STT',
    dataIndex: 'stt',
    key: 'stt',
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
    title: 'Email',
    dataIndex: 'email',
    key: 'email',
  },
  {
    title: 'Số điện thoại',
    key: 'phone',
    dataIndex: 'phone',
  },
  {
    title: 'Thời gian tạo',
    key: 'created_at',
    dataIndex: 'created_at',
  },
  {
    title: '',
    key: 'action',
  },
  {
    title: 'ID',
    key: 'id',
    dataIndex: 'id',
  },
];
const appOptions = props.listApp.map((v) => {
    return { value: v.id, label: v.name };
});

const pagination = ref({
  current: 1,
  pageSize: 1,
  total: 0,
});
const data = ref([]);
const formFilter = ref({
  app_id: null,
});
const tableRef = ref(null);
onMounted(async () => {
  await loadData();
});
const loadData = async () => {
  const params = {
    app_id: formFilter.value.app_id ? 'p_' + formFilter.value.app_id : ''
  };
  const res = await axios.get(route('users.json.jsonListUser', params));

  if (res.data.status) {
    data.value = res.data.data.data.map((v) => {
      return {
        id: v.id,
        name: v.name,
        email: v.email,
        phone: v.tel,
        created_at: v.created_at,
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
const confirm = (value) => {
  axios
    .delete(route('users.json.deleteMember', { id: value }))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Xóa user thành công');
        loadData();
      }
    })
    .catch(() => {
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
};
const goBack = () => {
  window.location = route('users.index');
};
const filterOption = (input, option) => {
    return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};
const handleChangeApp = (value) => {
    formFilter.value.app_id = value;
    loadData();
};
const removeFilter = () => {
    formFilter.value.app_id = null;
    loadData();
};
</script>

<template>
  <Head title="Users" />

  <SchoolLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Users</h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-10/12">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">
          Danh sách người dùng
        </h1>
        <a-button @click="goBack" class="custom-bg text-black" size="large">
          Quay lại
        </a-button>
          <div class="filter-page w-1/3 mt-4 flex gap-10">
              <a-select
                  class="input-search"
                  v-model:value="formFilter.app_id"
                  show-search
                  placeholder="Tất cả khóa học"
                  style="width: 95rem"
                  size="large"
                  :options="appOptions"
                  :filter-option="filterOption"
                  @change="handleChangeApp"
              ></a-select>
              <a-button @click="removeFilter" size="large">Xóa lọc </a-button>
          </div>
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
              <template v-if="column.key === 'stt'">
                {{ index + 1 + pagination.pageSize * (pagination.current - 1) }}
              </template>
              <template v-if="column.key === 'name'">
                <div class="flex gap-2">
                  <p :class="`flex items-center font-bold`">
                    {{ record.name }}
                  </p>
                </div>
              </template>
              <template v-if="column.key === 'email'">
                <div class="flex gap-2">
                  <p :class="`flex items-center font-bold`">
                    {{ record.email }}
                  </p>
                </div>
              </template>
              <template v-if="column.key === 'phone'">
                <div class="flex gap-2">
                  <p :class="`flex items-center font-bold`">
                    {{ record.phone }}
                  </p>
                </div>
              </template>
              <template v-if="column.key === 'created_at'">
                <div class="flex gap-2">
                  <p :class="`flex items-center font-bold`">
                    {{ record.created_at }}
                  </p>
                </div>
              </template>
              <template v-if="column.key === 'action'">
                <div class="flex items-center gap-12">
                  <Link
                    href="#"
                  >
                    <a-button :class="`text-white`" size="large" type="primary">
                      Chi tiết
                    </a-button>
                  </Link>
                  <div :class="`cursor-pointer text-[15px] font-semibold`">
                    <Link
                      :href="
                        route('users.editMember', {
                          user_id: record.id,
                        })
                      "
                      >Sửa
                    </Link>
                  </div>
                  <div :class="`cursor-pointer text-[15px] font-semibold`">
                    <a-popconfirm
                      placement="topRight"
                      ok-text="Xóa"
                      cancel-text="Bỏ qua"
                      @confirm="confirm(record.id)"
                    >
                      <template #title>
                        <p>Bạn có chắc chắn muốn xoá user này?</p>
                      </template>
                      Xóa
                    </a-popconfirm>
                  </div>
                </div>
              </template>
            </template>
            <template #footer>
              <div class="flex items-center justify-end">
                <!-- Pagination -->
                <a-pagination v-bind="pagination" @change="onPageChange" />
                <!-- Icon plus -->
                <Link :href="route('users.newMember', { type_id: type_id })">
                  <img
                    class="cursor-pointer"
                    src="/images/icon-plus.png"
                    alt="icon-plus"
                  />
                </Link>
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

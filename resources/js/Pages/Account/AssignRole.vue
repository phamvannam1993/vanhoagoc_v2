<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import MasterLayout from '@/Layouts/MasterLayout.vue';
import { ref, onMounted, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

const toast = useToast();
const page = usePage();
const query = page.props.query;
const type_id = parseInt(query.type_id);
const user_id = parseInt(query.user_id);
const columns = [
  {
    title: 'STT',
    dataIndex: 'stt',
    key: 'stt',
    width: 80,
    align: 'center',
  },
  {
    title: 'Tên app',
    dataIndex: 'name',
    key: 'name',
  },
  {
    title: 'Cấp quyền sử dụng',
    key: 'action',
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
onMounted(async () => {
  await loadData();
});
const loadData = async () => {
  const params = {
    page: pagination.value.current,
    user_id: user_id,
  };
  const res = await axios.get(route('users.json.jsonListApp', params));

  if (res.data.status) {
    data.value = res.data.data.data.map((v) => {
      return {
        id: v.id,
        name: v.name,
        checked: v.assigned ? true : false,
        url: v.url,
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
const confirm = (value, checked) => {
  axios
    .post(
      route('users.json.assignRole', {
        app_id: value,
        user_id: user_id,
        type_id: type_id,
        checked: checked,
      }),
    )
    .then((response) => {
      if (response.status === 200) {
        toast.success('Cấp quyền app thành công');
      }
    })
    .catch(() => {
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
};
const goBack = () => {
  window.location = route('users.member', { type_id: type_id });
};
</script>

<template>
  <Head title="Assign role" />

  <MasterLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Assign Role
      </h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-10/12">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Cấp quyền sử dụng</h1>
        <a-button
          @click="goBack"
          class="custom-bg mt-4 text-black"
          size="large"
        >
          Quay lại
        </a-button>
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
              <template v-if="column.key === 'action'">
                <div class="flex items-center gap-12">
                  <a-switch
                    @click="confirm(record.id, record.checked)"
                    v-model:checked="record.checked"
                  />
                </div>
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
  </MasterLayout>
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

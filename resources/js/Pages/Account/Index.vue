<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { ref, onMounted, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

const toast = useToast();
const page = usePage();
const query = page.props.query;
const app_id = query.appId;
const columns = [
  {
    title: 'Tên',
    dataIndex: 'name',
    key: 'name',
  },
  {
    title: 'Danh sách',
    dataIndex: 'list',
    key: 'list',
  },
  {
    title: 'ID',
    key: 'id',
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
  const res = await axios.get(route('users.json.list', params));

  if (res.data.status) {
    data.value = res.data.data.data.map((v) => {
      return {
        id: v.id,
        type_id: v.type,
        name: v.name,
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
  <Head title="Accounts" />

  <SchoolLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Accounts
      </h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-10/12">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Loại thành viên</h1>
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
              <template v-if="column.key === 'name'">
                <div class="flex gap-2">
                  {{ record.name }}
                </div>
              </template>
              <template v-if="column.key === 'list'">
                <Link :href="route('users.member', { type_id: record.id })">
                  <a-button :class="`text-white`" size="large" type="primary"
                    >Thành viên
                  </a-button>
                </Link>
              </template>
              <template v-if="column.key === 'id'">
                <div class="flex gap-2">
                  {{ record.id }}
                </div>
              </template>
            </template>
            <template #footer>
              <div class="flex items-center justify-end">
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

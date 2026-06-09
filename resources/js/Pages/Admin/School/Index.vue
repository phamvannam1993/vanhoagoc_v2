<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { SearchOutlined } from '@ant-design/icons-vue';
import { ref, onMounted, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import Sortable from 'sortablejs';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";

const toast = useToast();
const page = usePage();
const user = page.props.auth.user;
const showAdminMenu = !!user.user_type_id && user.user_type_id == 2;
const query = page.props.query;
const app_id = query.appId;
const columns = [
      {
        title: 'STT',
        dataIndex: 'id',
        key: 'id',
        width: 80,
        align: 'center',
      },
      {
        title: 'Tên đơn vị/Trường học',
        dataIndex: 'name',
        key: 'name',
        width: '30%',
      },
      {
        title: 'Danh sách Phòng ban/Lớp',
        dataIndex: 'menu',
        key: 'menu',
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
  const res = await axios.get(route('admins.school.json.list', params));

  if (res.data.status) {
    data.value = res.data.data.data.map((v) => {
      return {
        id: v.id,
        name: v.name,
        isClass: v.isClass,
        hasResult: v.has_result,
        menu: 'Danh sách phòng ban/Lớp',
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
const onSearch = () => {
  loadData();
};
const removeFilter = () => {
  formFilter.value.search = '';
  loadData();
};
</script>

<template>
  <Head title="App" />

  <SchoolLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">App</h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-10/12">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Danh sách Đơn vị/Trường học</h1>
        <div class="filter-page mt-4 flex gap-5">
          <a-input
            placeholder="Tìm kiếm"
            :allow-clear="true"
            style="width: 30rem"
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
                  <p
                    :class="`flex items-center font-bold ${record.status === 'off' ? 'hide-class' : ''}`"
                  >
                    {{ record.name }}
                  </p>
                </div>
              </template>
              <template v-if="column.key === 'menu'">
                <Link
                  :href="
                    route('admins.class.create', {
                      app_id: record.id
                    })
                  "
                  v-if="!record.isClass"
                >
                  <a-button
                    :class="`bg-[#b1b1b1] text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                    size="large"
                    >{{ record.menu }}
                  </a-button>
                </Link>
                <Link
                  :href="
                    route('admins.class.index', {
                      app_id: record.id
                    })
                  "
                  v-else
                >
                  <a-button
                    :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                    size="large"
                    type="primary"
                    >{{ record.menu }}
                  </a-button>
                </Link>
              </template>
              <template v-if="column.key === 'result'">
                  <Link v-if="!record.hasResult">
                      <a-button
                          :class="`bg-[#b1b1b1] text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                          size="large"
                          disabled
                      >
                          Kết quả
                      </a-button>
                  </Link>
                  <Link :href="route('admins.points.getResultSchool', { app_id: record.id })" v-else>
                      <a-button
                          :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                          size="large"
                          type="primary"
                      >Kết quả
                      </a-button>
                  </Link>
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

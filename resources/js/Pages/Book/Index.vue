<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import MasterLayout from '@/Layouts/MasterLayout.vue';
import { SearchOutlined } from '@ant-design/icons-vue';
import {ref, onMounted, nextTick} from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import Sortable from 'sortablejs';

const props = defineProps({
  listCourse: {
    type: Array,
  },
  appId: {
    type: String,
  },
  appList: {
    type: Array,
  },
});
const page = usePage();
const query = page.props.query;
const app_id = query.appId;
const appId = ref(parseInt(app_id));
const appOptions = props.appList.map((v) => {
  return { value: v.id, label: v.name };
});

const currentApp = props.appList.filter((v) => {
    if (v.id == appId.value) {
        return { value: v.id, label: v.name };
    }
});

const bookOptions = [
  {
    value: 'canhdieu',
    label: 'Cánh diều',
  },
  {
    value: 'kntt',
    label: 'Kết nối tri thức',
  },
];
const columns = [
  {
    title: 'STT',
    dataIndex: 'id',
    key: 'id',
    width: 80,
    align: 'center',
  },
  {
    title: 'Môn học/ Khoá học',
    dataIndex: 'name',
    key: 'name',
    width: '40%',
  },
  {
    title: 'Số lượng',
    dataIndex: 'count',
    key: 'count',
    width: '10%',
  },
  {
    title: '',
    key: 'action',
  },
];
const toast = useToast();
const pagination = ref({
  current: 1,
  pageSize: 1,
  total: 0,
});
const data = ref([]);
const formFilter = ref({
  search: '',
  app_id: appId.value,
  bo_sach: null,
});
const tableRef = ref(null);

const breadcrumbs = [{'title': 'App', 'url': route('apps.dashboard')}, {'title': currentApp[0].name, 'url': ''}]
onMounted(async () => {
    await loadData();
    setupSortable();
});
const setupSortable = () => {
    nextTick(() => {
        const tbody = tableRef.value?.$el.querySelector('tbody');
        if (tbody) {
            Sortable.create(tbody, {
                animation: 150,
                onEnd: (evt) => {
                    let from = evt.oldIndex;
                    let to = evt.newIndex;
                    if (from === to || from === undefined || to === undefined) return;
                    //Nếu table có scroll thì phải giảm index đi 1
                    //from -= 1
                    //to -= 1
                    const updated = [...data.value];
                    const fromId = updated[from].id
                    const toId = updated[to].id
                    const movedItem = updated.splice(from, 1)[0];
                    updated.splice(to, 0, movedItem);
                    data.value = updated;
                    const params = {
                        from_id: fromId,
                        to_id: toId
                    };
                    const res = axios.patch(route('books.json.updatePosition', params))
                },
            });
        }
    });
};
const loadData = async () => {
  const params = {
    page: pagination.value.current,
    search: formFilter.value.search,
    app_id: formFilter.value.app_id,
    bo_sach: formFilter.value.bo_sach,
  };
  const res = await axios.get(route('books.json.list', params));

  if (res.data.status) {
    data.value = res.data.data.data.map((v) => {
      return {
        id: v.id,
        name: v.name,
        title: v.title,
        app_id: v.app_id,
        status: v.status,
        img: v.img,
        has_weeks: v.has_weeks,
        count_week: v.count_week,
        access_permission: v.access_permission,
        menu: 'Danh sách tuần',
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

const handleChangeCourse = (value) => {
  formFilter.value.app_id = value;
  appId.value = value;
  loadData();
};
const handleChangeBook = (value) => {
  formFilter.value.bo_sach = value;
  loadData();
};
const filterOption = (input, option) => {
  return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};
const onSearch = () => {
  loadData();
};
const removeFilter = () => {
  formFilter.value.search = '';
  formFilter.value.app_id = null;
  formFilter.value.bo_sach = null;
  loadData();
};
const confirm = (value) => {
  axios
    .delete(route('books.json.delete', value))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Xóa môn học/ khóa học thành công');
        loadData();
      }
    })
    .catch(() => {
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
};
const hideApp = (id) => {
  // todo update status
  axios
    .post(route('books.json.visible', { id: id, status: 'off' }))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Cập nhật status sách thành công');
        setTimeout(function () {
          location.href = '/books?appId=' + appId.value; // URL cần chuyển hướng
        }, 500);
      }
    })
    .catch(() => {
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
};
const showApp = (id) => {
  // todo update status
  axios
    .post(route('books.json.visible', { id: id, status: 'on' }))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Cập nhật status sách thành công');
        setTimeout(function () {
          location.href = '/books?appId=' + appId.value; // URL cần chuyển hướng
        }, 500);
      }
    })
    .catch(() => {
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
};
const showOption = ref(false)
const toggleOption = () => {
  showOption.value = !showOption.value
}
const isCopy = ref(false)
const copyBook = async (to_app_id) => {
  if(listRecordSelect.value.length == 0) {
     toast.error('Vui lòng chọn sách');
     return false
  }
  if(isCopy.value) {
    return false
  }
  isCopy.value = true
   axios
    .post(route('books.json.copyData', { to_app_id: to_app_id, book_ids: listRecordSelect.value.map(item => item.id) }))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Sao chép sách thành công');
        showOption.value = false
      }
    })
    .catch(() => {
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
       isCopy.value = false
    });
}

const listRecordSelect = ref([]);
const rowSelection = ref({
    onChange: (selectedRowKeys, selectedRows) => {
        listRecordSelect.value = selectedRows;
    },
    onSelect: (record, selected, selectedRows) => {

    },
    onSelectAll: (selected, selectedRows, changeRows) => {
    },
});
const goBack = () => {
  window.location = route('apps.dashboard');
};
</script>

<template>
  <Head title="Books" />

  <MasterLayout :breadcrumbs=breadcrumbs>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Courses</h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-10/12">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">
          Danh sách môn học/ khóa học
        </h1>
        <div class="relative mt-6 flex gap-4">
          <a-button @click="goBack" class="custom-bg text-black" size="large">
            Quay lại
          </a-button>
          <Link :href="route('books.create', { appId: appId })">
            <a-button type="primary" size="large">Thêm mới</a-button>
          </Link>
            <div class="relative">
                <img :src="showOption ? '/images/copy.svg' : '/images/copy_hide.svg'" @click="toggleOption()" class="cursor-pointer">
                <div class="jump-box" v-if="showOption">
                    <span class="jump-title">Chuyển đến:</span>
                    <ul>
                    <li v-for="(item, index) in appList" :key="index" @click="copyBook(item.id)">
                        {{ item.name }}
                    </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="filter-page mt-4 flex flex-col md:flex-row gap-5">
          <a-select
            class="input-search md:w-[300px]"
            v-model:value="formFilter.app_id"
            show-search
            placeholder="Tất cả khóa học"
            size="large"
            :options="appOptions"
            :filter-option="filterOption"
            @change="handleChangeCourse"
          ></a-select>
          <a-select
            class="md:w-[300px]"
            v-model:value="formFilter.bo_sach"
            show-search
            placeholder="Tất cả các bộ sách"
            size="large"
            :options="bookOptions"
            :filter-option="filterOption"
            @change="handleChangeBook"
          ></a-select>
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
          <a-button @click="removeFilter" size="large">Xóa lọc</a-button>
        </div>
        <div class="mt-4">
          <a-table :columns="columns" :data-source="data" :pagination="false"  ref="tableRef" rowKey="id" :row-selection="rowSelection">
            <template #bodyCell="{ column, record, index }">
              <template v-if="column.key === 'id'">
                {{ index + 1 + pagination.pageSize * (pagination.current - 1) }}
              </template>
              <template v-if="column.key === 'name'">
                <div>
                    <Link :href="route('weeks.index', {
                        book_id: record?.id,
                        app_id: appId,
                        })" class="flex gap-2">
                    <a
                        :class="`flex items-center font-bold ${record.status === 'off' ? 'hide-class' : ''}`"
                    >

                            {{ record.title }}
                    </a>
                    </Link>
                </div>
              </template>
              <template v-if="column.key === 'count'">
                <span
                  v-if="record.count_week > 0"
                  :class="`text-[16px] font-semibold text-black ${record.status === 'off' ? 'hide-class' : ''}`"
                  >{{ record.count_week }} tuần</span
                >
              </template>
              <template v-if="column.key === 'menu'">
                <Link
                  v-if="!record.has_weeks"
                  :href="
                    route('weeks.create', {
                      book_id: record?.id,
                      app_id: props.appId,
                    })
                  "
                >
                  <a-button
                    :class="`bg-[#b1b1b1] text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                    size="large"
                  >
                    {{ record.menu }}
                  </a-button>
                </Link>
                <Link
                  v-else
                  :href="
                    route('weeks.index', {
                      book_id: record?.id,
                      app_id: appId,
                    })
                  "
                  ><a-button
                    :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                    size="large"
                    type="primary"
                  >
                    {{ record.menu }}
                  </a-button>
                </Link>
              </template>
              <template v-else-if="column.key === 'action'">
                <template v-if="record.access_permission == 1">
                  <img
                    v-if="record.status === 'off'"
                    @click="showApp(record.id)"
                    :class="`h-[24px] btn-view cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                    src="/images/icon-eye-closed.png"
                    alt="eye-closed"
                  />
                  <img
                    v-else
                    @click="hideApp(record.id)"
                    :class="`h-[24px] btn-view cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                    src="/images/icon-eye-open.png"
                    alt="eys-open"
                  />
                         <a-dropdown trigger="click">
                    <a class="ant-dropdown-link" @click.prevent>
                      <img src="/images/icon-dots.svg" class="h-[30px] cursor-pointer" alt="menu" />
                    </a>
                    <template #overlay>
                      <a-menu>
                        <a-menu-item>
                          <Link  :href="
                        route('books.edit', {
                          app_id: appId,
                          id: record.id,
                        })">Sửa</Link>
                        </a-menu-item>
                        <a-menu-item danger>
                          <a-popconfirm
                            placement="topRight"
                            ok-text="Xóa"
                            cancel-text="Bỏ qua"
                            @confirm="confirm(record.id)"
                          >
                            <template #title>
                              <p>Bạn có chắc chắn muốn xoá môn học/ khóa học này?</p>
                            </template>
                            Xóa
                          </a-popconfirm>
                        </a-menu-item>
                      </a-menu>

                    </template>
                  </a-dropdown>
                </template>
              </template>
            </template>
            <template #footer>
              <div class="flex items-center justify-end">
                <!-- Pagination -->
                <a-pagination v-bind="pagination" @change="onPageChange" />
                <!-- Icon plus -->
                <Link
                  :href="
                    route('books.create', {
                      appId: appId,
                    })
                  "
                >
                  <img src="/images/icon-plus.png" alt="icon-plus" />
                </Link>
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

.jump-box {
    background: #fff;
    border-radius: 10px;
    padding: 10px 14px;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.15);
    display: inline-block;
    font-family: sans-serif;
    position: absolute;
    z-index: 999;
    margin-left: 0px;
    width: max-content;
}

.jump-title {
    color: #007bff;
    font-weight: bold;
    display: block;
    margin-bottom: 6px;
}

.jump-box ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.jump-box ul li {
    padding: 4px 0;
    cursor: pointer;
    transition: color 0.2s;
}

.jump-box ul li:hover {
    color: #007bff;
}
</style>

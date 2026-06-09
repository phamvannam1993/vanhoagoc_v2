<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import MasterLayout from '@/Layouts/MasterLayout.vue';
import { SearchOutlined } from '@ant-design/icons-vue';
import { ref, onMounted, nextTick } from "vue";
import { Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { BASE_URL } from '@/config.js';
import Sortable from "sortablejs";

const props = defineProps({
  bookList: {
    type: Array,
  },
  book: {
    type: Object,
  }
});
const page = usePage();
const query = page.props.query;
const book_id = query.book_id;
const app_id = query.app_id;
const bookId = ref(parseInt(book_id))
const showOption = ref(false)
const bookOptions = props.bookList.map((v) => {
  return { value: v.id, label: v.title };
});

const breadcrumbs = [
    {'title': 'App', 'url': route('apps.dashboard')},
    {'title': props.book.app.name, 'url': route('books.index', {appId: props.book.app.id})},
    {'title': props.book.title, 'url': ''},
]
const columns = [
  {
    title: 'STT',
    dataIndex: 'id',
    key: 'id',
    width: 80,
    align: 'center',
  },
  {
    title: 'Tiêu đề',
    dataIndex: 'name',
    key: 'name',
    width: '40%',
  },
  {
    title: 'Số lượng',
    dataIndex: 'count',
    key: 'count',
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

const onKeyDown = (event) => {
    // Chặn tất cả các phím nhập liệu vào input
    if (event.key.length === 1) {
        event.preventDefault();
    }
}
const formFilter = ref({
  search: '',
  book_id: bookId.value,
});
const tableRef = ref(null);
onMounted(async () => {
    await loadData();
    setupSortable();
});
const loadData = async () => {
  const params = {
    page: pagination.value.current,
    search: formFilter.value.search,
    book_id: formFilter.value.book_id,
  };
  const res = await axios.get(route('weeks.json.list', params));

  if (res.data.status) {
    data.value = res.data.data.data.map((v) => {
      return {
        id: v.id,
        name: v.name,
        book_id: v.book_id,
        img: v.img,
        menu: 'Danh sách bài',
        status: v.status ? v.status : 'on',
        count_practice: v.count_practice,
        has_practices: v.has_practices,
      };
    });

    pagination.value.pageSize = res.data.data.per_page;
    pagination.value.total = res.data.data.total;
    pagination.value.current = res.data.data.current_page;
  }
};
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
                    // from -= 1
                    // to -= 1
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
                    const res = axios.patch(route('weeks.json.updatePosition', params))
                },
            });
        }
    });
};
const onPageChange = (page) => {
  pagination.value.current = page;
  loadData();
};

const handleChangeBook = (value) => {
  formFilter.value.book_id = value;
  bookId.value = value;
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
  formFilter.value.book_id = null;
  loadData();
};
const confirm = (value) => {
  axios
    .delete(route('weeks.json.delete', value))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Xóa tuần thành công');
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
    .post(route('weeks.json.visible', { id: id, status: 'off' }))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Cập nhật status tuần thành công');
        setTimeout(function () {
          location.href = route('weeks.index', {
            app_id: app_id,
            book_id: bookId.value,
          });
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
    .post(route('weeks.json.visible', { id: id, status: 'on' }))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Cập nhật status tuần thành công');
        setTimeout(function () {
          location.href = route('weeks.index', {
            app_id: app_id,
            book_id: bookId.value,
          });
        }, 500);
      }
    })
    .catch(() => {
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
};
// Thay đổi class của dòng theo logic
const rowClassName = (record, index) => {
  if (record.status === 'off') {
    return 'grey-row';
  }
  return '';
};
const goBack = () => {
  window.location = route('books.index', { appId: app_id });
};
const listRecordDelete = ref([]);
const rowSelection = ref({
    onChange: (selectedRowKeys, selectedRows) => {
        listRecordDelete.value = selectedRows;
    },
    onSelect: (record, selected, selectedRows) => {

    },
    onSelectAll: (selected, selectedRows, changeRows) => {
    },
});
const toggleOption = () => {
  showOption.value = !showOption.value
}
const isCopy = ref(false)
const isDelete = ref(false)
const copyWeek = async (book_id) => {
  if(listRecordDelete.value.length == 0) {
     toast.error('Vui lòng chọn tuần');
     return false
  }
  if(isCopy.value) {
    return false
  }
  isCopy.value = true
   axios
    .post(route('weeks.json.copyData', { book_id: book_id, week_ids: listRecordDelete.value, app_id:app_id }))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Sao chép tuần thành công');
        showOption.value = false
        setTimeout(function () {
           location.href = ''
        }, 500);
      }
    })
    .catch(() => {
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
       isCopy.value = false
    });
}

const deleteMany = async () => {
  if(listRecordDelete.value.length == 0) {
     toast.error('Vui lòng chọn tuần');
     return false
  }
  if(isDelete.value) {
    return false
  }
   axios
    .post(route('weeks.json.deleteMany', { week_ids: listRecordDelete.value }))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Xóa tuần thành công');
         setTimeout(function () {
          location.href = ''
        }, 500);
      }
    })
    .catch(() => {
      isDelete.value = false
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
}

</script>

<template>
  <Head title="Weeks" />

  <MasterLayout :breadcrumbs=breadcrumbs>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Weeks</h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-10/12">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Danh sách tuần</h1>
        <div class="relative mt-6 flex gap-4">
          <a-button @click="goBack" class="custom-bg text-black" size="large">
            Quay lại
          </a-button>
          <Link
            :href="
              route('weeks.create', {
                book_id: bookId,
                app_id: app_id,
              })
            "
          >
            <a-button type="primary" size="large">Thêm mới</a-button>
          </Link>
          <img src="/images/delete.svg" @click="deleteMany()" class="cursor-pointer">
          <img :src="showOption ? '/images/copy.svg' : '/images/copy_hide.svg'" @click="toggleOption()" class="cursor-pointer">
          <div class="jump-box" v-if="showOption">
            <span class="jump-title">Chuyển đến:</span>
            <ul>
             <li v-for="(item, index) in bookOptions" :key="index" @click="copyWeek(item.value)">
                {{ item.label }}
              </li>
            </ul>
          </div>
        </div>

        <div class="filter-page mt-4 flex gap-5 flex-col md:flex-row">
          <a-select
            class="input-search md:w-[300px]"
            v-model:value="formFilter.book_id"
            show-search
            placeholder="Tất cả khóa học"
            size="large"
            :options="bookOptions"
            :filter-option="filterOption"
            @change="handleChangeBook"
            @keydown="onKeyDown"
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
          <a-table :columns="columns" :data-source="data" :pagination="false" ref="tableRef" rowKey="id" :row-selection="rowSelection">
            <template #bodyCell="{ column, record, index }">
              <template v-if="column.key === 'id'">
                {{ index + 1 + pagination.pageSize * (pagination.current - 1) }}
              </template>
              <template v-if="column.key === 'name'">
                <div>
                    <Link :href=" route('lessons.index', {
                        book_id: bookId,
                        week_id: record.id,
                        app_id: app_id,
                        })" class="flex gap-2">
                        <img
                            :class="`h-[58px] w-[60px] ${record.status === 'off' ? 'hide-class' : ''}`"
                            v-if="record.img"
                            :src="record.img"
                            alt=""
                        />
                        <img
                            :class="`h-[58px] w-[60px] ${record.status === 'off' ? 'hide-class' : ''}`"
                            v-else
                            src="/images/image_book.png"
                            alt=""
                        />
                        <a
                            :class="`flex items-center font-bold ${record.status === 'off' ? 'hide-class' : ''}`"
                        >
                                {{ record.name }}
                        </a>
                    </Link>
                </div>
              </template>
              <template v-if="column.key === 'count'">
                <div
                  v-if="record.count_practice > 0"
                  :class="`flex gap-2 ${record.status === 'off' ? 'hide-class' : ''}`"
                >
                  {{ record?.count_practice }} bài
                </div>
              </template>
              <template v-if="column.key === 'menu'">
                <Link
                  v-if="!record.has_practices"
                  :href="
                    route('lessons.create', {
                      book_id: book_id,
                      week_id: record.id,
                      app_id: app_id,
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
                    route('lessons.index', {
                      book_id: bookId,
                      week_id: record.id,
                      app_id: app_id,
                    })
                  "
                  ><a-button
                    :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                    size="large"
                    type="primary"
                  >
                    {{ record.menu }}
                  </a-button></Link
                >
              </template>
              <template v-else-if="column.key === 'action'">
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
                          <Link  
                          :href="
                            route('weeks.edit', {
                              id: record.id,
                              book_id: bookId,
                              app_id: app_id,
                            })
                        ">Sửa</Link>
                        </a-menu-item>
                        <a-menu-item danger>
                          <a-popconfirm
                            placement="topRight"
                            ok-text="Xóa"
                            cancel-text="Bỏ qua"
                            @confirm="confirm(record.id)"
                          >
                            <template #title>
                              <p>Bạn có chắc chắn muốn xoá tuần này?</p>
                            </template>
                            Xóa
                          </a-popconfirm>
                        </a-menu-item>
                      </a-menu>
                    
                    </template>
                  </a-dropdown>
              </template>
            </template>
            <template #footer>
              <div v-if="data.length > 0" class="flex items-center justify-end">
                <!-- Pagination -->
                <a-pagination v-bind="pagination" @change="onPageChange" />
                <!-- Icon plus -->
                <Link
                  :href="
                    route('weeks.create', {
                      book_id: bookId,
                      app_id: app_id,
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
  margin-left: 310px;
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

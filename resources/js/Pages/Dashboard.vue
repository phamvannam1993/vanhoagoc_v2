<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import MasterLayout from '@/Layouts/MasterLayout.vue';
import { SearchOutlined } from '@ant-design/icons-vue';
import { ref, onMounted, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import Sortable from 'sortablejs';

const toast = useToast();
const page = usePage();
const user = page.props.auth.user;
const showAdminMenu = !!user.user_type_id && user.user_type_id == 2;
const query = page.props.query;
const app_id = query.appId;
const columns = showAdminMenu
  ? [
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
        width: '30%',
      },
    //   {
    //     title: 'Danh mục',
    //     key: 'category',
    //     dataIndex: 'category',
    //   },
      {
        title: 'Mẫu',
        key: 'template',
        dataIndex: 'template',
      },
      {
        title: '',
        key: 'action',
        width: '30%',
      },
    ]
  : [
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
        width: '30%',
      },
      {
        title: 'Mẫu',
        key: 'template',
        dataIndex: 'template',
      },
      {
        title: '',
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
  setupSortable();
});
const loadData = async () => {
  const params = {
    page: pagination.value.current,
    search: formFilter.value.search,
  };
  const res = await axios.get(route('apps.json.list', params));

  if (res.data.status) {
    data.value = res.data.data.data.map((v) => {
      return {
        id: v.id,
        name: v.name,
        is_book: v.is_book,
        is_comment: v.is_comment,
        is_show_comment: v.is_show_comment,
        is_novel: v.is_novel,
        status: v.status,
        books_count:v.books_count,
        age: 32,
        menu: 'Quản lý môn học/khóa học',
        category: 'Kho truyện',
        template: 'Mẫu',
        img: v?.img,
        access_permission: v.access_permission,
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
          const fromId = updated[from].id;
          const toId = updated[to].id;
          const movedItem = updated.splice(from, 1)[0];
          updated.splice(to, 0, movedItem);
          data.value = updated;
          const params = {
            from_id: fromId,
            to_id: toId,
          };
          const res = axios.patch(route('apps.json.updatePosition', params));
        },
      });
    }
  });
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
const confirm = (value) => {
  axios
    .delete(route('apps.json.delete', { id: value }))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Xóa app thành công');
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
    .post(route('apps.json.visible', { id: id, status: 'off' }))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Cập nhật status app thành công');
        setTimeout(function () {
          location.href = '/apps?appId=' + app_id; // URL cần chuyển hướng
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
    .post(route('apps.json.visible', { id: id, status: 'on' }))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Cập nhật status app thành công');
        setTimeout(function () {
          location.href = '/apps?appId=' + app_id; // URL cần chuyển hướng
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
const copyApp = (id) => {
  axios
    .post(route('apps.json.clone', { id: id }))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Copy app thành công');
        setTimeout(function () {
          window.location = response.data.data.redirectUrl;
        }, 500);
      }
    })
    .catch(() => {
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
};
</script>

<template>
  <Head title="App" />

  <MasterLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">App</h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-10/12">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Danh sách App</h1>
        <Link
          :href="route('apps.create', { appId: app_id })"
          class="mt-6"
          v-if="showAdminMenu"
        >
          <a-button class="mt-6" type="primary">Thêm mới </a-button>
        </Link>
        <div class="filter-page mt-4 flex gap-10">
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
          >
            <template #bodyCell="{ column, record, index }">
                <template v-if="column.key === 'id'">
                    {{ index + 1 + pagination.pageSize * (pagination.current - 1) }}
                </template>
                <template v-if="column.key === 'name'">
                    <Link :href="route('books.index', {appId: record.id})" class="flex gap-2">
                        <p :class="`flex items-center font-bold ${record.status === 'off' ? 'hide-class' : ''}`">
                            <span>{{ record.name }}</span>
                            <span v-if="record.books_count > 0">&nbsp;({{ record.books_count }})</span>
                        </p>
                    </Link>
                </template>
                <template v-if="column.key === 'category' && showAdminMenu">
                    <a-button
                    :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                    size="large"
                    v-if="!record.is_novel"
                    type="primary"
                    disabled
                    >{{ record.category }}
                    </a-button>
                    <a-button
                    v-else
                    :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                    size="large"
                    type="primary"
                    >{{ record.category }}
                    </a-button>
                </template>
                <template v-if="column.key === 'template'">
                    <div class="flex gap-4">
                        <Link v-if="showAdminMenu":href="route('templates.index', { app_id: record.id })">
                            <a-button
                                :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                size="large"
                                type="primary"
                            >
                                {{ record.template }}
                            </a-button>
                        </Link>
                        <template v-if="record.is_show_comment">
                            <Link v-if="!record.is_comment">
                                <a-button
                                :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                size="large"
                                type="primary"
                                disabled
                                >
                                Góp ý
                                </a-button>
                            </Link>
                            <Link
                                v-else
                                :href="route('comments.listCommentApp', { id: record.id })"
                            >
                                <a-button
                                :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                size="large"
                                type="primary"
                                >
                                Góp ý
                                </a-button>
                            </Link>
                        </template>
                    </div>
                </template>

                <template v-if="column.key === 'action'">
                    <template v-if="record.access_permission == 1">
                        <img
                        v-if="record.status === 'off'"
                        @click="showApp(record.id)"
                        :class="`h-[30px] btn-view cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                        src="/images/icon-eye-closed.png"
                        alt="eye-closed"
                        />
                        <img
                        v-else
                        @click="hideApp(record.id)"
                        :class="`h-[30px] btn-view cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
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
                                <Link :href="route('apps.edit', { id: record.id })">Cài đặt</Link>
                                </a-menu-item>
                                <a-menu-item @click="copyApp(record.id)">
                                Copy
                                </a-menu-item>
                                <a-menu-item danger>
                                <a-popconfirm
                                    placement="topRight"
                                    ok-text="Xóa"
                                    cancel-text="Bỏ qua"
                                    @confirm="confirm(record.id)"
                                >
                                    <template #title>
                                    <p>Bạn có chắc chắn muốn xoá app này?</p>
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
                <Link :href="route('apps.create', { appId: app_id })">
                  <img
                    v-if="showAdminMenu"
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

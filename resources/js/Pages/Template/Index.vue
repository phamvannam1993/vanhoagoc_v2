<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { SearchOutlined } from "@ant-design/icons-vue";
import { ref, onMounted, defineProps, nextTick } from "vue";
import { Link } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import Sortable from "sortablejs";

const props = defineProps({
    app: {
        type: Object,
    },
});
const toast = useToast();
const page = usePage();
const query = page.props.query;
const appList = page.props.appList;
const app_id = query.app_id;
const columns = [
    {
        title: "STT",
        dataIndex: "id",
        key: "id",
        width: 80,
        align: "center"
    },
    {
        title: "Ảnh",
        dataIndex: "image",
        key: "image",
    },
    {
        title: "Tên",
        dataIndex: "name",
        key: "name",
        width: "20%"
    },
    {
        title: "Mã",
        dataIndex: "code",
        key: "code"
    },
    {
        title: "SL câu trả lời",
        key: "input_number",
        dataIndex: "input_number"
    },
    {
        title: "SL từ tối đa",
        key: "max_word",
        dataIndex: "max_word"
    },
    {
        title: "Ngày",
        key: "created_at",
        dataIndex: "created_at"
    },
    {
        title: "",
        key: "action"
    }
];
const pagination = ref({
    current: 1,
    pageSize: 20,
    total: 0
});
const data = ref([]);
const formFilter = ref({
    search: ""
});
const tableRef = ref(null);
onMounted(async () => {
    await loadData();
    setupSortable();
});
const loadData = async () => {
    const params = {
        page: pagination.value.current,
        pageSize: pagination.value.pageSize,
        search: formFilter.value.search,
        app_id: app_id
    };
    const res = await axios.get(route("templates.json.list", params));
    if (res.data.status) {
        data.value = res.data.data.data.map((v) => {
            return {
                id: v.id,
                status: v.status,
                name: v.template.name,
                image: v.image ? v.image : null,
                code: v.playable ? v.playable : null,
                input_number: v.template.number_answer ? v.template.number_answer : null,
                max_word: v.template.max_length_question ? v.template.max_length_question : null,
                created_at: v.created_at ? v.created_at : null,
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
                    const res = axios.patch(route('templates.json.updatePosition', params))
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
    formFilter.value.search = "";
    loadData();
};
const confirm = (value) => {
    axios
        .delete(route("templates.json.delete", { id: value }))
        .then((response) => {
            if (response.status === 200) {
                toast.success("Xóa template thành công");
                loadData();
            }
        })
        .catch(() => {
            toast.error("Đã có lỗi xảy ra, vui lòng thử lại sau!");
        });
};
const hideApp = (id) => {
    axios
        .post(route("templates.json.visible", { id: id, status: "off" }))
        .then((response) => {
            if (response.status === 200) {
                toast.success("Cập nhật status template thành công");
                setTimeout(function() {
                    location.href = route('templates.index', { app_id: app_id })
                }, 500);
            }
        })
        .catch(() => {
            toast.error("Đã có lỗi xảy ra, vui lòng thử lại sau!");
        });
};
const showApp = (id) => {
    axios
        .post(route("templates.json.visible", { id: id, status: "on" }))
        .then((response) => {
            if (response.status === 200) {
                toast.success("Cập nhật status template thành công");
                setTimeout(function() {
                    location.href = route('templates.index', { app_id: app_id })
                }, 500);
            }
        })
        .catch(() => {
            toast.error("Đã có lỗi xảy ra, vui lòng thử lại sau!");
        });
};
// Thay đổi class của dòng theo logic
const rowClassName = (record, index) => {
    if (record.status === "off") {
        return "grey-row";
    }
    return "";
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
const showOption = ref(false)
const toggleOption = () => {
  showOption.value = !showOption.value
}
const isCopy = ref(false)
const isDelete = ref(false)
const copyTemplate= async (app_id) => {
  if(listRecordDelete.value.length == 0) {
     toast.error('Vui lòng chọn tuần');
     return false
  }
  if(isCopy.value) {
    return false
  }
  isCopy.value = true
   axios
    .post(route('templates.json.copyData', {template_ids: listRecordDelete.value, app_id:app_id }))
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
    .post(route('templates.json.deleteMany', { template_ids: listRecordDelete.value }))
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
    <Head title="Template" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Template
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-full md:w-10/12">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Danh sách template của App: {{ props.app.name }}
                </h1>
                <div class="relative mt-6 flex gap-4">
                    <a-button class="custom-bg mt-3 text-black" size="middle" @click="() => window.history.back()">Quay lại</a-button>
                    <Link :href="route('templates.create', {app_id: app_id})">
                        <a-button class="ml-2" type="primary" size="large">
                            Thêm mới
                        </a-button>
                    </Link>
                <img src="/images/delete.svg" @click="deleteMany()" class="cursor-pointer">
                <img :src="showOption ? '/images/copy.svg' : '/images/copy_hide.svg'" @click="toggleOption()" class="cursor-pointer">
                <div class="jump-box" v-if="showOption">
                    <span class="jump-title">Chuyển đến:</span>
                    <ul>
                        <li v-for="(item, index) in appList" :key="index" @click="copyTemplate(item.id)">
                            {{ item.name }}
                        </li>
                    </ul>
                </div>
                </div>
                <div class="filter-page mt-4 flex gap-10">
                    <a-input
                        placeholder="Tìm kiếm"
                        :allow-clear="true"
                        style="width: 30rem"
                        v-model:value="formFilter.search"
                    >
                        <!-- Sử dụng slot suffix để đặt icon -->
                        <template #suffix>
                            <SearchOutlined
                                @click="onSearch"
                                style="cursor: pointer"
                            />
                        </template>
                    </a-input>
                    <a-button @click="removeFilter" size="large"
                    >Xóa lọc
                    </a-button
                    >
                </div>
                <div class="mt-4">
                    <a-table
                        :columns="columns"
                        :data-source="data"
                        :pagination="false"
                        ref="tableRef"
                        rowKey="id"
                        :row-selection="rowSelection"
                    >
                        <template #bodyCell="{ column, record, index }">
                            <template v-if="column.key === 'id'">
                                {{ index + 1 + pagination.pageSize * (pagination.current - 1) }}
                            </template>
                            <template v-if="column.key === 'image'">
                                <div class="flex gap-2">
                                    <img
                                        :class="`h-[62px] w-[114px] ${record.status === 'off' ? 'hide-class' : ''}`"
                                        v-if="record.image"
                                        :src="record.image"
                                        alt=""
                                    />
                                    <img
                                        :class="`h-[62px] w-[114px] ${record.status === 'off' ? 'hide-class' : ''}`"
                                        v-else
                                        src="/images/image_book.png"
                                        alt=""
                                    />
                                </div>
                            </template>
                            <template v-if="column.key === 'name'">
                                <div :class="`flex gap-2 ${record.status === 'off' ? 'hide-class' : ''}`">
                                    {{ record.name }}
                                </div>
                            </template>
                            <template v-if="column.key === 'code'">
                                <div :class="`flex gap-2 ${record.status === 'off' ? 'hide-class' : ''}`">
                                    {{ record.code }}
                                </div>
                            </template>
                            <template v-if="column.key === 'input_number'">
                                <div :class="`flex gap-2 ${record.status === 'off' ? 'hide-class' : ''}`">
                                    {{ record.input_number }}
                                </div>
                            </template>
                            <template v-if="column.key === 'max_word'">
                                <div :class="`flex gap-2 ${record.status === 'off' ? 'hide-class' : ''}`">
                                    {{ record.max_word }}
                                </div>
                            </template>
                            <template v-if="column.key === 'created_at'">
                                <div :class="`flex gap-2 ${record.status === 'off' ? 'hide-class' : ''}`">
                                    {{ record.created_at }}
                                </div>
                            </template>
                            <template v-if="column.key === 'action'">
                                <div class="flex items-center gap-12">
                                    <img
                                        v-if="record.status === 'off'"
                                        @click="showApp(record.id)"
                                        :class="`h-[24px] cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                                        src="/images/icon-eye-closed.png"
                                        alt="eye-closed"
                                    />
                                    <img
                                        v-else
                                        @click="hideApp(record.id)"
                                        :class="`h-[24px] cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                                        src="/images/icon-eye-open.png"
                                        alt="eys-open"
                                    />
                                    <div
                                        :class="`cursor-pointer text-[15px] font-semibold ${record.status === 'off' ? 'hide-class' : ''}`"
                                    >
                                        <Link
                                            :href="route('templates.edit', {
                                                question_template_id: record.id,
                                                app_id: app_id
                                            })"
                                        >Sửa
                                        </Link
                                        >
                                    </div>
<!--                                    <div-->
<!--                                        :class="`cursor-pointer text-[15px] font-semibold ${record.status === 'off' ? 'hide-class' : ''}`"-->
<!--                                    >-->
<!--                                        <Link-->
<!--                                            href=""-->
<!--                                        >Tọa độ-->
<!--                                        </Link-->
<!--                                        >-->
<!--                                    </div>-->
                                    <div
                                        :class="`cursor-pointer text-[15px] font-semibold ${record.status === 'off' ? 'hide-class' : ''}`"
                                    >
                                        <a-popconfirm
                                            placement="topRight"
                                            ok-text="Xóa"
                                            cancel-text="Bỏ qua"
                                            @confirm="confirm(record.id)"
                                        >
                                            <template #title>
                                                <p>
                                                    Bạn có chắc chắn muốn xoá
                                                    template này?
                                                </p>
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
                                <a-pagination
                                    v-bind="pagination"
                                    @change="onPageChange"
                                />
                                  <a-select
                                    ref="select"
                                    v-model:value="pagination.pageSize"
                                    @focus="focus"
                                    @change="handleChangePageSize"
                                >
                                    <a-select-option v-for="(item, index) in listPageSize" :value="item.value">{{ item.label }}</a-select-option>
                                </a-select>
                                <!-- Icon plus -->
                                <Link :href="route('templates.create', {app_id: app_id})">
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

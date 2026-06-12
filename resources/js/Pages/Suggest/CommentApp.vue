<script setup>
import { Head } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { SearchOutlined } from '@ant-design/icons-vue';
import { onMounted, reactive, ref } from "vue";
import { Link } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";

const props = defineProps({
    app: {
        type: Object,
    },
    allowedWeekIds: {
        type: Array,
        default: null,
    },
});

const toast = useToast();
const id = ref('');
const pathParts = window.location.pathname.split("/").filter(Boolean);
id.value = pathParts[pathParts.length - 1];
const options = ref([
    {
        value: -1,
        label: 'Chọn ngày',
    },
    {
        value: 1,
        label: 'Hôm nay',
    },
    {
        value: 2,
        label: 'Hôm qua',
    },
    {
        value: 3,
        label: '7 ngày qua',
    },
    {
        value: 4,
        label: '30 ngày qua',
    },
    {
        value: 5,
        label: 'Năm nay',
    },
]);

const statusConfigs = [
  { label: 'Chờ xử lý', value: 1 },
  { label: 'Đã duyệt', value: 2 },
  { label: 'Từ chối', value: 3 }
]

const filterStatuses = [
  {
    label: 'Tất cả',
    value: ''
  },
  ...statusConfigs
];

const weekOptions = [
  {
    label: 'Tất cả',
    value: ''
  },
  ...props.app.books.map(book => ({
    label: book.name,
    options: book.weeks.map(week => ({
      label: week.name,
      value: week.id
    }))
  }))
];
const handleChange = (value) => {
    formFilter.value.optionFilter = value;
    loadData();
};
const handleChangeWeekFilter = (value) => {
    formFilter.value.weekOptionFilter = value;
    loadData();
};
const handleChangeStatusFilter = (value) => {
    formFilter.value.status = value;
    loadData();
};

const filterOption = (input, option) => {
    return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};
const columns = [
    {
        title: 'STT',
        dataIndex: 'id',
        key: 'id',
        align: 'center',
        width: '5%'
    },
    {
        title: 'Tên',
        dataIndex: 'name',
        key: 'name',
        width: '12%'
    },
    {
        title: 'Nội dung comment',
        key: 'content',
        dataIndex: 'content',
        width: '25%'
    },
    {
        title: 'Ảnh hoặc link',
        key: 'image',
        dataIndex: 'image',
    },
    {
        title: 'Ngày',
        key: 'created_at',
        dataIndex: 'created_at',
        className: 'text-center',
        width: '5%'
    },
    {
        title: 'Trạng thái',
        key: 'status',
        width: '10%'
    },
    {
        title: '',
        key: 'action',
        width: '5%'
    },
];
const pagination = ref({
    current: 1,
    pageSize: 20,
    total: 0
});
const data = ref([]);
const formFilter = ref({
    optionFilter: -1,
    weekOptionFilter: null,
    status: 1
});
onMounted(() => {
    loadData();
});
const loadData = async () => {
    const params = {
        page: pagination.value.current,
        app_id: id.value,
        pageSize: pagination.value.pageSize,
        optionFilter: formFilter.value.optionFilter,
        weekOptionFilter: formFilter.value.weekOptionFilter,
        status: formFilter.value.status,
        allowedWeekIds: props.allowedWeekIds,
    };
    const res = await axios.get(route("comments.json.list", params));
    if (res.status) {
        data.value = res.data.data.map((v) => {
            return {
                id: v.id,
                name: v.user?.name,
                image: v.user?.img,
                content: v.comment,
                created_at: v.created_at_formatted,
                link: v.link,
                status: v.status,
                img: v.img
            };
        });
        pagination.value.pageSize = res.data.per_page;
        pagination.value.total = res.data.total;
        pagination.value.current = res.data.current_page;
    }
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
const onPageChange = (page) => {
     pagination.value.current = page;
    loadData()
};
const handleChangePageSize = () => {
    loadData();
}

const handleStatusChange = async (record) => {
    await axios.post(route("comments.json.update", record))
        .then((response) => {
            if (response.status === 200) {
                if (response.data.status) {
                    toast.success(response.data.message);
                }else{
                    toast.error(response.data.message);
                }
            }else{
                toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
            }
        })
        .catch(() => {
            toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
        });
    loadData();
}

const removeFilter = () => {
  formFilter.value.optionFilter = null;
};
const confirm = (value) => {
    console.log(value)
    axios
        .delete(route('comments.json.delete', { id: value }))
        .then((response) => {
            if (response.status === 200) {
                toast.success('Xóa comment thành công');
                loadData();
            }
        })
        .catch(() => {
            toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
        });
};
</script>

<template>
    <Head :title="`Comment - ${app.name}`" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Comment - {{ app.name }}
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-full md:w-10/12 sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Danh sách comment - {{ app.name }}
                </h1>
                <div class="relative mt-6 flex gap-4">
                    <Link :href="route('apps.dashboard')">
                        <a-button
                            class="custom-bg mt-3 text-black"
                            size="middle"
                        >
                            Quay lại
                        </a-button>
                    </Link>
                </div>
                <div class="filter-page mt-4 flex gap-10">
                    <a-select
                        class="input-search w-2/12"
                        v-model:value="formFilter.optionFilter"
                        show-search
                        placeholder="Tất cả khóa học"
                        size="large"
                        :options="options"
                        :filter-option="filterOption"
                        @change="handleChange"
                    ></a-select>
                    <a-select
                        class="input-search w-3/12"
                        v-model:value="formFilter.weekOptionFilter"
                        show-search
                        placeholder="Tất cả khóa học"
                        size="large"
                        :options="weekOptions"
                        :filter-option="filterOption"
                        @change="handleChangeWeekFilter"
                    ></a-select>
                    <a-select
                        class="input-search w-3/12"
                        v-model:value="formFilter.status"
                        show-search
                        placeholder="Trạng thái"
                        size="large"
                        :options="filterStatuses"
                        :filter-option="filterOption"
                        @change="handleChangeStatusFilter"
                    ></a-select>
                    <a-button @click="removeFilter" size="large"
                    >Xóa lọc</a-button
                    >
                </div>
                <div class="mt-8 w-full">
                    <a-table
                        :columns="columns"
                        :data-source="data"
                        :pagination="false"
                        bordered
                        :scroll="{ x: 'max-content' }"
                    >
                        <template #bodyCell="{ column, record, index }">
                            <template v-if="column.key === 'id'">
                                {{ index + 1 + pagination.pageSize * (pagination.current - 1) }}
                            </template>
                            <template v-if="column.key === 'name'">
                                <div class="flex gap-2">
                                    <a
                                        class="flex items-center font-bold"
                                    >
                                        {{ record.name }}
                                    </a>
                                </div>
                            </template>
                            <template v-if="column.key === 'content'">
                                <div>{{ record.content }}</div>
                            </template>
                            <template v-if="column.key === 'image'">
                                <div v-if="record.link"><a :href=record.link target="_blank">{{ record.link }}</a></div>
                                <div class="w-[75px] h-[25px]">
                                    <a-image class="w-full h-full object-contain" v-if="record.img" :src="record.img" alt="" :preview="true"/>
                                </div>
                            </template>
                            <template v-if="column.key === 'created_at'">
                                <div>{{ record.created_at }}</div>
                            </template>
                            <template v-if="column.key === 'status'">
                                <a-select
                                    class="w-full"
                                    v-model:value="record.status"
                                    :options="statusConfigs"
                                    placeholder="Chọn trạng thái"
                                    size="large"
                                    @change="handleStatusChange(record)"
                                />
                            </template>
                            <template v-if="column.key === 'action'">
                                <a-popconfirm
                                    placement="topRight"
                                    ok-text="Xóa"
                                    cancel-text="Bỏ qua"
                                    @confirm="confirm(record.id)"
                                >
                                    <template #title>
                                        <p>Bạn có chắc chắn muốn xoá comment này?</p>
                                    </template>
                                    <img
                                        class="delete-icon cursor-pointer"
                                        src="/images/icon-game-choose-correct/icon-delete.png"
                                        alt=""
                                    />
                                </a-popconfirm>
                            </template>
                        </template>
                        <template #footer>
                            <div
                                class="flex items-center justify-end"
                            >
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
.custom-background {
    background-color: #ffb800;
}
</style>

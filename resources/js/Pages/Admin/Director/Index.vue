<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import {SearchOutlined} from '@ant-design/icons-vue';
import {ref, onMounted, nextTick} from 'vue';
import {Link} from '@inertiajs/vue3';
import {useToast} from 'vue-toastification';
import Sortable from 'sortablejs';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";

const toast = useToast();
const page = usePage();
const user = page.props.auth.user;
const showAdminMenu = !!user.user_type_id && user.user_type_id == 2;
const query = page.props.query;
const app_id = query.app_id;
const columns = [
    {
        title: 'STT',
        dataIndex: 'id',
        key: 'id',
        width: 80,
        align: 'center',
    },
    {
        title: 'Họ tên',
        dataIndex: 'name',
        key: 'name',
        width: '20%',
    },
    {
        title: 'Tên tài khoản',
        dataIndex: 'username',
        key: 'username',
        width: '20%',
    },
    {
        title: 'Email',
        dataIndex: 'email',
        key: 'email',
        width: '10%',
    },
    {
        title: 'Số điện thoại',
        key: 'tel',
        dataIndex: 'tel',
        width: '10%',
    },
    {
        title: 'Đơn vị (App)',
        key: 'app',
        dataIndex: 'app',
        width: '10%',
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
    app_id: app_id
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
    const res = await axios.get(route('admins.directors.json.jsonList', params));

    if (res.data.status) {
        data.value = res.data.data.data.map((v) => {
            return {
                id: v.id,
                name: v.name,
                email:v.email,
                username: v.username,
                tel: v.tel,
                apps: v?.director_apps ?? [],
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
const confirmDeleteMulti = () => {
    const idsToDelete = listRecordDelete.value.map(item => item.id);
    axios.delete(route('admins.directors.json.deleteMultiple'), {
        data: { ids: idsToDelete }
    })
    .then(response => {
        if (response.status === 200) {
            toast.success('Xóa tài khoản Hiệu trưởng/Giám đốc thành công');
            setTimeout(function () {
                location.href = route('admins.directors.index')
            }, 500);
        }
    })
    .catch(error => {
        toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
}
const confirm = (value) => {
    axios
        .delete(route('admins.directors.json.delete', { id: value }))
        .then((response) => {
            if (response.status === 200) {
                toast.success('Xóa tài khoản Hiệu trưởng/Giám đốc thành công');
                setTimeout(function () {
                    location.href = route('admins.directors.index')
                }, 500);
            }
        })
        .catch(() => {
            toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
        });
};
const resetPassword = (value) => {
    axios
        .post(route('admins.directors.json.resetPassword', { id: value }))
        .then((response) => {
            if (response.status === 200) {
                toast.success('Reset password thành công');
                setTimeout(function () {
                    location.href = route('admins.directors.index')
                }, 500);
            }
        })
        .catch(() => {
            toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
        });
};
</script>

<template>
    <Head title="Director"/>

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Director</h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-11/12">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Danh sách Giám đốc/Hiệu trưởng</h1>
                <div class="relative mt-6 flex gap-4">
                    <Link
                        :href="route('admins.directors.create')"
                        v-if="showAdminMenu"
                    >
                        <a-button  type="primary">Thêm mới</a-button>
                    </Link>
                    <img v-if="showDelete" src="/images/delete.svg" @click="confirmDeleteMulti()" class="cursor-pointer">
                </div>
                <div class="filter-page mt-4 flex gap-5 flex-col md:flex-row">
                    <a-input
                        class="md:w-[300px]"
                        placeholder="Nhập tên tìm kiếm"
                        :allow-clear="true"
                        v-model:value="formFilter.search"
                    >
                        <!-- Sử dụng slot suffix để đặt icon -->
                        <template #suffix>
                            <SearchOutlined @click="onSearch" style="cursor: pointer"/>
                        </template>
                    </a-input>
                    <a-button @click="removeFilter" size="large">Xóa lọc</a-button>
                </div>
                <div class="mt-4">
                    <a-table
                        :columns="columns"
                        :data-source="data"
                        :pagination="false"
                        size="small"
                        ref="tableRef"
                        rowKey="id"
                        :scroll="{ x: 'max-content' }"
                        :row-selection="rowSelection"
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
                            <template v-if="column.key === 'username'">
                                <div class="flex gap-2">
                                    <p
                                        :class="`flex items-center font-bold ${record.status === 'off' ? 'hide-class' : ''}`"
                                    >
                                        {{ record.username }}
                                    </p>
                                </div>
                            </template>
                            <template v-if="column.key === 'tel'">
                                <div class="flex gap-2">
                                    <p
                                        :class="`flex items-center font-bold ${record.status === 'off' ? 'hide-class' : ''}`"
                                    >
                                        {{ record.tel }}
                                    </p>
                                </div>
                            </template>
                            <template v-if="column.key === 'app'">
                                <div class="flex flex-col gap-1">
                                    <a-tag v-for="app in record.apps" :key="app.id" color="blue">{{ app.name }}</a-tag>
                                    <span v-if="!record.apps.length" class="text-gray-400">—</span>
                                </div>
                            </template>
                            <template v-else-if="column.key === 'action'">
                                <div class="flex gap-6">
                                    <div
                                        :class="`cursor-pointer text-[15px] font-semibold ${record.status === 'off' ? 'hide-class' : ''}`"
                                    >
                                        <Link
                                            :href="
                                            route('admins.directors.edit', {
                                              user_id: record.id,
                                            })
                                          "
                                        >Sửa</Link
                                        >
                                    </div>
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
                                                <p>Bạn có chắc chắn muốn xoá tài khoản này?</p>
                                            </template>
                                            Xóa
                                        </a-popconfirm>
                                    </div>
                                    <div
                                        :class="`cursor-pointer text-[15px] font-semibold ${record.status === 'off' ? 'hide-class' : ''}`"
                                    >
                                        <a-popconfirm
                                            placement="topRight"
                                            ok-text="Có"
                                            cancel-text="Bỏ qua"
                                            @confirm="resetPassword(record.id)"
                                        >
                                            <template #title>
                                                <p>Bạn có chắc chắn muốn reset mật khẩu?</p>
                                            </template>
                                            Reset mật khẩu
                                        </a-popconfirm>
                                    </div>
                                </div>
                            </template>
                        </template>
                        <template #footer>
                            <div class="flex items-center justify-end">
                                <!-- Pagination -->
                                <a-pagination v-bind="pagination" @change="onPageChange"/>
                                <!-- Icon plus -->
                                <Link :href="route('admins.class.create', { app_id: app_id })">
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

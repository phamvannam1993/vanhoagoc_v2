<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import {SearchOutlined} from '@ant-design/icons-vue';
import {ref, onMounted, nextTick, defineProps} from 'vue';
import {Link} from '@inertiajs/vue3';
import {useToast} from 'vue-toastification';
import { USER_TYPE_ADMIN, USER_TYPE_DIRECTOR, USER_TYPE_TEACHER } from "@/const.js";
import SchoolLayout from "@/Layouts/SchoolLayout.vue";

const props = defineProps({
    appId: {
        type: String,
    },
});
const toast = useToast();
const page = usePage();
const user = page.props.auth.user;
const query = page.props.query;
const app_id = ref(query.app_id);
const userType = user.user_type.type;

if (userType === USER_TYPE_TEACHER) {
    app_id.value = props.appId
}
const baseColumns  = [
    {
        title: 'STT',
        dataIndex: 'id',
        key: 'id',
        width: 80,
        align: 'center',
    },
     {
        title: 'ID',
        dataIndex: 'id',
        key: 'db_id',
        width: 80,
        align: 'center',
    },
    {
        title: 'Tên Phòng ban/Lớp',
        dataIndex: 'name',
        key: 'name',
        width: '30%',
    },
    {
        title: 'Nhập Excel',
        key: 'import',
        dataIndex: 'import',
        width: '10%',
    },
    {
        title: 'Xuất Excel',
        key: 'export',
        dataIndex: 'export',
        width: '10%',
    },
    {
        title: 'Kết quả học tập',
        key: 'result',
        dataIndex: 'result',
    },
    {
        title: 'Thời gian tạo',
        key: 'beauty_created_at',
        dataIndex: 'beauty_created_at',
    },
    {
        title: 'Tùy chỉnh',
        key: 'action',
    },
];

if (userType === USER_TYPE_TEACHER) {
    baseColumns.splice(5, 0, { title: 'Giao bài', key: 'assign', dataIndex: 'assign' });
}
const columns = baseColumns;

const pagination = ref({
    current: 1,
    pageSize: 1,
    total: 0,
});
const data = ref([]);
const formFilter = ref({
    search: '',
    app_id: app_id.value
});
const tableRef = ref(null);
onMounted(async () => {
    await loadData();
});
const loadData = async () => {
    if (!formFilter.value.app_id && userType !== USER_TYPE_TEACHER) {
        return;
    }

    const params = {
        page: pagination.value.current,
        search: formFilter.value.search,
        app_id: formFilter.value.app_id
    };

    try {
        const res = await axios.get(route('admins.class.json.list', params));

        if (res.data.status) {
            const responseData = res.data.data;

            data.value = responseData.data.map((v) => {
                const usersCount = v.users_count ?? 0;
                const totalPoint = v.total_point ?? 0;
                const practiceClass = v.practice_class ?? [];

                return {
                    id: v.id,
                    app_id: v.app_id,
                    is_result: v.is_result,
                    name: v.name,
                    users_count: usersCount,
                    total_point: totalPoint,
                    has_student: usersCount > 0,
                    menu: 'DS Nhân viên/Học sinh',
                    result: totalPoint > 0,
                    isAssign: practiceClass.length > 0,
                    beauty_created_at: v.beauty_created_at
                };
            });

            pagination.value.pageSize = responseData.per_page;
            pagination.value.total = responseData.total;
            pagination.value.current = responseData.current_page;
        }
    } catch (error) {
        toast.error('Không tải được danh sách Phòng ban/Lớp!');
        console.error(error);
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
const rowSelection = ref({
    onChange: (selectedRowKeys, selectedRows) => {
        console.log(`selectedRowKeys: ${selectedRowKeys}`, 'selectedRows: ', selectedRows);
    },
    onSelect: (record, selected, selectedRows) => {
        console.log(record, selected, selectedRows);
    },
    onSelectAll: (selected, selectedRows, changeRows) => {
        console.log(selected, selectedRows, changeRows);
    },
});
const fileImport = ref(null);
const classIdImport = ref('');
const appIdImport = ref('');
const isUploading = ref(false);
const triggerFileImport = (classId, appId) => {
    fileImport.value.value = '';
    classIdImport.value = classId;
    appIdImport.value = appId;
    fileImport.value.click();
};
const handleFileImport = async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    isUploading.value = true;
    const formData = new FormData()
    formData.append('file', file)
    formData.append('app_id', appIdImport.value)
    formData.append('class_id', classIdImport.value)

    try {
        axios
            .post(route('admins.students.json.importStudent'), formData)
            .then((response) => {
                if (response.data.status) {
                    toast.success('Import thành công!');
                } else {
                    toast.error('Import không thành công! Vui lòng xem lại format file!');
                }
            })
            .catch((error) => {
                toast.error(error);
            });
    } catch (error) {
        console.error('Lỗi upload:', error);
    } finally {
        isUploading.value = false;
    }
};
const confirm = (value) => {
    axios
        .delete(route('admins.class.json.delete', { id: value }))
        .then((response) => {
            if (response.status === 200) {
                toast.success('Xóa tài khoản Phòng ban/Lớp thành công');
                setTimeout(function () {
                    location.href = ''
                }, 500);
            }
        })
        .catch(() => {
            toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
        });
};
const exportStudent = (classId) => {
    isUploading.value = true;
    axios({
        url: route('admins.students.json.exportStudent', { class_id: classId }),
        method: 'GET',
        responseType: 'blob',
    })
        .then((response) => {
            const blob = new Blob([response.data], { type: response.headers['content-type'] });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'students.xlsx'); // Tên file tải về
            document.body.appendChild(link);
            link.click();
            link.remove();
            toast.success('Export thành công!');
        })
        .catch((error) => {
            toast.error('Export không thành công!');
        })
        .finally(() => {
            isUploading.value = false;
        });
};
const goBack = () => {
    window.location = route('admins.school.index');
};

const breadcrumbs = [
    {'title': 'App', 'url': route('admins.school.index')},
    {'title': page.props.app.name, 'url': route('admins.class.index', {app_id: page.props.app.id})},
]
</script>

<template>
    <Head title="Class"/>

    <SchoolLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Class</h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-full md:w-11/12">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Danh sách Phòng ban/Lớp</h1>
                <a-button v-if="userType === USER_TYPE_ADMIN || userType === USER_TYPE_DIRECTOR" @click="goBack" class="custom-bg text-black mt-6" size="large">
                    Quay lại
                </a-button>

                <Link
                    :href="route('admins.class.create', { app_id: app_id })"

                    v-if="userType === USER_TYPE_ADMIN || userType === USER_TYPE_DIRECTOR"
                >
                    <a-button class="mt-6 ml-5" type="primary">Thêm mới</a-button>
                </Link>
                <div class="filter-page mt-6 flex gap-10">
                    <a-input
                        placeholder="Tìm kiếm"
                        :allow-clear="true"
                        style="width: 30rem"
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
                                    <Link :href=" route('admins.students.index', {class_id: record.id,app_id: record.app_id})">
                                        {{ record.name }}
                                        ({{ record.users_count }})
                                    </Link>
                                    </p>
                                </div>
                            </template>
                            <template v-if="column.key === 'menu'">
                                <Link
                                    :href="
                                        route('admins.students.create', {
                                          app_id: record.app_id,
                                        })
                                      "
                                    v-if="!record.has_student"
                                >
                                    <a-button
                                        :class="`bg-[#b1b1b1] text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                    >{{ record.menu }}
                                    </a-button>
                                </Link>
                                <Link
                                    :href="
                                        route('admins.students.index', {
                                          class_id: record.id,app_id: record.app_id
                                        })
                                    "
                                    v-else
                                >
                                    <a-button
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                        type="primary"
                                    >{{ record.menu }}

                                    ({{ record.users_count }})
                                    </a-button>
                                </Link>
                            </template>
                            <template v-if="column.key === 'import'">
                                <div class="flex gap-4 text-center">
                                    <input type="file" class="hidden" ref="fileImport"
                                           @change="handleFileImport" accept=".xls,.xlsx,.csv" />
                                    <a-button
                                        @click="triggerFileImport(record.id, record.app_id)"
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        :type="record.has_student ? 'primary' : undefined"
                                        :style="!record.has_student ? 'background:#b1b1b1' : ''"
                                        size="large"
                                    >Nhập DS
                                    </a-button>
                                </div>
                            </template>
                            <template v-if="column.key === 'export'">
                                <div class="flex gap-4 text-center">
                                    <a-button
                                        @click="exportStudent(record.id)"
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        :type="record.has_student ? 'primary' : undefined"
                                        :style="!record.has_student ? 'background:#b1b1b1' : ''"
                                        size="large"
                                    >Xuất DS
                                    </a-button>
                                </div>
                            </template>
                            <template v-if="column.key === 'assign'">
                                <Link  v-if="!record.isAssign" :href="route('admins.practices.index', { class_id: record.id })">
                                    <a-button
                                        :class="`bg-[#b1b1b1] text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                    >
                                        Chưa giao
                                    </a-button>
                                </Link>

                                <Link v-else :href="route('admins.class.assignment', { class_id: record.id })">
                                    <a-button
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                        type="primary"
                                    >Đã giao
                                    </a-button>
                                </Link>
                            </template>
                            <template v-if="column.key === 'result'">
                                <a-button
                                    v-if="!record.is_result"
                                    :class="`bg-[#b1b1b1] text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                    size="large"
                                    disabled
                                >
                                    Chưa có kết quả
                                </a-button>
                                <Link v-else :href="route('admins.class.resultLearn', { class_id: record.id })">
                                    <a-button
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                        type="primary"
                                    >Xem kết quả
                                    </a-button>
                                </Link>
                            </template>
                            <template v-else-if="column.key === 'action'">
                                <div
                                    :class="`cursor-pointer flex gap-4 text-[15px] font-semibold ${record.status === 'off' ? 'hide-class' : ''}`"
                                >
                                    <Link
                                        :href="
                                            route('admins.class.edit', {
                                              app_id: record.app_id,
                                              id: record.id,
                                            })
                                          "
                                    >Sửa</Link
                                    >
                                      <a-popconfirm
                                            placement="topRight"
                                            ok-text="Xóa"
                                            cancel-text="Bỏ qua"
                                            @confirm="confirm(record.id)"
                                        >
                                            <template #title>
                                                <p>Bạn có chắc chắn muốn xoá?</p>
                                            </template>
                                            Xóa
                                        </a-popconfirm>
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
                                        v-if="userType === USER_TYPE_ADMIN || userType === USER_TYPE_DIRECTOR || userType === USER_TYPE_TEACHER"
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
        <a-modal
            v-model:open="isUploading"
            centered
            :closable="false"
            :footer="null"
            :maskClosable="false"
        >
            <div class="flex flex-col items-center justify-center p-4">
                <a-spin size="large" />
                <p class="mt-4 text-lg font-medium">Đang tải lên...</p>
            </div>
        </a-modal>
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
.text-name {
    float: right;
    font-size: 20px;
    margin-top: -40px;
}
</style>

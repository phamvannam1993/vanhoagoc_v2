<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { SearchOutlined } from '@ant-design/icons-vue';
import { ref, onMounted, computed } from 'vue';
import { useToast } from 'vue-toastification';
import { USER_TYPE_ADMIN, USER_TYPE_DIRECTOR, USER_TYPE_TEACHER } from '@/const.js';
import SchoolLayout from '@/Layouts/SchoolLayout.vue';

const props = defineProps({
    appId: {
        type: [String, Number],
        default: '',
    },
});

const toast = useToast();
const page = usePage();

const user = page.props?.auth?.user;
const query = page.props?.query || {};
const userType = user?.user_type?.type;

const isTeacher = userType === USER_TYPE_TEACHER;
const isAdminOrDirector = userType === USER_TYPE_ADMIN || userType === USER_TYPE_DIRECTOR;

const normalizeId = (input) => {
    let value = input;

    for (let i = 0; i < 5; i++) {
        if (value && typeof value === 'object') {
            value = value.value ?? value.id ?? null;
        } else {
            break;
        }
    }

    if (value === null || value === undefined || value === '') {
        return null;
    }

    return String(value);
};

const app_id = ref(isTeacher ? normalizeId(props.appId) : normalizeId(query?.app_id));

const pagination = ref({
    current: 1,
    pageSize: 10,
    total: 0,
});

const data = ref([]);
const apps = ref([]);
const tableRef = ref(null);

const formFilter = ref({
    search: '',
    app_id: app_id.value,
});

const fileImport = ref(null);
const classIdImport = ref('');
const appIdImport = ref('');
const isUploading = ref(false);

const currentAppId = computed(() => {
    return normalizeId(formFilter.value.app_id) || normalizeId(app_id.value) || normalizeId(props.appId);
});

const baseColumns = [
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

if (isTeacher) {
    baseColumns.splice(5, 0, {
        title: 'Giao bài',
        key: 'assign',
        dataIndex: 'assign',
    });
}

const columns = baseColumns;

const breadcrumbs = [
    {
        title: 'App',
        url: route('admins.school.index'),
    },
    {
        title: page.props?.app?.name || 'Class',
        url: route('admins.class.index', {
            app_id: page.props?.app?.id,
        }),
    },
];

onMounted(async () => {
    if (isAdminOrDirector) {
        await loadApps();
    }

    await loadData();
});

const loadApps = async () => {
    try {
        const res = await axios.get(route('admins.class.apps'));

        if (res.data.status) {
            apps.value = (res.data.data || []).map(app => ({
                value: String(app.id),
                label: app.name,
            }));

            const selectedId = normalizeId(formFilter.value.app_id);

            if (selectedId) {
                const selectedApp = apps.value.find(a => String(a.value) === selectedId);

                if (selectedApp) {
                    formFilter.value.app_id = String(selectedApp.value);
                    app_id.value = String(selectedApp.value);
                }
            }
        }
    } catch (error) {
        console.error('Error loading apps:', error);
        toast.error('Không tải được danh sách App!');
    }
};

const handleAppChange = (value) => {
    const selectedId = normalizeId(value);

    formFilter.value.app_id = selectedId;
    app_id.value = selectedId;

    pagination.value.current = 1;
    loadData();
};

const buildClassListUrl = () => {
    const params = new URLSearchParams();

    params.set('page', String(pagination.value.current));
    params.set('search', formFilter.value.search || '');

    const selectedAppId = normalizeId(formFilter.value.app_id);

    if (selectedAppId) {
        params.set('app_id', selectedAppId);
    }

    return `${route('admins.class.json.list')}?${params.toString()}`;
};

const loadData = async () => {
    const selectedAppId = normalizeId(formFilter.value.app_id);

    if (!selectedAppId && isTeacher) {
        return;
    }

    try {
        const url = buildClassListUrl();

        console.log('URL class list:', url);

        const res = await axios.get(url);

        if (res.data.status) {
            const responseData = res.data.data;

            data.value = (responseData.data || []).map((v) => {
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
                    beauty_created_at: v.beauty_created_at,
                    status: v.status,
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
    pagination.value.current = 1;
    loadData();
};

const removeFilter = () => {
    formFilter.value.search = '';
    pagination.value.current = 1;
    loadData();
};

const triggerFileImport = (classId, recordAppId) => {
    if (!fileImport.value) return;

    fileImport.value.value = '';
    classIdImport.value = classId;
    appIdImport.value = normalizeId(recordAppId);
    fileImport.value.click();
};

const handleFileImport = async (event) => {
    const file = event.target.files[0];

    if (!file) return;

    isUploading.value = true;

    const formData = new FormData();
    formData.append('file', file);
    formData.append('app_id', appIdImport.value);
    formData.append('class_id', classIdImport.value);

    try {
        const response = await axios.post(route('admins.students.json.importStudent'), formData);

        if (response.data.status) {
            toast.success('Import thành công!');
            await loadData();
        } else {
            toast.error('Import không thành công! Vui lòng xem lại format file!');
        }
    } catch (error) {
        console.error('Lỗi upload:', error);
        toast.error('Import không thành công!');
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

                setTimeout(() => {
                    location.href = '';
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
        url: route('admins.students.json.exportStudent', {
            class_id: classId,
        }),
        method: 'GET',
        responseType: 'blob',
    })
        .then((response) => {
            const blob = new Blob([response.data], {
                type: response.headers['content-type'],
            });

            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');

            link.href = url;
            link.setAttribute('download', 'students.xlsx');

            document.body.appendChild(link);
            link.click();
            link.remove();

            window.URL.revokeObjectURL(url);

            toast.success('Export thành công!');
        })
        .catch((error) => {
            console.error(error);
            toast.error('Export không thành công!');
        })
        .finally(() => {
            isUploading.value = false;
        });
};

const goBack = () => {
    window.location = route('admins.school.index');
};
</script>

<template>
    <Head title="Class" />

    <SchoolLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Class
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-full md:w-11/12">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Danh sách Phòng ban/Lớp
                </h1>

                <a-button
                    v-if="isAdminOrDirector"
                    class="custom-bg text-black mt-6"
                    size="large"
                    @click="goBack"
                >
                    Quay lại
                </a-button>

                <Link
                    v-if="isAdminOrDirector"
                    :href="route('admins.class.create', {
                        app_id: currentAppId,
                    })"
                >
                    <a-button class="mt-6 ml-5" type="primary">
                        Thêm mới
                    </a-button>
                </Link>

                <div class="filter-page mt-6 flex gap-10">
                    <a-select
                        v-if="isAdminOrDirector"
                        v-model:value="formFilter.app_id"
                        placeholder="Chọn App"
                        style="width: 300px"
                        :options="apps"
                        allow-clear
                        show-search
                        option-filter-prop="label"
                        :label-in-value="false"
                        @change="handleAppChange"
                    />

                    <a-input
                        v-model:value="formFilter.search"
                        placeholder="Tìm kiếm"
                        :allow-clear="true"
                        style="width: 30rem"
                        @pressEnter="onSearch"
                    >
                        <template #suffix>
                            <SearchOutlined
                                style="cursor: pointer"
                                @click="onSearch"
                            />
                        </template>
                    </a-input>

                    <a-button size="large" @click="removeFilter">
                        Xóa lọc
                    </a-button>
                </div>

                <input
                    ref="fileImport"
                    type="file"
                    class="hidden"
                    accept=".xls,.xlsx,.csv"
                    @change="handleFileImport"
                />

                <div class="mt-4">
                    <a-table
                        ref="tableRef"
                        :columns="columns"
                        :data-source="data"
                        :pagination="false"
                        rowKey="id"
                        :scroll="{ x: 'max-content' }"
                    >
                        <template #bodyCell="{ column, record, index }">
                            <template v-if="column.key === 'id'">
                                {{
                                    index + 1 + pagination.pageSize * (pagination.current - 1)
                                }}
                            </template>

                            <template v-if="column.key === 'name'">
                                <div class="flex gap-2">
                                    <p
                                        :class="`flex items-center font-bold ${record.status === 'off' ? 'hide-class' : ''}`"
                                    >
                                        <Link
                                            :href="route('admins.students.index', {
                                                class_id: record.id,
                                                app_id: record.app_id,
                                            })"
                                        >
                                            {{ record.name }} ({{ record.users_count }})
                                        </Link>
                                    </p>
                                </div>
                            </template>

                            <template v-if="column.key === 'menu'">
                                <Link
                                    v-if="!record.has_student"
                                    :href="route('admins.students.create', {
                                        app_id: record.app_id,
                                    })"
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
                                    :href="route('admins.students.index', {
                                        class_id: record.id,
                                        app_id: record.app_id,
                                    })"
                                >
                                    <a-button
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                        type="primary"
                                    >
                                        {{ record.menu }} ({{ record.users_count }})
                                    </a-button>
                                </Link>
                            </template>

                            <template v-if="column.key === 'import'">
                                <div class="flex gap-4 text-center">
                                    <a-button
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        :type="record.has_student ? 'primary' : undefined"
                                        :style="!record.has_student ? 'background:#b1b1b1' : ''"
                                        size="large"
                                        @click="triggerFileImport(record.id, record.app_id)"
                                    >
                                        Nhập DS
                                    </a-button>
                                </div>
                            </template>

                            <template v-if="column.key === 'export'">
                                <div class="flex gap-4 text-center">
                                    <a-button
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        :type="record.has_student ? 'primary' : undefined"
                                        :style="!record.has_student ? 'background:#b1b1b1' : ''"
                                        size="large"
                                        @click="exportStudent(record.id)"
                                    >
                                        Xuất DS
                                    </a-button>
                                </div>
                            </template>

                            <template v-if="column.key === 'assign'">
                                <Link
                                    v-if="!record.isAssign"
                                    :href="route('admins.practices.index', {
                                        class_id: record.id,
                                    })"
                                >
                                    <a-button
                                        :class="`bg-[#b1b1b1] text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                    >
                                        Chưa giao
                                    </a-button>
                                </Link>

                                <Link
                                    v-else
                                    :href="route('admins.class.assignment', {
                                        class_id: record.id,
                                    })"
                                >
                                    <a-button
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                        type="primary"
                                    >
                                        Đã giao
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

                                <Link
                                    v-else
                                    :href="route('admins.class.resultLearn', {
                                        class_id: record.id,
                                    })"
                                >
                                    <a-button
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                        type="primary"
                                    >
                                        Xem kết quả
                                    </a-button>
                                </Link>
                            </template>

                            <template v-else-if="column.key === 'action'">
                                <div
                                    :class="`cursor-pointer flex gap-4 text-[15px] font-semibold ${record.status === 'off' ? 'hide-class' : ''}`"
                                >
                                    <Link
                                        :href="route('admins.class.edit', {
                                            app_id: record.app_id,
                                            id: record.id,
                                        })"
                                    >
                                        Sửa
                                    </Link>

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
                                <a-pagination
                                    v-bind="pagination"
                                    @change="onPageChange"
                                />

                                <Link
                                    :href="route('admins.class.create', {
                                        app_id: currentAppId,
                                    })"
                                >
                                    <img
                                        v-if="isAdminOrDirector || isTeacher"
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
                <p class="mt-4 text-lg font-medium">
                    Đang tải lên...
                </p>
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

<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import {SearchOutlined} from '@ant-design/icons-vue';
import {ref, onMounted, nextTick} from 'vue';
import {Link} from '@inertiajs/vue3';
import {useToast} from 'vue-toastification';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { USER_TYPE_ADMIN, USER_TYPE_DIRECTOR, USER_TYPE_TEACHER } from "@/const.js";

const toast = useToast();
const page = usePage();
const user = page.props.auth.user;
const showAdminMenu = !!user.user_type_id && user.user_type_id == 2;
const query = page.props.query;
const name =  page.props.name;
const name_app =  page.props.name_app;
const class_id = query.class_id;
const user_id = query.user_id;
const app_id = page.props.app_id;
const tab_id = query.tab_id ?? 2;
const userType = user.user_type.type;
let columns = [
    { title: 'STT', dataIndex: 'id', key: 'id', align: 'center', width: 60 },
    { title: 'Danh sách nhiệm vụ', dataIndex: 'name', key: 'name' },
    { title: 'GV phụ trách', dataIndex: 'teacher_name', key: 'teacher_name', width: 140 },
    { title: 'Thời hạn', dataIndex: 'duration', key: 'duration', width: 160 },
    { title: 'Tiến độ', dataIndex: 'progress_text', key: 'progress_text', align: 'center', width: 90 },
    { title: 'Tỉ lệ nộp bài', dataIndex: 'submit_rate_text', key: 'submit_rate_text', align: 'center', width: 110 },
    { title: 'Điểm trung bình lớp', dataIndex: 'avg_point', key: 'avg_point', align: 'center', width: 140 },
    { title: 'Bảng xếp hạng', key: 'result', dataIndex: 'result', width: 140 },
];
if(tab_id == 1) {
    columns = [
        {
            title: 'STT',
            dataIndex: 'id',
            key: 'id',
            width: '8%',
        },
        {
            title: 'Họ và tên',
            dataIndex: 'name',
            key: 'name',
            width: '25%',
        },
        {
            title: 'Thành tích (Sao)',
            key: 'star_count',
            dataIndex: 'star_count',
            width: '25%',
        },
        {
            title: 'Thời gian làm',
            key: 'time_text',
            dataIndex: 'time_text',
            width: '22%',
        },
        {
            title: '',
            key: 'expand',
            width: '20%',
        },
    ];
}

const optionsType = ref([
    {
        value: -1,
        label: 'Kết quả tổng hợp'
    },
    {
        value: 1,
        label: 'Kết quả luyện tập'
    },
    {
        value: 2,
        label: 'Kết quả học lý thuyết'
    },
]);

const pagination = ref({
    current: 1,
    pageSize: 1,
    total: 0,
});
const data = ref([]);
const expandedRowKeys = ref([]);
const formFilter = ref({
    search: '',
    user_id:user_id,
    class_id: class_id,
    type: -1
});
const tableRef = ref(null);
onMounted(async () => {
    await loadData();
});
const loadData = async () => {
    const params = {
        page: pagination.value.current,
        class_id: formFilter.value.class_id,
        type: formFilter.value.type
    };
    let res = null;
    if(tab_id == 1) {
        res = await axios.get(route('admins.points.json.getResultByClass', params));
    } else {
        res = await axios.get(route('admins.class.json.assignedTask', params));
    }
    if (res.data.status) {
        if(tab_id == 2) {
            data.value = res.data.data.map((v) => {
                return {
                    id: v.id,
                    name: v.name,
                    status:v.status,
                    duration: v.duration,
                    review_status: v.review_status,
                    point_text: v.point_text,
                    time_text:v.time_text,
                    point_id:v.point_id,
                    is_rank:v.is_rank,
                    day_create:v.day_create,
                    point_text_1:v.point_text_1,
                    point_text_2:v.point_text_2,
                    total_point:v.total_point,
                    practice_id:v.practice.id,
                    rank:v.rank,
                    teacher_name: v.teacher_name,
                    progress_text: v.progress_text,
                    submit_rate_text: v.submit_rate_text,
                    avg_point: v.avg_point,
                };
            });
        } else {
            data.value = res.data.data.data.map((v) => {
                const totalStar = v.best_points_per_practice?.reduce((sum, item) => sum + (item.star_count || 0), 0) || 0;
                const totalTimeInSeconds = v.best_points_per_practice?.reduce((sum, item) => {
                    if (item.type === 1) {
                        return sum + (item.time || 0);
                    }
                    return sum;
                }, 0) || 0;
                return {
                    id: v.id,
                    name: v.name,
                    star_count: Number(totalStar),
                    practice_id: v.best_points_per_practice.length > 0 ? v.best_points_per_practice[0].practice_id : '',
                    type:formFilter.value.type,
                    time_text: totalTimeInSeconds > 0 ? formatTimeToMinutesSeconds(totalTimeInSeconds) : '',
                    best_points_per_practice: v.best_points_per_practice || []
                };
            });
            pagination.value.pageSize = res.data.data.per_page;
            pagination.value.total = res.data.data.total;
            pagination.value.current = res.data.data.current_page;
        }
    }
};
function formatTimeToMinutesSeconds(time) {
    const minutes = Math.floor(time / 60);
    const seconds = Math.round(time % 60);
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}
const onPageChange = (page) => {
    pagination.value.current = page;
    loadData();
};
const onSearch = () => {
    loadData();
};
const removeFilter = () => {
    formFilter.value.search = '';
    formFilter.value.type_id = null;
    loadData();
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

const popoverVisible = ref(false);
const listApp = ref([]);
const handleClickTitle = async (appId) => {
    const params = {
        app_id: appId
    };
    const res = await axios.get(route('admins.teachers.json.getClassByApp', params));
    if (res.data.status) {
        listApp.value = res.data.data.map((v) => {
            return { value: v.id, label: v.name }
        });
    }
}

const filterOption = (input, option) => {
    return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const handleChangeType = (value) => {
    formFilter.value.type = value;
    loadData();
};
const breadcrumbs = [
    {'title': 'App', 'url': route('admins.school.index')},
    {'title': page.props.class?.app.name, 'url': route('admins.class.index', {app_id: page.props.class?.app.id})},
    {'title': page.props.class?.name, 'url' : ''},
]
</script>

<template>
    <Head title="Student"/>

    <SchoolLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Student</h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-11/12">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Kết quả học tập</h1>
                <Link
                    :href="route('admins.class.index', { app_id: app_id, class_id: class_id, tab_id:tab_id })"
                    class="mt-6"
                >
                    <a-button class="mt-6 gray-btn">Quay lại</a-button>
                </Link>
                <a
                    :href="route('admins.class.exportResult', { class_id: class_id, tab_id: tab_id })"
                    class="mt-6 ml-4"
                >
                    <a-button class="mt-6" type="default">⬇ Xuất Word</a-button>
                </a>
                <Link
                    :href="route('admins.class.resultLearn', { user_id: user_id, class_id: class_id, tab_id:2 })"
                    class="mt-6 ml-20"
                >
                    <a-button class="mt-6" :class="tab_id == 1 ? 'gray-btn color-white' : ''" :type="tab_id == 2 ? 'primary' : 'default'">Nhiệm vụ được giao</a-button>
                </Link>
               <Link
                   :href="route('admins.class.resultLearn', { user_id: user_id, class_id: class_id, tab_id:1 })"
                    class="mt-6 ml-2"
                >
                    <a-button class="mt-6" :class="tab_id == 2 ? 'gray-btn color-white' : ''" :type="tab_id == 1 ? 'primary' : 'default'">Luyện tập tự do</a-button>
                </Link>
                <div class="filter-page mt-4 flex gap-10" v-if="tab_id == 1">
                    <a-select
                        class="input-search"
                        v-model:value="formFilter.type"
                        show-search
                        placeholder="Tất cả khóa học"
                        style="width: 30rem"
                        size="large"
                        :options="optionsType"
                        :filter-option="filterOption"
                        @change="handleChangeType"
                    ></a-select>
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
                        :expandable="tab_id == 1 ? { expandedRowKeys, onExpand: (expanded, record) => { if(expanded) expandedRowKeys.push(record.id); else expandedRowKeys.splice(expandedRowKeys.indexOf(record.id), 1); } } : null"
                    >
                        <template #bodyCell="{ column, record, index }">
                            <template v-if="column.key === 'id'">
                                {{ index + 1 + pagination.pageSize * (pagination.current - 1) }}
                            </template>
                            <template v-if="column.key === 'result' ">
                                <Link v-if="record.is_rank" :href="route('admins.class.showRank', { practice_id: record.practice_id, class_id:class_id , tab_id:tab_id})">
                                <a-button
                                    :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                    size="large"
                                    type="primary"
                                >Bảng xếp hạng
                                </a-button>
                            </Link>
                            <Link v-else>
                                <a-button
                                    :class="`bg-[#b1b1b1] text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                    size="large"
                                >
                                    Chưa thực hiện
                                </a-button>
                            </Link>
                            </template>

                            <template v-if="column.key === 'detail' && record.type != 2">
                                <a-button
                                    type="primary"
                                    @click="expandedRowKeys.includes(record.id) ? expandedRowKeys.splice(expandedRowKeys.indexOf(record.id), 1) : expandedRowKeys.push(record.id)"
                                    v-if="tab_id == 1"
                                >
                                    {{ expandedRowKeys.includes(record.id) ? 'Ẩn chi tiết' : 'Xem chi tiết' }}
                                </a-button>
                                <a v-else :href="route('admins.students.result', { user_id: record.id, class_id: class_id, tab_id:1, result_learn:1})">
                                    <span class="ant-btn ant-btn-primary mt-6">Chi tiết luyện tập</span>
                                </a>
                            </template>

                            <template v-if="column.key === 'expand'">
                                <a-button
                                    type="primary"
                                    @click="expandedRowKeys.includes(record.id) ? expandedRowKeys.splice(expandedRowKeys.indexOf(record.id), 1) : expandedRowKeys.push(record.id)"
                                >
                                    {{ expandedRowKeys.includes(record.id) ? '▲ Ẩn' : '▼ Xem chi tiết' }}
                                </a-button>
                            </template>

                            <template v-if="column.key === 'review'">
                                <span v-if="record.review_status === 0" class="ant-btn ant-btn-default mt-6 gray-btn color-white">Đánh giá</span>
                                <span v-else-if="record.review_status === 1" class="ant-btn ant-btn-primary mt-6">Đạt</span>
                                <span v-else class=" ant-btn ant-btn-red mt-6 gray-btn color-white">Không đạt</span>
                            </template>
                        </template>

                        <template #expandedRowRender="{ record }" v-if="tab_id == 1">
                            <div class="practice-details p-4 bg-gray-50">
                                <h4 class="font-bold mb-3">Chi tiết luyện tập của {{ record.name }}</h4>
                                <a-table
                                    :columns="[
                                        { title: 'STT', key: 'stt', width: 60, align: 'center' },
                                        { title: 'Tên bài luyện tập', dataIndex: 'name', key: 'name' },
                                        { title: 'Thành tích (Sao)', dataIndex: 'star_count', key: 'star_count', width: 150, align: 'center' },
                                        { title: 'Thời gian', dataIndex: 'time_text', key: 'time_text', width: 120, align: 'center' }
                                    ]"
                                    :data-source="record.best_points_per_practice.map((item, idx) => ({
                                        ...item,
                                        stt: idx + 1,
                                        star_count: item.star_count || 0,
                                        time_text: item.type === 1 && item.time ? formatTimeToMinutesSeconds(item.time) : '-',
                                        name: item.practice_name || 'Không xác định'
                                    }))"
                                    :pagination="false"
                                    size="small"
                                    rowKey="id"
                                />
                            </div>
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
.ant-dropdown-menu-submenu-title {
    display: flex !important;
    align-items: center !important;
}

.ant-dropdown-menu-submenu-expand-icon {
    display: flex;
    align-items: center;
    margin-left: auto;
}
.gray-btn {
  background-color: #d9d9d9;
  border: 1px solid #d9d9d9;
}
.ant-btn-orange {
    background-color: #ff7012;
}
.color-white {
    color:#ffffff;
}
.ant-btn-red {
    background-color:red
}
.text-name {
    float: right;
    font-size: 20px;
    margin-top: -40px;
}
.ant-btn {
    font-size: 14px;
    height: 32px;
    padding: 4px 15px;
    border-radius: 6px;
}
.ant-btn-primary {
    color: #fff;
    background-color: #1677ff;
    box-shadow: 0 2px 0 rgba(5, 145, 255, 0.1);
}
</style>

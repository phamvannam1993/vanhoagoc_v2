<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import {ref, onMounted, nextTick} from 'vue';
import {Link} from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";

const page = usePage();
const user = page.props?.auth?.user;
const query = page.props?.query;
const title =  page.props.title;
const class_id = query.class_id;
const user_id = query.user_id;
const app_id = page.props.app_id;
const practice_id = page.props.practice_id;
const tab_id = query.tab_id ?? 2;
let columns = [
    {
        title: 'Xếp hạng',
        dataIndex: 'rank',
        key: 'rank',
        width: '10%',
        align: 'center',
    },
    {
        title: 'Họ và tên',
        dataIndex: 'name',
        key: 'name',
    },
    {
        title: 'Điểm(Sao)',
        dataIndex: 'point_text',
        key: 'point_text',
        width: '20%',
    },
    {
        title: 'Số câu đúng',
        key: 'correct_answers_text',
        dataIndex: 'correct_answers_text',
        width: '10%',
    },
    {
        title: 'Thời gian làm',
        key: 'time_text',
        dataIndex: 'time_text',
        width: '10%',
    },
    {
        title: '',
        key: 'detail',
        dataIndex: 'detail',
        width: '10%',
    },
];
if(tab_id == 1) {
    columns = [
        {
            title: 'STT',
            dataIndex: 'id',
            key: 'id',
            width: '10%',
            align: 'center',
        },
        {
            title: 'Họ và tên',
            dataIndex: 'name',
            key: 'name',
            width: 80,
            align: 'center',
        },
        {
            title: 'Thành tích(Sao)',
            key: 'star_count',
            dataIndex: 'star_count',
            width: '10%',
        },
        {
            title: 'Thời gian làm',
            key: 'time_text',
            dataIndex: 'time_text',
            width: '10%',
        },
        {
            title: '',
            key: 'result',
            dataIndex: 'result',
            width: '10%',
        },
    ];
}

const pagination = ref({
    current: 1,
    pageSize: 1,
    total: 0,
});
const data = ref([]);
const formFilter = ref({
    search: '',
    user_id:user_id,
    class_id: class_id,
    practice_id:practice_id,
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
        practice_id: formFilter.value.practice_id,
        type: formFilter.value.type
    };
    let res = null;
    if(tab_id == 1) {
        res = await axios.get(route('admins.points.json.getResultByClass', params));
    } else {
        res = await axios.get(route('admins.class.json.rankAssignedTask', params));
    }
    if (res.data.status) {
        if(tab_id == 2) {
            data.value = res.data.data.map((v) => {
                return {
                    name: v.name,
                    point_text: v.point_text,
                    correct_answers_text: v?.correct_answers_text,
                    time_text:v.time_text,
                    rank:v.rank,
                    point_id:v.point_id,
                    user_id:v.user_id
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
                    practice_id: v.best_points_per_practice ?? v.best_points_per_practice[0].practice_id,
                    type:formFilter.value.type,
                    time_text: totalTimeInSeconds > 0 ? formatTimeToMinutesSeconds(totalTimeInSeconds) : ''
                };
            });
        }

    }
};
function formatTimeToMinutesSeconds(time) {
    const minutes = Math.floor(time / 60);
    const seconds = Math.round(time % 60);
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

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
                <h1 class="text-[20px] font-bold text-[#2C75E3]">{{title}}</h1>
                <div class="flex items-center gap-3 mt-6">
                    <Link
                        :href="practice_id > 0 ? route('admins.class.resultLearn', { class_id: class_id }) : route('admins.class.index', { app_id: app_id })"
                    >
                        <a-button class="gray-btn" @click="() => window.history.back()">Quay lại</a-button>
                    </Link>
                    <a v-if="tab_id == 2"
                        :href="route('admins.class.exportRank', { class_id: class_id, practice_id: practice_id })"
                    >
                        <a-button type="primary">Xuất báo cáo</a-button>
                    </a>
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
                    >
                        <template #bodyCell="{ column, record, index }">
                            <template v-if="column.key === 'id'">
                                {{ index + 1 + pagination.pageSize * (pagination.current - 1) }}
                            </template>
                            <template v-if="column.key === 'detail' && record.point_id > 0">
                                <a  :href="route('admins.students.pointDetail', { user_id: record.user_id, class_id: class_id, tab_id:2, point_id: record.point_id, practice_id:practice_id})"><span class=" ant-btn ant-btn-primary mt-6">Chi tiết kết quả</span></a>
                            </template>

                            <template v-if="column.key === 'review'">
                                <span v-if="record.review_status === 0" class="ant-btn ant-btn-default mt-6 gray-btn color-white">Đánh giá</span>
                                <span v-else-if="record.review_status === 1" class="ant-btn ant-btn-primary mt-6">Đạt</span>
                                <span v-else class=" ant-btn ant-btn-red mt-6 gray-btn color-white">Không đạt</span>
                            </template>
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

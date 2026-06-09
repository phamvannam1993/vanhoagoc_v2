<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import {SearchOutlined} from '@ant-design/icons-vue';
import {ref, onMounted, nextTick} from 'vue';
import {Link} from '@inertiajs/vue3';
import {useToast} from 'vue-toastification';
import Sortable from 'sortablejs';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";

const props = defineProps({
    practice: {
        type: Object,
    },
});
const toast = useToast();
const page = usePage();
const user = page.props.auth.user;
const showAdminMenu = !!user.user_type_id && user.user_type_id == 2;
const query = page.props.query;
const app_id = query.app_id;
const practice_id = query.practice_id;
const class_id = query.class_id;
const columns = [
    {
        title: 'STT',
        dataIndex: 'id',
        key: 'id',
        width: 80,
        align: 'center',
    },
    {
        title: 'Họ và tên',
        dataIndex: 'name',
        key: 'name',
    },
    {
        title: 'Thời gian giao',
        dataIndex: 'created_assign',
        key: 'created_assign',
    },
    {
        title: 'Trạng thái',
        key: 'status',
        dataIndex: 'status',
    },
    {
        title: 'Thành tích (Sao)',
        key: 'star_count',
        dataIndex: 'star_count',
        sorter: (a, b) => a.star_count - b.star_count,
        sortDirections: ['ascend', 'descend'],
        defaultSortOrder: 'descend',
    },
    {
        title: 'Ngày hoàn thành',
        key: 'created_complete',
        dataIndex: 'created_complete',
    },
    {
        title: 'Thời gian làm bài',
        key: 'time',
        dataIndex: 'time',
    },
];

const pagination = ref({
    current: 1,
    pageSize: 1,
    total: 0,
});
const data = ref([]);
const formFilter = ref({
    practice_id: practice_id
});
const tableRef = ref(null);
onMounted(async () => {
    await loadData();
});
function formatTimeToMinutesSeconds(time) {
    const minutes = Math.floor(time / 60);
    const seconds = Math.round(time % 60);
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}
const loadData = async () => {
    const params = {
        page: pagination.value.current,
        practice_id: formFilter.value.practice_id,
        class_id: class_id
    };
    const res = await axios.get(route('admins.points.json.getResultByPractice', params));

    if (res.data.status) {
        data.value = res.data.data.data.map((v) => {
            return {
                id: v.id,
                name: v.name,
                created_assign: v?.student_class?.practice_class[0].created_at,
                status: v?.best_point !== null,
                star_count: v?.best_point ? v?.best_point?.star_count : '',
                created_complete: v?.best_point ? v?.best_point?.created_at : '',
                time: v?.best_point ? formatTimeToMinutesSeconds(v?.best_point?.time) : ''
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
const goBack = () => {
    window.location = route('admins.practices.index', { class_id: class_id });
};
</script>

<template>
    <Head title="Result"/>

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Result</h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-11/12">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Kết quả</h1>
                <div class="flex items-center mt-4">
                    <a-button @click="goBack" class="custom-bg text-black" size="large">
                        Quay lại
                    </a-button>
                    <div class="ml-auto text-[#2C75E3] font-semibold">
                        {{ props.practice.book.title }} / {{ props.practice.week.name }} / {{ props.practice.name }}
                    </div>
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
                            <template v-if="column.key === 'name'">
                                <div class="flex gap-2">
                                    <p
                                        :class="`flex items-center font-bold ${record.status === 'off' ? 'hide-class' : ''}`"
                                    >
                                        {{ record.name }}
                                    </p>
                                </div>
                            </template>
                            <template v-if="column.key === 'status'">
                                <div class="flex gap-2">
                                   <span class="text-[#009C2F] font-semibold" v-if="record.status">Hoàn thành</span>
                                   <span v-else class="text-[#E20000] font-semibold">Chưa hoàn thành</span>
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

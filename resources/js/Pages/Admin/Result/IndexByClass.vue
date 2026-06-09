<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import {SearchOutlined} from '@ant-design/icons-vue';
import { ref, onMounted, nextTick, computed } from "vue";
import {Link} from '@inertiajs/vue3';
import {useToast} from 'vue-toastification';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";

const props = defineProps({
    classes: {
        type: Object,
    },
});
const toast = useToast();
const page = usePage();
const user = page.props.auth.user;
const showAdminMenu = !!user.user_type_id && user.user_type_id == 2;
const query = page.props.query;
const app_id = query.app_id;
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
        title: 'Thành tích (Sao)',
        key: 'star_count',
        dataIndex: 'star_count',
        sorter: (a, b) => a.star_count - b.star_count,
        sortDirections: ['ascend', 'descend'],
        defaultSortOrder: 'descend',
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
    class_id: class_id,
    type: -1
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
const totalStarCount = computed(() => {
    return student.best_points_per_practice.reduce((sum, item) => sum + (item.star_count || 0), 0);
});

const totalTime = computed(() => {
    return student.best_points_per_practice.reduce((sum, item) => sum + (item.time || 0), 0);
});
const loadData = async () => {
    const params = {
        page: pagination.value.current,
        class_id: formFilter.value.class_id,
        type: formFilter.value.type
    };
    const res = await axios.get(route('admins.points.json.getResultByClass', params));

    if (res.data.status) {
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
                time: totalTimeInSeconds > 0 ? formatTimeToMinutesSeconds(totalTimeInSeconds) : ''
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
const goBack = () => {
    window.location = route('admins.class.index', { app_id: app_id });
};
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
const filterOption = (input, option) => {
    return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};
const handleChangeType = (value) => {
    formFilter.value.type = value;
    loadData();
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
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Kết quả: {{ props.classes.name }}</h1>
                <div class="flex items-center mt-4">
                    <a-button @click="goBack" class="custom-bg text-black" size="large">
                        Quay lại
                    </a-button>
                </div>
                <div class="filter-page mt-4 flex gap-10">
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

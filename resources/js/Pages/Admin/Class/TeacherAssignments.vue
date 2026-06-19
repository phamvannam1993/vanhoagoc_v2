<script setup>
import { Head } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { onMounted, ref } from "vue";

const props = defineProps({
    teacher_id: {
        type: Number,
        required: true
    },
    teacher_name: {
        type: String,
        required: true
    },
    query: {
        type: Object,
        default: () => ({})
    },
});

const goBack = () => {
    if (typeof window !== 'undefined' && window.history) {
        window.history.back();
    }
};

const columns = [
    {
        title: 'STT',
        dataIndex: 'id',
        key: 'id',
        align: 'center',
        width: '4%'
    },
    {
        title: 'Khóa học',
        dataIndex: 'book',
        key: 'book',
        width: '15%'
    },
    {
        title: 'Bài',
        dataIndex: 'week',
        key: 'week',
        width: '15%'
    },
    {
        title: 'Bài tập',
        dataIndex: 'practice',
        key: 'practice',
        width: '20%'
    },
    {
        title: 'Lớp',
        dataIndex: 'class',
        key: 'class',
        width: '15%'
    },
    {
        title: 'Từ ngày',
        dataIndex: 'from',
        key: 'from',
        width: '10%'
    },
    {
        title: 'Đến ngày',
        dataIndex: 'to',
        key: 'to',
        width: '10%'
    },
    {
        title: 'Ngày giao',
        dataIndex: 'created_at',
        key: 'created_at',
        width: '11%'
    }
];

const pagination = ref({
    current: 1,
    pageSize: 20,
    total: 0
});

const data = ref([]);
const loading = ref(false);

onMounted(() => {
    loadData();
});

const loadData = async () => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value.current,
            teacher_id: props.teacher_id,
            pageSize: pagination.value.pageSize
        };
        const res = await axios.get(route('admins.class.json.teacherAssignments', params));
        if (res.data.status) {
            data.value = res.data.data.map((item, index) => ({
                id: index + 1 + pagination.value.pageSize * (pagination.value.current - 1),
                book: item.book?.name || '-',
                week: item.week?.name || '-',
                practice: item.practice?.name || '-',
                class: item.class?.name || '-',
                from: item.from || '-',
                to: item.to || '-',
                created_at: item.created_at || '-'
            }));
            pagination.value.pageSize = res.data.per_page;
            pagination.value.total = res.data.total;
            pagination.value.current = res.data.current_page;
        }
    } catch (error) {
        console.error('Error loading teacher assignments:', error);
    } finally {
        loading.value = false;
    }
};

const onPageChange = (page) => {
    pagination.value.current = page;
    loadData();
};
</script>

<template>
    <Head :title="`Danh sách giao bài - ${teacher_name}`" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Danh sách giao bài - {{ teacher_name }}
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-full sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Danh sách bài tập đã giao - {{ teacher_name }}
                </h1>
                <div class="relative mt-6 flex gap-4">
                    <a-button class="custom-bg mt-3 text-black" size="middle" @click="goBack">Quay lại</a-button>
                </div>
                <div class="mt-8 w-full">
                    <a-table
                        :columns="columns"
                        :data-source="data"
                        :loading="loading"
                        :pagination="false"
                        bordered
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.key === 'id'">
                                {{ record.id }}
                            </template>
                        </template>
                        <template #footer>
                            <div class="flex items-center justify-end">
                                <a-pagination
                                    v-bind="pagination"
                                    @change="onPageChange"
                                    :show-size-changer="false"
                                />
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

.ant-table {
    font-size: 13px;

    :deep(.ant-table-thead > tr > th) {
        padding: 8px 4px !important;
    }

    :deep(.ant-table-tbody > tr > td) {
        padding: 8px 4px !important;
        word-break: break-word;
        white-space: normal;
    }
}
</style>

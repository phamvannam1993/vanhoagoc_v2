<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { SearchOutlined } from '@ant-design/icons-vue';
import { ref, onMounted } from 'vue';
import { useToast } from 'vue-toastification';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";

const toast = useToast();
const page = usePage();
const query = page.props.query;

const columns = [
    { title: 'STT', key: 'stt', width: 80, align: 'center' },
    { title: 'Tên phẩm chất năng lực', dataIndex: 'name', key: 'name' },
    { title: 'Mã', dataIndex: 'code', key: 'code', width: 150 },
    { title: 'Thành phần NL', key: 'components', width: 160, align: 'center' },
    { title: 'Sửa', key: 'edit', width: 80, align: 'center' },
    { title: 'Xóa', key: 'delete', width: 80, align: 'center' },
];

const pagination = ref({ current: 1, pageSize: 20, total: 0 });
const data = ref([]);
const formFilter = ref({ search: '' });
const loading = ref(false);

onMounted(loadData);

async function loadData() {
    loading.value = true;
    const res = await axios.get(route('admins.competencies.json.list', {
        page: pagination.value.current,
        search: formFilter.value.search,
    }));
    if (res.data.status) {
        data.value = res.data.data.data;
        pagination.value.pageSize = res.data.data.per_page;
        pagination.value.total = res.data.data.total;
        pagination.value.current = res.data.data.current_page;
    }
    loading.value = false;
}

function onPageChange(p) {
    pagination.value.current = p;
    loadData();
}

function onSearch() {
    pagination.value.current = 1;
    loadData();
}

function removeFilter() {
    formFilter.value.search = '';
    loadData();
}

async function deleteItem(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa?')) return;
    const res = await axios.delete(route('admins.competencies.json.delete', id));
    if (res.data.status) {
        toast.success('Xóa thành công');
        loadData();
    } else {
        toast.error('Có lỗi xảy ra');
    }
}
</script>

<template>
    <Head title="Quản lý PCNL" />
    <SchoolLayout>
        <div class="app-page py-4">
            <div class="content-page mx-auto w-full md:w-10/12">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Quản lý Phẩm chất Năng lực (PCNL)</h1>
                <div class="filter-page mt-4 flex gap-3 items-center">
                    <a-input
                        placeholder="Tìm kiếm"
                        allow-clear
                        style="width: 30rem"
                        v-model:value="formFilter.search"
                        @pressEnter="onSearch"
                    >
                        <template #suffix>
                            <SearchOutlined @click="onSearch" style="cursor: pointer" />
                        </template>
                    </a-input>
                    <a-button @click="removeFilter" size="large">Xóa lọc</a-button>
                    <Link :href="route('admins.competencies.create')">
                        <a-button type="primary" size="large">+ Thêm mới</a-button>
                    </Link>
                </div>
                <div class="mt-4">
                    <a-table
                        :columns="columns"
                        :data-source="data"
                        :pagination="false"
                        :loading="loading"
                        rowKey="id"
                        :scroll="{ x: 'max-content' }"
                    >
                        <template #bodyCell="{ column, record, index }">
                            <template v-if="column.key === 'stt'">
                                {{ index + 1 + pagination.pageSize * (pagination.current - 1) }}
                            </template>
                            <template v-if="column.key === 'components'">
                                <Link :href="route('admins.competency-components.index', { competency_id: record.id })">
                                    <a-button type="primary" size="small">Xem</a-button>
                                </Link>
                            </template>
                            <template v-if="column.key === 'edit'">
                                <Link :href="route('admins.competencies.edit', { id: record.id })">
                                    <a-button size="small">Sửa</a-button>
                                </Link>
                            </template>
                            <template v-if="column.key === 'delete'">
                                <a-button danger size="small" @click="deleteItem(record.id)">Xóa</a-button>
                            </template>
                        </template>
                        <template #footer>
                            <div class="flex items-center justify-end">
                                <a-pagination v-bind="pagination" @change="onPageChange" />
                            </div>
                        </template>
                    </a-table>
                </div>
            </div>
        </div>
    </SchoolLayout>
</template>

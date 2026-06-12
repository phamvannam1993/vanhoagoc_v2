<script setup>
import { ref, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { SearchOutlined } from '@ant-design/icons-vue';
import { useToast } from 'vue-toastification';
import { USER_TYPE_ADMIN, USER_TYPE_DIRECTOR, USER_TYPE_TEACHER } from "@/const.js";

const props = defineProps({
    // 'assign' = Giao bài, 'result' = Kết quả
    mode: { type: String, default: 'assign' },
    // app_id của giáo viên (controller truyền vào); admin/giám đốc để trống → hiện bộ chọn Đơn vị
    appId: { type: [String, Number], default: '' },
});

const toast = useToast();
const page = usePage();
const user = page.props.auth.user;
const userType = user.user_type.type;
const isTeacher = userType === USER_TYPE_TEACHER;

const apps = ref([]);
const selectedAppId = ref(props.appId || null);
const search = ref('');
const data = ref([]);
const loading = ref(false);
const pagination = ref({ current: 1, pageSize: 1, total: 0 });

const columns = [
    { title: 'STT', dataIndex: 'id', key: 'stt', width: 80, align: 'center' },
    { title: 'Tên Phòng ban/Lớp', dataIndex: 'name', key: 'name', width: '45%' },
    { title: props.mode === 'assign' ? 'Giao bài' : 'Kết quả học tập', key: 'action' },
];

onMounted(async () => {
    if (isTeacher) {
        selectedAppId.value = props.appId || null;
        await loadClasses();
    } else {
        await loadApps();
    }
});

const loadApps = async () => {
    try {
        const res = await axios.get(route('admins.school.json.list', { page: 1, search: '' }));
        if (res.data.status) {
            apps.value = (res.data.data.data || []).map(v => ({ value: v.id, label: v.name }));
        }
    } catch (e) {
        toast.error('Không tải được danh sách Đơn vị!');
    }
};

const onAppChange = () => {
    pagination.value.current = 1;
    loadClasses();
};

const loadClasses = async () => {
    if (!selectedAppId.value) {
        data.value = [];
        return;
    }
    loading.value = true;
    try {
        const params = { page: pagination.value.current, search: search.value };
        if (selectedAppId.value) params.app_id = selectedAppId.value;
        const res = await axios.get(route('admins.class.json.list', params));
        if (res.data.status) {
            const rd = res.data.data;
            data.value = (rd.data || []).map(v => ({
                id: v.id,
                app_id: v.app_id,
                name: v.name,
                users_count: v.users_count ?? 0,
                is_result: v.is_result,
                isAssign: (v.practice_class ?? []).length > 0,
            }));
            pagination.value.pageSize = rd.per_page;
            pagination.value.total = rd.total;
            pagination.value.current = rd.current_page;
        }
    } catch (e) {
        toast.error('Không tải được danh sách Phòng ban/Lớp!');
    } finally {
        loading.value = false;
    }
};

// Đích điều hướng cho cả tên lớp lẫn nút hành động
const actionHref = (r) => {
    if (props.mode === 'assign') {
        return r.isAssign
            ? route('admins.class.assignment', { class_id: r.id })
            : route('admins.practices.index', { class_id: r.id });
    }
    return r.is_result ? route('admins.class.resultLearn', { class_id: r.id }) : null;
};

const onPageChange = (p) => { pagination.value.current = p; loadClasses(); };
const onSearch = () => { pagination.value.current = 1; loadClasses(); };
const removeFilter = () => { search.value = ''; onSearch(); };
</script>

<template>
    <div class="app-page py-4">
        <div class="content-page mx-auto w-full md:w-11/12">
            <h1 class="text-[30px] font-bold text-[#2C75E3]">
                {{ props.mode === 'assign' ? 'Giao bài' : 'Kết quả học tập' }}
            </h1>
            <p class="text-gray-500 mt-1">
                {{ props.mode === 'assign'
                    ? 'Chọn Đơn vị và Lớp để giao bài luyện tập.'
                    : 'Chọn Đơn vị và Lớp để xem kết quả học tập.' }}
            </p>

            <div class="filter-page mt-6 flex flex-wrap gap-5 items-center">
                <a-select
                    v-if="!isTeacher"
                    v-model:value="selectedAppId"
                    :options="apps"
                    show-search
                    option-filter-prop="label"
                    placeholder="Chọn Đơn vị/Trường học"
                    style="width: 22rem"
                    size="large"
                    @change="onAppChange"
                />
                <a-input
                    placeholder="Tìm kiếm lớp"
                    :allow-clear="true"
                    style="width: 26rem"
                    v-model:value="search"
                >
                    <template #suffix>
                        <SearchOutlined @click="onSearch" style="cursor: pointer" />
                    </template>
                </a-input>
                <a-button @click="removeFilter" size="large">Xóa lọc</a-button>
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
                    <template #emptyText>
                        <div class="py-6 text-gray-400">
                            {{ !isTeacher && !selectedAppId ? 'Vui lòng chọn Đơn vị/Trường học' : 'Không có lớp nào' }}
                        </div>
                    </template>
                    <template #bodyCell="{ column, record, index }">
                        <template v-if="column.key === 'stt'">
                            {{ index + 1 + pagination.pageSize * (pagination.current - 1) }}
                        </template>
                        <template v-if="column.key === 'name'">
                            <Link v-if="actionHref(record)" :href="actionHref(record)" class="font-bold text-[#2C75E3] hover:underline">
                                {{ record.name }} ({{ record.users_count }})
                            </Link>
                            <span v-else class="font-bold">{{ record.name }} ({{ record.users_count }})</span>
                        </template>
                        <template v-if="column.key === 'action'">
                            <!-- Giao bài -->
                            <template v-if="props.mode === 'assign'">
                                <Link v-if="!record.isAssign" :href="route('admins.practices.index', { class_id: record.id })">
                                    <a-button class="bg-[#b1b1b1] text-white" size="large">Giao bài</a-button>
                                </Link>
                                <Link v-else :href="route('admins.class.assignment', { class_id: record.id })">
                                    <a-button type="primary" size="large">Đã giao — Xem/Sửa</a-button>
                                </Link>
                            </template>
                            <!-- Kết quả -->
                            <template v-else>
                                <a-button v-if="!record.is_result" class="bg-[#b1b1b1] text-white" size="large" disabled>
                                    Chưa có kết quả
                                </a-button>
                                <Link v-else :href="route('admins.class.resultLearn', { class_id: record.id })">
                                    <a-button type="primary" size="large">Xem kết quả</a-button>
                                </Link>
                            </template>
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
</template>

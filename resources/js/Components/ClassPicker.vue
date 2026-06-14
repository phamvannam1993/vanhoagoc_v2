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
const classSearch = ref('');
const data = ref([]);
const loading = ref(false);
const pagination = ref({ current: 1, pageSize: 1, total: 0 });
const activeTab = ref('class');
const studentAppId = ref(null);
const studentSearch = ref('');
const selectedClass = ref(null);
const studentData = ref([]);
const studentPagination = ref({ current: 1, pageSize: 10, total: 0 });
const studentLoading = ref(false);

const columns = [
    { title: 'STT', dataIndex: 'id', key: 'stt', width: 80, align: 'center' },
    { title: 'Tên Phòng ban/Lớp', dataIndex: 'name', key: 'name', width: '40%' },
    props.mode === 'assign'
        ? { title: 'Giao bài', key: 'action', width: '20%' }
        : { title: 'Kết quả học tập', key: 'result_summary', width: '25%', align: 'center' },
    { title: props.mode === 'assign' ? '' : 'Chi tiết', key: 'action', width: '15%', align: 'center' }
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
        const res = await axios.get('/admins/school/json/list?page=1&search=');
        console.log('API response:', res.data);
        if (res.data.status) {
            const appList = res.data.data.data || [];
            console.log('Apps from API:', appList);
            apps.value = appList.map(v => ({ value: v.id, label: v.name }));
            console.log('Mapped apps:', apps.value);

            // Auto-select first app and load classes
            if (appList.length > 0) {
                selectedAppId.value = appList[0].id;
                await loadClasses();
            }
        } else {
            console.error('API error:', res.data.message);
            toast.error(res.data.message || 'Không tải được danh sách Đơn vị!');
        }
    } catch (e) {
        console.error('Request error:', e);
        toast.error('Không tải được danh sách Đơn vị!');
    }
};

const onAppChange = async () => {
    pagination.value.current = 1;
    studentPagination.value.current = 1;
    selectedClass.value = null;
    studentSearch.value = '';
    await loadClasses();
    if (activeTab.value === 'student') {
        await loadStudents();
    }
};

const loadClasses = async () => {
    if (!selectedAppId.value) {
        data.value = [];
        return;
    }
    loading.value = true;
    try {
        const params = { page: pagination.value.current, search: classSearch.value };
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
const removeFilter = () => { classSearch.value = ''; onSearch(); };

const onStudentAppChange = async () => {
    studentPagination.value.current = 1;
    selectedClass.value = null;
    studentSearch.value = '';
    if (studentAppId.value) {
        await loadClasses();
    } else {
        data.value = [];
    }
    await loadStudents();
};

const loadStudents = async () => {
    studentLoading.value = true;
    try {
        const params = {
            page: studentPagination.value.current,
            per_page: studentPagination.value.pageSize,
            search: studentSearch.value
        };
        if (studentAppId.value) params.app_id = studentAppId.value;
        if (selectedClass.value) params.class_id = selectedClass.value;
        const res = await axios.get(route('admins.students.json.resultSummaryByApp', params));
        if (res.data.status) {
            const rd = res.data.data;
            studentData.value = (rd.data || []).map(v => ({
                id: v.id,
                name: v.name,
                username: v.username,
                email: v.email,
                phone: v.phone,
                class_id: v.class_id,
                class_name: v.class_name,
                formatted_created_at: v.formatted_created_at,
                total_points: v.total_points ?? 0,
                total_score: v.total_score ?? 0,
                is_result: v.is_result
            }));
            studentPagination.value.pageSize = rd.per_page;
            studentPagination.value.total = rd.total;
            studentPagination.value.current = rd.current_page;
        }
    } catch (e) {
        toast.error('Không tải được danh sách học sinh!');
    } finally {
        studentLoading.value = false;
    }
};

const onTabChange = async (key) => {
    activeTab.value = key;
    if (key === 'student') {
        // Reset student filters
        studentAppId.value = null;
        selectedClass.value = null;
        studentSearch.value = '';
        studentPagination.value.current = 1;
        data.value = [];
        // Load all students (without app filter)
        await loadStudents();
    }
};

const onStudentPageChange = (p) => {
    studentPagination.value.current = p;
    loadStudents();
};

const onStudentSearch = () => {
    studentPagination.value.current = 1;
    loadStudents();
};

const onClassFilterChange = () => {
    studentPagination.value.current = 1;
    loadStudents();
};
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

            <a-tabs v-model:activeKey="activeTab" @change="onTabChange" style="margin-top: 1.5rem">
                <a-tab-pane key="class" tab="Phòng ban/Lớp">
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
                            v-model:value="classSearch"
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
                            <span class="font-bold">{{ record.name }} ({{ record.users_count }})</span>
                        </template>
                        <template v-if="column.key === 'result_summary'">
                            <div v-if="record.is_result" class="text-sm text-center">
                                <div class="font-semibold">{{ record.users_count }} học sinh</div>
                                <div class="text-gray-500 text-xs">Có kết quả</div>
                            </div>
                            <div v-else class="text-gray-400 text-sm text-center">Chưa có kết quả</div>
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
                                <a-button v-if="!record.is_result" class="bg-[#b1b1b1] text-white" size="small" disabled>
                                    Chưa có kết quả
                                </a-button>
                                <Link v-else :href="route('admins.class.resultLearn', { class_id: record.id })">
                                    <a-button type="primary" size="small">Xem chi tiết</a-button>
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
                </a-tab-pane>

                <a-tab-pane key="student" tab="Học sinh">
                    <div class="filter-page mt-6 flex flex-wrap gap-5 items-center">
                        <a-select
                            v-if="!isTeacher"
                            v-model:value="studentAppId"
                            :options="apps"
                            show-search
                            allow-clear
                            option-filter-prop="label"
                            placeholder="Tất cả Đơn vị (mặc định)"
                            style="width: 22rem"
                            size="large"
                            @change="onStudentAppChange"
                        />
                        <a-select
                            v-model:value="selectedClass"
                            placeholder="Tất cả lớp"
                            show-search
                            allow-clear
                            style="width: 20rem"
                            size="large"
                            :options="data.length > 0 ? data.map(c => ({ label: c.name, value: c.id })) : []"
                            @change="onClassFilterChange"
                        />
                        <a-input
                            placeholder="Tìm kiếm học sinh"
                            :allow-clear="true"
                            style="width: 26rem"
                            v-model:value="studentSearch"
                        >
                            <template #suffix>
                                <SearchOutlined @click="onStudentSearch" style="cursor: pointer" />
                            </template>
                        </a-input>
                        <a-button @click="() => { studentAppId = null; studentSearch = ''; selectedClass = null; loadStudents(); }" size="large">Xóa lọc</a-button>
                    </div>

                    <div class="mt-4">
                        <a-table
                            :columns="[
                                { title: 'STT', key: 'stt', width: 60, align: 'center' },
                                { title: 'Họ tên', dataIndex: 'name', key: 'name' },
                                { title: 'Tên tài khoản', dataIndex: 'username', key: 'username' },
                                { title: 'Thời gian tạo', dataIndex: 'formatted_created_at', key: 'formatted_created_at', align: 'center' },
                                { title: 'Kết quả', key: 'result', align: 'center' }
                            ]"
                            :data-source="studentData.map((s, idx) => ({
                                ...s,
                                stt: idx + 1 + studentPagination.pageSize * (studentPagination.current - 1)
                            }))"
                            :pagination="false"
                            :loading="studentLoading"
                            rowKey="id"
                            :scroll="{ x: 'max-content' }"
                        >
                            <template #emptyText>
                                <div class="py-6 text-gray-400">
                                    {{ !isTeacher && !studentAppId ? 'Vui lòng chọn Đơn vị/Trường học' : 'Không có học sinh nào' }}
                                </div>
                            </template>
                            <template #bodyCell="{ column, record, index }">
                                <template v-if="column.key === 'stt'">
                                    {{ index + 1 + studentPagination.pageSize * (studentPagination.current - 1) }}
                                </template>
                                <template v-if="column.key === 'username'">
                                    {{ record.username || record.email || record.phone || '-' }}
                                </template>
                                <template v-if="column.key === 'result'">
                                    <a-button v-if="!record.is_result" class="bg-[#b1b1b1] text-white" size="small" disabled>
                                        Kết quả
                                    </a-button>
                                    <Link v-else :href="route('admins.students.result', {
                                        user_id: record.id,
                                        class_id: record.class_id
                                    })">
                                        <a-button type="primary" size="small">Kết quả</a-button>
                                    </Link>
                                </template>
                            </template>
                        </a-table>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                Tổng: {{ studentPagination.total }} học sinh
                            </div>
                            <a-pagination
                                :current="studentPagination.current"
                                :pageSize="studentPagination.pageSize"
                                :total="studentPagination.total"
                                :show-size-changer="true"
                                :page-size-options="['10', '20', '50']"
                                @change="onStudentPageChange"
                                @showSizeChange="(current, pageSize) => {
                                    studentPagination.pageSize = pageSize;
                                    studentPagination.current = 1;
                                    loadStudents();
                                }"
                            />
                        </div>
                    </div>
                </a-tab-pane>
            </a-tabs>
        </div>
    </div>
</template>

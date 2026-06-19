<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import { SearchOutlined } from "@ant-design/icons-vue";
import { ref, onMounted, nextTick, reactive } from "vue";
import { Link } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { h } from "vue";
import * as yup from "yup";
import dayjs from "dayjs";
import { Progress as AProgress } from 'ant-design-vue'
import clsx from 'clsx';

const toast = useToast();
const page = usePage();
const user = page.props.auth.user;
const showAdminMenu = !!user.user_type_id && user.user_type_id == 2;
const query = page.props.query;
const app_id = query.appId;
const class_id = ref(query.class_id || null);
const student_id = page.props.student_id ?? null;
const student_name = page.props.student_name ?? null;
const columns = [
    {
        title: "STT",
        dataIndex: "id",
        key: "id",
        align: "center",
        width: "50px",
        customRender: ({ record, index }) => {
            // Only show STT for non-exercise items
            if (record.level_type !== "exercise-item") {
                return h("span", { class: "text-gray-600" }, index + 1 + pagination.pageSize * (pagination.current - 1));
            }
            // Don't render anything for exercise items
            return null;
        }
    },
    {
        title: "Nội dung",
        dataIndex: "name",
        key: "name",
        customRender: ({ record }) => {
            // Indent exercise items
            if (record.level_type === "exercise-item") {
                return h("div", { class: "pl-8 py-3 font-medium text-gray-800 break-words" }, record.name);
            }
            return h("div", { class: "py-3 font-semibold text-gray-900 break-words" }, record.name);
        }
    },
    {
        title: "Giao bài",
        dataIndex: "assign",
        key: "assign",
        align: "center",
        width: "150px",
        customRender: ({ record }) => {
            // For exercise items - show button with assigned status
            if (record.level_type === "exercise-item") {
                const isAssigned = assignedItems.value.has(record.id);

                let buttonText = isAssigned ? "Đã giao" : "Giao bài";
                let buttonClass = isAssigned
                    ? "px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
                    : "px-4 py-2 bg-gray-400 text-white text-sm font-semibold rounded-lg hover:bg-gray-500 transition-colors shadow-sm";

                return h(
                    "button",
                    {
                        class: buttonClass,
                        onClick: () => handleAssignItem(record)
                    },
                    buttonText
                );
            }

            // For practices - show assigned/not-assigned image
            if (record.level === "practice") {
                const imageSrc = record.assign ? "/images/Teacher/assigned.png" : "/images/Teacher/not_assigned.png";
                return h(
                    "img",
                    {
                        src: imageSrc,
                        alt: record.assign ? "Đã giao" : "Giao bài",
                        style: {
                            cursor: "pointer",
                            width: "70px",
                            height: "38px",
                        },
                        onClick: () => handleAssign(record)
                    }
                );
            }

            return null;
        }
    },
    {
        title: "Thu hồi",
        dataIndex: "withdraw",
        key: "withdraw",
        customRender: ({ record }) => {
            // Withdraw button for practice
            if (record.level === "practice" && record.assign) {
                const imageSrc = "/images/Teacher/with_draw.png";
                return h(
                    "img",
                    {
                        src: imageSrc,
                        style: {
                            cursor: "pointer",
                            width: "70px",
                            height: "38px",
                        },
                        onClick: () => handleWithdraw(record)
                    }
                );
            }

            // Withdraw button for exercise item
            if (record.level_type === "exercise-item" && assignedItems.value.has(record.id)) {
                return h(
                    "button",
                    {
                        class: "px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded hover:bg-red-600 transition-colors",
                        onClick: () => handleWithdrawExerciseItem(record)
                    },
                    "Hủy giao"
                );
            }

            return null;
        }
    },
    {
        title: "Tình trạng",
        dataIndex: "status",
        key: "status",
        customRender: ({ record }) => {
            if (record.level === "practice") {
                const done = record.practicePoints.length ?? 0
                const total = record?.practice_classes.length > 0 ? record?.practice_classes[0].users.length : 0;
                const percent = total > 0 ? (done / total) * 100 : 0

                return h(AProgress, {
                    percent,
                    format: () => `${done} / ${total}`,
                })
            }
            return null;
        }
    },
    {
        title: "Kết quả",
        dataIndex: "result",
        key: "result",
        customRender: ({ record }) => {
            if (record.level === "practice") {
                const imageSrc = record.assign ? "/images/Teacher/result.png" : "/images/Teacher/not_result.png";
                return h("img", {
                    src: imageSrc,
                    alt: "Kết quả",
                    style: {
                        cursor: "pointer",
                        width: "70px",
                        height: "38px",
                    },
                    onClick: () => {
                        window.location.href = route('admins.points.getResultPractice', {
                            practice_id: record.id,
                            class_id: class_id.value
                        });
                    }
                });
            }
            return null;
        }
    }
];
const openAssign = ref(false);
const exerciseItems = ref([]);
const selectedItems = ref([]);
const loadingItems = ref(false);

const formAssign = ref({
    checkedTime: false,
    checkedNonTime: false,
    limitTime: null,
    practice_id: null,
    book_id: null,
    week_id: null,
    showConfirm: true
});
const formAssignErrors = ref({});

const handleAssignItem = async (item) => {
    // Load all exercise items for this practice
    openAssign.value = true;
    loadingItems.value = true;

    try {
        const itemRes = await axios.get(route('admins.practices.json.getExerciseItems'), {
            params: { practice_id: item.practice_id, student_id: student_id }
        });

        if (itemRes.data.status && itemRes.data.data?.length > 0) {
            // Map items with checkbox: pre-check the clicked item and unassigned items
            exerciseItems.value = itemRes.data.data.map(ex => ({
                ...ex,
                checked: ex.id === item.id && !assignedItems.value.has(ex.id)
            }));
        }
    } catch (err) {
        console.error('Error loading items:', err);
        toast.error('Lỗi tải bài tập con');
        openAssign.value = false;
        return;
    } finally {
        loadingItems.value = false;
    }

    formAssign.value = {
        checkedTime: false,
        checkedNonTime: false,
        limitTime: null,
        practice_id: item.practice_id,
        book_id: item.book_id,
        week_id: item.week_id,
        showConfirm: true
    };

    formAssignErrors.value = {};
};
const handleWithdraw = async (record) => {
    if (record.infoAssign.length > 0) {
        const form = {
            practice_id: record.id,
            class_id: class_id,
            student_id: student_id,
        };
        const res = await axios.post(route('admins.practices.json.withDrawPractice'), form)

        if (res.data.status) {
            openAssign.value = false;
            toast.success('Thu hồi thành công');
            await loadData();
        }
    }
}

const handleWithdrawExerciseItem = async (item) => {
    try {
        const form = {
            exercise_item_id: parseInt(item.id),
            student_id: student_id ? parseInt(student_id) : null,
            class_id: class_id.value ? parseInt(class_id.value) : null,
        };

        const res = await axios.post(route('admins.practices.json.withdrawExerciseItem'), form);

        if (res.data.status) {
            // Remove item from assigned set
            assignedItems.value.delete(item.id);
            toast.success('Hủy giao bài tập con thành công');
            // Reload to update UI
            await loadData();
        } else {
            toast.error(res.data.message || 'Lỗi hủy giao bài tập con');
        }
    } catch (err) {
        console.error('Withdraw error:', err);
        toast.error(err.response?.data?.message || err.message || 'Lỗi hủy giao bài tập con');
    }
}
const assignSchema = yup.object({
    checkedTime: yup.boolean(),
    checkedNonTime: yup.boolean(),
    limitTime: yup.mixed().when('checkedTime', {
        is: true,
        then: (schema) => schema.required('Phải chọn thời gian giới hạn'),
        otherwise: (schema) => schema.nullable(),
    }),
}).test(
    'at-least-one-checked',
    'Phải chọn ít nhất một trong hai: Có thời hạn hoặc Vô thời hạn',
    (value) => value.checkedTime || value.checkedNonTime
);
const handleClose = (record) => {
    openAssign.value = false;
};
const handleResult = (record) => {

}
const confirmAssign = async () => {
    try {
        await assignSchema.validate(formAssign.value, { abortEarly: false });

        const checkedItems = exerciseItems.value.filter(item => item.checked);

        if (checkedItems.length === 0) {
            formAssignErrors.value = { general: 'Vui lòng chọn ít nhất một bài tập con' };
            return;
        }

        const form = {
            checkedTime: formAssign.value.checkedTime,
            checkedNonTime: formAssign.value.checkedNonTime,
            practice_id: parseInt(formAssign.value.practice_id),
            book_id: formAssign.value.book_id ? parseInt(formAssign.value.book_id) : null,
            week_id: formAssign.value.week_id ? parseInt(formAssign.value.week_id) : null,
            class_id: class_id.value ? parseInt(class_id.value) : null,  // Backend will auto-detect if null
            student_id: student_id ? parseInt(student_id) : null,
            exercise_item_ids: checkedItems.map(item => parseInt(item.id)),
            from: formAssign.value.limitTime
                ? formAssign.value.limitTime[0].format('YYYY/MM/DD')
                : null,
            to: formAssign.value.limitTime
                ? formAssign.value.limitTime[1].format('YYYY/MM/DD')
                : null,
        };

        const res = await axios.post(route('admins.practices.json.assignExerciseItems'), form)

        if (res.data.status) {
            // Mark items as assigned
            checkedItems.forEach(item => {
                assignedItems.value.add(item.id);
            });

            openAssign.value = false;
            toast.success('Giao bài tập con thành công');
            // Reload data to refresh all state including practice-level assignment status
            await loadData();
        } else {
            // Handle error response from backend
            toast.error(res.data.message || 'Lỗi giao bài tập con');
        }
    } catch (err) {
        console.error('Assignment error:', err);

        // Handle axios error response (422, 500, etc.)
        if (err.response?.data?.message) {
            toast.error(err.response.data.message);
            formAssignErrors.value = { general: err.response.data.message };
        }
        // Handle validation errors (yup)
        else if (err.inner && err.inner.length > 0) {
            const errors = {};
            err.inner.forEach((e) => {
                if (e.path) {
                    errors[e.path] = e.message;
                } else {
                    errors.general = e.message;
                }
            });
            formAssignErrors.value = errors;
            toast.error(errors.general || 'Lỗi giao bài tập con');
        }
        // Handle other errors
        else {
            const message = err.message || 'Lỗi giao bài tập con';
            formAssignErrors.value = { general: message };
            toast.error(message);
        }
    }
};
const pagination = ref({
    current: 1,
    pageSize: 1,
    total: 0
});
const data = ref([]);
const formFilter = ref({
    search: ""
});
const tableRef = ref(null);
onMounted(async () => {
    await loadData();
});
const loadData = async () => {
    try {
        // Clear assigned items when reloading data
        assignedItems.value.clear();

        const params = {
            page: pagination.value.current,
            search: formFilter.value.search,
            class_id: class_id,
            student_id: student_id
        };
        console.log('Loading lessons with params:', params);
        const res = await axios.get(route("admins.practices.json.getLesson", params));

        if (!res.data.status) {
            console.error('API returned status false:', res.data);
            toast.error('Lỗi tải dữ liệu');
            return;
        }

        console.log('Lessons loaded:', res.data.data.data);

        // Get class_id from API response
        if (!class_id.value && res.data.class_id) {
            class_id.value = res.data.class_id;
            console.log('Set class_id from API:', class_id.value);
        }

        // Build the base structure without exercise items first
        const booksData = res.data.data.data.map((v) => {
            return {
                key: `book-${v.id}`,
                id: v.id,
                name: v.title,
                level: "book",
                children: (v.weeks || []).map((week) => ({
                    key: `week-${week.id}`,
                    id: week.id,
                    name: `Tuần ${week.numberWeek}: ${week.name || "Không có tên"}`,
                    level: "week",
                    children: (week.practices || []).map((practice) => {
                        return {
                            key: `practice-${practice.id}`,
                            id: practice.id,
                            week_id: practice.week_id,
                            book_id: practice.book_id,
                            name: practice.name,
                            practicePoints: practice.practice_points || [],
                            practice_classes: practice.practice_classes || [],
                            infoAssign: practice.assigned_for_user || [],
                            assign: (practice.assigned_for_user || []).length > 0,
                            withdraw: true,
                            status: practice.status,
                            result: true,
                            level: "practice",
                            children: [] // Will be populated with exercise items
                        };
                    })
                }))
            };
        });

        data.value = booksData;
        pagination.value.pageSize = res.data.data.per_page;
        pagination.value.total = res.data.data.total;
        pagination.value.current = res.data.data.current_page;

        console.log('Data loaded, booksData:', booksData);

        // Preload exercise items only for practices with assignments to avoid excessive API calls
        // Note: This only catches individual assignments (practice.assign), not class-level.
        // Class-level assignment status shows on expand.
        const practicesToLoad = [];
        let preloadCount = 0;
        const maxPreload = 5;  // Limit preloading to avoid too many API calls

        booksData.forEach(book => {
            book.children?.forEach(week => {
                week.children?.forEach(practice => {
                    // Preload practices with individual assignments, up to limit
                    if (preloadCount < maxPreload && practice.assign) {
                        practicesToLoad.push(practice);
                        preloadCount++;
                    }
                });
            });
        });

        // Load exercise items for assigned practices
        for (const practice of practicesToLoad) {
            try {
                const itemRes = await axios.get(route('admins.practices.json.getExerciseItems'), {
                    params: { practice_id: practice.id, student_id: student_id }
                });

                if (itemRes.data.status && itemRes.data.data?.length > 0) {
                    practice.children = itemRes.data.data.map((item) => {
                        if (item.is_assigned) {
                            assignedItems.value.add(item.id);
                        }
                        return {
                            key: `exercise-item-${item.id}`,
                            id: item.id,
                            name: item.name,
                            level: item.level,
                            total_questions: item.total_questions,
                            level_type: "exercise-item",
                            practice_id: practice.id,
                            week_id: practice.week_id,
                            book_id: practice.book_id,
                            is_assigned: item.is_assigned || false
                        };
                    });
                }
            } catch (err) {
                console.error('Error preloading items for practice:', practice.id, err);
            }
        }

        // Trigger final reactive update
        data.value = [...data.value];
    } catch (err) {
        console.error('Error in loadData:', err);
        toast.error('Lỗi tải dữ liệu: ' + (err.response?.data?.message || err.message));
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
    formFilter.value.search = "";
    loadData();
};

const expandedRowKeys = ref([]);
const assignedItems = ref(new Set()); // Track assigned exercise items

const handleExpand = async (expanded, record) => {
    if (expanded && record.level === 'practice' && record.children.length === 0) {
        // Load exercise items when expanding a practice
        try {
            const itemRes = await axios.get(route('admins.practices.json.getExerciseItems'), {
                params: { practice_id: record.id, student_id: student_id, class_id: class_id.value }
            });

            if (itemRes.data.status && itemRes.data.data && itemRes.data.data.length > 0) {
                record.children = itemRes.data.data.map((item) => {
                    // Add assigned item to the set
                    if (item.is_assigned) {
                        assignedItems.value.add(item.id);
                    }
                    return {
                        key: `exercise-item-${item.id}`,
                        id: item.id,
                        name: item.name,
                        level: item.level,
                        total_questions: item.total_questions,
                        level_type: "exercise-item",
                        practice_id: record.id,
                        week_id: record.week_id,
                        book_id: record.book_id,
                        is_assigned: item.is_assigned || false
                    };
                });
                // Trigger reactive update
                data.value = [...data.value];
            }
        } catch (err) {
            console.error('Error loading items for practice:', err);
            toast.error('Lỗi tải bài tập con');
        }
    }
};
const confirm = (value) => {
    axios
        .delete(route("apps.json.delete", { id: value }))
        .then((response) => {
            if (response.status === 200) {
                toast.success("Xóa app thành công");
                setTimeout(function() {
                    location.href = "/apps?appId=" + app_id; // URL cần chuyển hướng
                }, 500);
            }
        })
        .catch(() => {
            toast.error("Đã có lỗi xảy ra, vui lòng thử lại sau!");
        });
};
</script>

<template>
    <Head title="Practice" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Practice</h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-full md:w-10/12">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Giao bài<span v-if="student_name"> — {{ student_name }}</span>
                </h1>
                <div class="filter-page mt-4 flex gap-10">
                    <a-input
                        placeholder="Tìm kiếm"
                        :allow-clear="true"
                        style="width: 30rem"
                        v-model:value="formFilter.search"
                    >
                        <!-- Sử dụng slot suffix để đặt icon -->
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
                        ref="tableRef"
                        rowKey="key"
                        :scroll="{ x: 'max-content' }"
                        @expand="handleExpand"
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
                        </template>
                        <template #footer>
                            <div class="flex items-center justify-end">
                                <!-- Pagination -->
                                <a-pagination v-bind="pagination" @change="onPageChange" />
                                <!-- Icon plus -->
                                <Link :href="route('apps.create', { appId: app_id })">
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
    <a-modal v-model:open="openAssign" :width="1000" @cancel="handleClose">
        <template #title>Chọn bài tập con để giao</template>
        <a-spin :spinning="loadingItems">
            <div class="mt-4">
                <h3 class="font-semibold mb-3">Danh sách bài tập con:</h3>
                <div v-if="exerciseItems.length === 0" class="text-gray-500">Không có bài tập con nào</div>
                <div v-else class="space-y-2 max-h-96 overflow-y-auto border rounded p-3 bg-gray-50">
                    <div v-for="item in exerciseItems" :key="item.id" class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
                        <a-checkbox v-model:checked="item.checked" />
                        <div class="flex-1">
                            <div class="font-medium">{{ item.name }}</div>
                            <div class="text-sm text-gray-500">
                                Mức độ: <span class="font-semibold">{{ item.level }}</span> |
                                Số câu: <span class="font-semibold">{{ item.total_questions }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="font-semibold mb-3">Thời hạn giao bài:</h3>
                    <div class="flex gap-2 items-center">
                        <div>
                            <a-checkbox v-model:checked="formAssign.checkedTime">Có thời hạn</a-checkbox>
                        </div>
                        <a-range-picker v-model:value="formAssign.limitTime" size="large" />
                        <div v-if="formAssignErrors.limitTime" class="text-red-500 text-sm mt-1">
                            {{ formAssignErrors.limitTime }}
                        </div>
                    </div>
                    <div class="mt-4">
                        <a-checkbox v-model:checked="formAssign.checkedNonTime">Vô thời hạn</a-checkbox>
                    </div>
                </div>

                <div v-if="formAssignErrors.general" class="text-red-500 mt-4 p-2 bg-red-50 rounded">
                    {{ formAssignErrors.general }}
                </div>
            </div>
        </a-spin>
        <template #footer>
            <a-button
                v-if="formAssign.showConfirm"
                class="text-white"
                size="large"
                type="primary"
                @click="confirmAssign"
            >
                Giao bài tập con
            </a-button>
        </template>
    </a-modal>
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

tr td:first-child {
    width:120px
}
</style>

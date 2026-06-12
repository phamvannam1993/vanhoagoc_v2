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
const class_id = query.class_id;
const student_id = page.props.student_id ?? null;
const student_name = page.props.student_name ?? null;
const columns = [
    {
        title: "STT",
        dataIndex: "id",
        key: "id",
        align: "center",
        width: "10%"
    },
    {
        title: "Nội dung",
        dataIndex: "name",
        key: "name",
        width: "30%"
    },
    {
        title: "Giao bài",
        dataIndex: "assign",
        key: "assign",
        customRender: ({ record }) => {
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
            if (record.level === "practice" &&  record.assign) {
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
                            class_id: class_id
                        });
                    }
                });
            }
            return null;
        }
    }
];
const openAssign = ref(false);
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
const handleAssign = (record) => {
    openAssign.value = true;
    formAssign.value = {
        checkedTime: false,
        checkedNonTime: false,
        limitTime: null,
        practice_id: null,
        book_id: null,
        week_id: null,
        showConfirm: true
    };
    if (record.infoAssign.length > 0) {
        if (record.infoAssign[0].from) {
            formAssign.value.checkedTime = true;
            formAssign.value.limitTime = [dayjs(record.infoAssign[0].from, 'YYYY/MM/DD'), dayjs(record.infoAssign[0].to, 'YYYY/MM/DD')];
        } else {
            formAssign.value.checkedNonTime = true;
        }
        formAssign.value.showConfirm = false;
    }

    formAssign.value.practice_id = record.id;
    formAssign.value.book_id = record.book_id;
    formAssign.value.week_id = record.week_id;
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

        const form = {
            checkedTime: formAssign.value.checkedTime,
            checkedNonTime: formAssign.value.checkedNonTime,
            practice_id: formAssign.value.practice_id,
            book_id: formAssign.value.book_id,
            week_id: formAssign.value.week_id,
            class_id: class_id,
            student_id: student_id,
            from: formAssign.value.limitTime
                ? formAssign.value.limitTime[0].format('YYYY/MM/DD')
                : null,
            to: formAssign.value.limitTime
                ? formAssign.value.limitTime[1].format('YYYY/MM/DD')
                : null,
        };
        const res = await axios.post(route('admins.practices.json.assignPractice'), form)

        if (res.data.status) {
            openAssign.value = false;
            toast.success('Giao bài thành công');
            await loadData();
        }
    } catch (err) {
        if (err.inner && err.inner.length > 0) {
            const errors = {};
            err.inner.forEach((e) => {
                if (e.path) {
                    errors[e.path] = e.message;
                } else {
                    // Trường hợp không có path, gán vào lỗi tổng
                    errors.general = e.message;
                }
            });
            formAssignErrors.value = errors;
        } else {
            formAssignErrors.value = { general: err.message };
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
    const params = {
        page: pagination.value.current,
        search: formFilter.value.search,
        class_id: class_id,
        student_id: student_id
    };
    const res = await axios.get(route("admins.practices.json.getLesson", params));

    if (res.data.status) {
        data.value = res.data.data.data.map((v) => {
            return {
                key: `book-${v.id}`,
                id: v.id,
                name: v.title,
                level: "book",
                children: v.weeks.map((week) => ({
                    key: `week-${week.id}`,
                    id: week.id,
                    name: `Tuần ${week.numberWeek}: ${week.name || "Không có tên"}`,
                    level: "week",
                    children: week.practices.map((practice) => ({
                        key: `practice-${practice.id}`,
                        id: practice.id,
                        week_id: practice.week_id,
                        book_id: practice.book_id,
                        name: practice.name,
                        practicePoints: practice.practice_points,
                        practice_classes: practice.practice_classes,
                        infoAssign: practice.assigned_for_user,
                        assign: practice.assigned_for_user.length > 0,
                        withdraw: true,
                        status: practice.status,
                        result: true,
                        level: "practice"
                    }))
                }))
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
    formFilter.value.search = "";
    loadData();
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
        <template #title>Giáo viên giao bài</template>
        <div class="mt-4">
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
            <div v-if="formAssignErrors.general" class="text-red-500 mt-2">
                {{ formAssignErrors.general }}
            </div>
        </div>
        <template #footer>
            <a-button
                v-if="formAssign.showConfirm"
                class="text-white"
                size="large"
                type="primary"
                @click="confirmAssign"
            >
                Xác nhận
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
</style>

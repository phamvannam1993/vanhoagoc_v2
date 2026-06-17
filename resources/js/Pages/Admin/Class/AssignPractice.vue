<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import { SearchOutlined } from "@ant-design/icons-vue";
import { ref, onMounted, reactive } from "vue";
import { useToast } from "vue-toastification";
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { h } from "vue";
import * as yup from "yup";
import dayjs from "dayjs";
import { Progress as AProgress, Modal, Form, Input, Checkbox, DatePicker } from 'ant-design-vue';
import axios from "axios";

const toast = useToast();
const page = usePage();
const user = page.props.auth.user;
const query = page.props.query;
const class_id = ref(query.class_id);
const pagination = ref({ current: 1, pageSize: 100, total: 0 });
const data = ref([]);
const formFilter = reactive({ search: "" });
const openAssign = ref(false);

const formAssign = ref({
    checkedTime: false,
    checkedNonTime: false,
    limitTime: null,
    practice_id: null,
    book_id: null,
    week_id: null,
});

const columns = [
    {
        title: "STT",
        dataIndex: "id",
        key: "id",
        align: "center",
        width: "50px",
        customRender: ({ record, index }) => index + 1,
    },
    {
        title: "Nội dung",
        dataIndex: "name",
        key: "name",
        customRender: ({ record }) => {
            if (record.level === "week") {
                return h("div", { class: "font-semibold text-gray-800 truncate" }, record.name);
            } else if (record.level === "practice") {
                return h("div", { class: "pl-6 text-gray-700 truncate" }, record.name);
            }
            return h("div", { class: "truncate" }, record.name);
        }
    },
    {
        title: "Giao bài",
        dataIndex: "assign",
        key: "assign",
        align: "center",
        width: "100px",
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
        align: "center",
        width: "100px",
        customRender: ({ record }) => {
            if (record.level === "practice" && record.assign) {
                const imageSrc = "/images/Teacher/with_draw.png";
                return h(
                    "img",
                    {
                        src: imageSrc,
                        alt: "Thu hồi",
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
        align: "center",
        width: "120px",
        customRender: ({ record }) => {
            if (record.level === "practice") {
                const done = record.practicePoints?.length ?? 0;
                const total = record?.practice_classes?.length > 0 ? record?.practice_classes[0]?.users?.length ?? 0 : 0;
                const percent = total > 0 ? (done / total) * 100 : 0;

                return h(AProgress, {
                    percent,
                    format: () => `${done} / ${total}`,
                });
            }
            return null;
        }
    },
    {
        title: "Kết quả",
        dataIndex: "result",
        key: "result",
        align: "center",
        width: "100px",
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

const handleAssign = (record) => {
    openAssign.value = true;
    formAssign.value = {
        checkedTime: false,
        checkedNonTime: false,
        limitTime: null,
        practice_id: record.id,
        book_id: record.book_id,
        week_id: record.week_id,
    };

    if (record.infoAssign && record.infoAssign.length > 0) {
        if (record.infoAssign[0].from) {
            formAssign.value.checkedTime = true;
            formAssign.value.limitTime = [dayjs(record.infoAssign[0].from), dayjs(record.infoAssign[0].to)];
        } else {
            formAssign.value.checkedNonTime = true;
        }
    }
};

const handleWithdraw = async (record) => {
    if (!record.infoAssign || record.infoAssign.length === 0) return;

    try {
        const res = await axios.post(route('admins.practices.json.withDrawPractice'), {
            practice_id: record.id,
            class_id: class_id.value,
            student_id: null,
        });

        if (res.data.status) {
            toast.success('Thu hồi thành công');
            await loadData();
        }
    } catch (err) {
        toast.error(err.response?.data?.message || 'Lỗi thu hồi bài tập');
    }
};

const handleClose = () => {
    openAssign.value = false;
};

const confirmAssign = async () => {
    try {
        const from = formAssign.value.limitTime ? formAssign.value.limitTime[0]?.format('YYYY/MM/DD') : null;
        const to = formAssign.value.limitTime ? formAssign.value.limitTime[1]?.format('YYYY/MM/DD') : null;

        const res = await axios.post(route('admins.practices.json.assignPractice'), {
            practice_id: formAssign.value.practice_id,
            book_id: formAssign.value.book_id,
            week_id: formAssign.value.week_id,
            class_id: class_id.value,
            student_id: null,
            checkedTime: formAssign.value.checkedTime,
            checkedNonTime: formAssign.value.checkedNonTime,
            from,
            to,
        });

        if (res.data.status) {
            toast.success('Giao bài thành công');
            openAssign.value = false;
            await loadData();
        }
    } catch (err) {
        toast.error(err.response?.data?.message || 'Lỗi giao bài');
    }
};

const loadData = async () => {
    try {
        const res = await axios.get(route('admins.practices.json.getLesson'), {
            params: {
                class_id: class_id.value,
                page: pagination.value.current,
                search: formFilter.search,
            }
        });

        if (res.data.status && res.data.data) {
            const lessons = res.data.data.data || res.data.data;
            data.value = lessons.map((book) => ({
                key: `book-${book.id}`,
                id: book.id,
                name: book.title,
                level: "book",
                children: (book.weeks || []).map((week) => ({
                    key: `week-${week.id}`,
                    id: week.id,
                    name: `Tuần ${week.numberWeek}: ${week.name || "Không có tên"}`,
                    level: "week",
                    children: (week.practices || []).map((practice) => ({
                        key: `practice-${practice.id}`,
                        id: practice.id,
                        week_id: practice.week_id,
                        book_id: practice.book_id,
                        name: practice.name,
                        level: "practice",
                        practicePoints: practice.practice_points || [],
                        practice_classes: practice.practice_classes || [],
                        infoAssign: practice.assigned_for_user || [],
                        assign: (practice.assigned_for_user || []).length > 0,
                    }))
                }))
            }));

            if (res.data.data.total) {
                pagination.value.total = res.data.data.total;
            }
        }
    } catch (err) {
        console.error('Error loading data:', err);
        toast.error('Lỗi tải dữ liệu');
    }
};

onMounted(async () => {
    await loadData();
});
</script>

<style scoped>
:deep(.ant-table-cell) {
    padding: 8px 12px !important;
    white-space: nowrap;
}

:deep(.ant-table-row) {
    height: auto;
}
</style>

<template>
    <Head>
        <title>Giao bài theo lớp</title>
    </Head>
    <SchoolLayout>
        <div class="min-h-screen bg-gray-50 p-6">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h1 class="text-3xl font-bold mb-6 text-gray-900">Giao bài theo lớp</h1>

                    <!-- Table -->
                    <a-table
                        :columns="columns"
                        :data-source="data"
                        :pagination="{ pageSize: 100, hideOnSinglePage: true }"
                        :loading="false"
                        bordered
                        size="middle"
                    />
                </div>
            </div>
        </div>

        <!-- Assign Modal -->
        <a-modal
            v-model:visible="openAssign"
            title="Giao bài"
            ok-text="Xác nhận"
            cancel-text="Hủy"
            @ok="confirmAssign"
            @cancel="handleClose"
        >
            <a-form layout="vertical">
                <a-form-item label="Thời hạn">
                    <a-checkbox v-model:checked="formAssign.checkedTime">Có thời hạn</a-checkbox>
                    <a-range-picker
                        v-if="formAssign.checkedTime"
                        v-model:value="formAssign.limitTime"
                        format="YYYY/MM/DD"
                        class="w-full mt-2"
                    />
                </a-form-item>

                <a-form-item>
                    <a-checkbox v-model:checked="formAssign.checkedNonTime">Vô thời hạn</a-checkbox>
                </a-form-item>
            </a-form>
        </a-modal>
    </SchoolLayout>
</template>

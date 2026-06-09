<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import {SearchOutlined} from '@ant-design/icons-vue';
import {ref, onMounted, nextTick} from 'vue';
import {Link} from '@inertiajs/vue3';
import {useToast} from 'vue-toastification';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { USER_TYPE_ADMIN, USER_TYPE_DIRECTOR, USER_TYPE_TEACHER } from "@/const.js";

const toast = useToast();
const page = usePage();
const user = page.props.auth.user;
const showAdminMenu = !!user.user_type_id && user.user_type_id == 2;
const query = page.props.query;
const name =  page.props.name;
const title =  page.props.title;
const name_app =  page.props.name_app;
const class_id = query.class_id;
const result_learn = query.result_learn;
const user_id = query.user_id;
const tab_id = query.tab_id ?? 2;
const userType = user.user_type.type;
let columns = [
    {
        title: 'STT',
        dataIndex: 'id',
        key: 'id',
        width: 80,
        align: 'center',
    },
    {
        title: 'Nội dung',
        dataIndex: 'name',
        key: 'name',
    },
    {
        title: 'Thời hạn',
        dataIndex: 'duration',
        key: 'duration',
        width: '20%',
    },
    {
        title: 'Trạng thái thực hiện',
        key: 'status_text',
        dataIndex: 'status_text',
        width: '10%',
    },
    {
        title: 'Điểm (Sao)',
        key: 'point_text',
        dataIndex: 'point_text',
        width: '10%',
    },
    {
        title: 'Thời gian làm',
        key: 'time_text',
        dataIndex: 'time_text',
        width: '10%',
    },
    {
        title: 'Xếp hạng',
        key: 'rank',
        dataIndex: 'rank',
        width: '10%',
    },
    {
        title: 'Đánh giá',
        key: 'review',
        dataIndex: 'review',
        width: '10%',
    },
];
if(tab_id == 1) {
    columns = [
        {
            title: 'Ngày',
            dataIndex: 'day_create',
            key: 'day_create',
            width: '10%',
            align: 'center',
        },
        {
            title: 'Nội dung',
            dataIndex: 'name',
            key: 'name',
            width: '30%',
        },
        {
            title: 'Điểm lý thuyết',
            dataIndex: 'point_text_2',
            key: 'point_text_2',
            width: '10%',
        },
        {
            title: 'Điểm luyện tập',
            key: 'point_text_1',
            dataIndex: 'point_text_1',
            width: '10%',
        },
        {
            title: 'Thời gian làm',
            key: 'time_text',
            dataIndex: 'time_text',
            width: '10%',
        },
        {
            title: 'Điểm tổng hợp',
            key: 'total_point',
            dataIndex: 'total_point',
            width: '10%',
        },
        {
            title: 'Chi tiết',
            key: 'detail',
            dataIndex: 'detail',
            width: '10%',
        },
        {
            title: 'Xếp hạng',
            key: 'rank',
            dataIndex: 'rank',
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
    type_id: null
});
const tableRef = ref(null);
onMounted(async () => {
    await loadData();
});
const loadData = async () => {
    const params = {
        type_id: formFilter.value.type_id,
        class_id: formFilter.value.class_id,
        user_id: formFilter.value.user_id,
    };
    let res = null;
    if(tab_id == 1) {
        res = await axios.get(route('admins.students.json.freePractice', params));
    } else {
        res = await axios.get(route('admins.students.json.assignedTask', params));
    }
    if (res.data.status) {
        data.value = res.data.data.map((v) => {
            return {
                id: v.id,
                name: v.name,
                status:v.status,
                duration: v.duration,
                review_status: v.review_status,
                point_text: v.point_text,
                time_text:v.time_text,
                point_id:v.point_id,
                day_create:v.day_create,
                point_text_1:v.point_text_1,
                point_text_2:v.point_text_2,
                total_point:v.total_point,
                rank:v.rank,
            };
        });
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
    formFilter.value.type_id = null;
    loadData();
};
const listRecordDelete = ref([]);
const rowSelection = ref({
    onChange: (selectedRowKeys, selectedRows) => {
        listRecordDelete.value = selectedRows;
    },
    onSelect: (record, selected, selectedRows) => {

    },
    onSelectAll: (selected, selectedRows, changeRows) => {
    },
});
const confirmDeleteMulti = () => {
    const idsToDelete = listRecordDelete.value.map(item => item.id);
    axios.delete(route('admins.students.json.deleteMultiple'), {
        data: { ids: idsToDelete }
    })
        .then(response => {
            if (response.status === 200) {
                toast.success('Xóa tài khoản Nhân viên/Học sinh thành công');
                setTimeout(function () {
                    location.href = route('admins.students.index', { class_id: class_id })
                }, 500);
            }
        })
        .catch(error => {
            toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
        });
}
const confirm = (value) => {
    axios
        .delete(route('admins.students.json.delete', { id: value }))
        .then((response) => {
            if (response.status === 200) {
                toast.success('Xóa tài khoản Nhân viên/Học sinh thành công');
                setTimeout(function () {
                    location.href = route('admins.students.index', { class_id: class_id })
                }, 500);
            }
        })
        .catch(() => {
            toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
        });
};

const popoverVisible = ref(false);
const listApp = ref([]);
const handleClickTitle = async (appId) => {
    const params = {
        app_id: appId
    };
    const res = await axios.get(route('admins.teachers.json.getClassByApp', params));
    if (res.data.status) {
        listApp.value = res.data.data.map((v) => {
            return { value: v.id, label: v.name }
        });
    }
}

const filterOption = (input, option) => {
    return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};
const optionsType = ref([
    {
        value: 1,
        label: 'Theo tên đơn vị (App)'
    },
    {
        value: 2,
        label: 'Theo mã lớp'
    },
    {
        value: 3,
        label: 'Theo tài khoản'
    },
    {
        value: 4,
        label: 'Theo tên'
    },
    {
        value: 5,
        label: 'Theo số điện thoại'
    },
]);
</script>

<template>
    <Head title="Student"/>

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Student</h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-11/12">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">{{title}}</h1>

                <h1 class="font-bold text-[#2C75E3] text-name text-center">{{name_app}}<br>{{name}}</h1>
                <Link
                    :href="!result_learn ? route('admins.students.index', { class_id: class_id }) : route('admins.class.resultLearn', { class_id: class_id, tab_id:tab_id })"
                    class="mt-6"
                >
                    <a-button class="mt-6 gray-btn">Quay lại</a-button>
                </Link>
                <Link v-if="!result_learn"
                    :href="route('admins.students.result', { user_id: user_id, class_id: class_id, tab_id:2 })"
                    class="mt-6 ml-20"
                >
                    <a-button class="mt-6" :class="tab_id == 1 ? 'gray-btn color-white' : ''" :type="tab_id == 2 ? 'primary' : 'default'">Nhiệm vụ được giao</a-button>
                </Link>
               <Link v-if="!result_learn"
                   :href="route('admins.students.result', { user_id: user_id, class_id: class_id, tab_id:1 })"
                    class="mt-6 ml-2"
                >
                    <a-button class="mt-6" :class="tab_id == 2 ? 'gray-btn color-white' : ''" :type="tab_id == 1 ? 'primary' : 'default'">Luyện tập tự do</a-button>
                </Link>
                <a
                    :href="route('admins.students.json.exportStudentResult', { user_id: user_id, class_id: class_id, tab_id: tab_id })"
                    class="mt-6 ml-4"
                >
                    <a-button class="mt-6" type="default">⬇ Xuất Word</a-button>
                </a>

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
                            <template v-if="column.key === 'result'">
                              <a class=" ant-btn ant-btn-primary ant-btn-lg text-white ant-dropdown-trigger" href="{{route('admins.students.result', { user_id: record.id })}}">
                                <span>Kết quả</span>
                              </a>
                            </template>
                            <template v-if="column.key === 'status_text'">
                                <span v-if="record.status === 0" class=" ant-btn ant-btn-default mt-6 gray-btn color-white">Chưa thực hiện</span>
                                <span v-else-if="record.status === 1" class=" ant-btn ant-btn-primary mt-6"><a  :href="route('admins.students.pointDetail', { user_id: user_id, class_id: class_id, tab_id:2, point_id: record.point_id})">Chi tiết kết quả</a></span>
                                <span v-else class="ant-btn mt-6 bg-red-500 border-red-500 text-white">Trễ hạn</span>
                            </template>
                            <template v-if="column.key === 'detail'">
                                <a  :href="route('admins.students.pointDetail', { user_id: user_id, class_id: class_id, tab_id:tab_id, point_id: record.id, result_learn:result_learn})"><span class=" ant-btn ant-btn-primary mt-6">Xem chi tiết</span></a>
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

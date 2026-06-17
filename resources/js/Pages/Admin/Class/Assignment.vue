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
const user = page.props?.auth?.user;
const showAdminMenu = !!user.user_type_id && user.user_type_id == 2;
const query = page.props?.query;
const name =  page.props.name;
const name_app =  page.props.name_app;
const class_id = query.class_id;
const user_id = query.user_id;
const app_id = page.props.app_id;
const userType = user.user_type.type;
let columns = [
    {
        title: 'STT',
        dataIndex: 'id',
        key: 'id',
        width: '10%',
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
        title: 'Giao cho',
        dataIndex: 'class_name',
        key: 'class_name',
        width: '20%',
    },
    {
        title: 'Bảng xếp hạng',
        key: 'result',
        dataIndex: 'result',
        width: '10%',
    },
    {
        title: 'Thu hồi',
        key: 'delete',
        dataIndex: 'delete',
        width: '10%',
    },
];

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
});
const tableRef = ref(null);
onMounted(async () => {
    await loadData();
});
const loadData = async () => {
    const params = {
        page: pagination.value.current,
        class_id: formFilter.value.class_id,
        user_id: user_id
    };
    let res = null;
    res = await axios.get(route('admins.class.json.assignedTask', params));
    if (res.data.status) {
        data.value = res.data.data.map((v) => {
            return {
                id: v.id,
                name: v.name,
                status:v.status,
                is_rank:v.is_rank,
                duration: v.duration,
                class_name:v.class?.name,
                review_status: v.review_status,
                point_text: v.point_text,
                time_text:v.time_text,
                point_id:v.point_id,
                class_id:v.class?.id,
                day_create:v.day_create,
                point_text_1:v.point_text_1,
                point_text_2:v.point_text_2,
                total_point:v.total_point,
                practice_id:v.practice?.id,
                rank:v.rank,
            };
        });
    }
};
function formatTimeToMinutesSeconds(time) {
    const minutes = Math.floor(time / 60);
    const seconds = Math.round(time % 60);
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}
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

const confirm = (value) => {
    axios
        .post(route('admins.class.json.deleteAssign', { id: value }))
        .then((response) => {
            if (response.status === 200) {
                toast.success('Thu hồi thành công');

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

const handleChangeType = (value) => {
    formFilter.value.type = value;
    loadData();
};
</script>

<template>
    <Head title="Student"/>

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Student</h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-11/12">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Danh sách giao bài</h1>
                <h1 class="font-bold text-[#2C75E3] text-name text-center">{{name_app}}<br>{{name}}</h1>
                <Link
                    :href="user_id > 0 ? route('admins.teachers.index') : route('admins.class.index', { app_id: app_id, class_id: class_id })"
                    class="mt-6"
                >
                    <a-button class="mt-6 gray-btn" @click="() => window.history.back()">Quay lại</a-button>
                </Link>
                <Link v-if="class_id"
                    :href="route('admins.practices.index', { user_id: user_id, class_id: class_id, tab_id:2 })"
                    class="mt-6 ml-5"
                >
                    <a-button class="mt-6"  type="primary">Giao bài mới</a-button>
                </Link>
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
                              <a v-if="record.is_rank" class=" ant-btn ant-btn-primary ant-btn-lg text-white ant-dropdown-trigger" :href="route('admins.class.showRank', { practice_id: record.practice_id, class_id:record.class_id})">
                                <span>Bảng xếp hạng</span>
                              </a>
                             <a v-else class="css-dev-only-do-not-override-1p3hq3p ant-btn ant-btn-default gray-btn">
                                <span>Chưa có kết quả</span>
                              </a>
                            </template>

                            <template v-if="column.key === 'delete'">
                                <a-popconfirm
                                    placement="topRight"
                                    ok-text="Đồng ý"
                                    cancel-text="Bỏ qua"
                                    @confirm="confirm(record.id)"
                                >
                                    <template #title>
                                        <p>Bạn có chắc chắn muốn thu hồi?</p>
                                    </template>
                                    <span class="css-dev-only-do-not-override-1p3hq3p ant-btn ant-btn-primary gray-btn">Thu hồi</span>
                                </a-popconfirm>
                            </template>


                            <template v-if="column.key === 'detail' && record.type != 2">
                                <a  :href="route('admins.students.result', { user_id: record.id, class_id: class_id, result_learn:1})"><span class=" ant-btn ant-btn-primary mt-6">Chi tiết luyện tập</span></a>
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
    font-size: 13px;
    height: 32px;
    padding: 5px;
    border-radius: 5px;
}
.ant-btn-primary {
    color: #fff;
    background-color: #1677ff;
    box-shadow: 0 2px 0 rgba(5, 145, 255, 0.1);
}
</style>

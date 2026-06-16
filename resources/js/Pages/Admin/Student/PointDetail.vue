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
const name_app =  page.props.name_app;
const class_id = query.class_id;
const user_id = query.user_id;
const practice_id =  query.practice_id;
const tab_id = query.tab_id ?? 2;
const result_learn = query.result_learn
const title = page.props.title;
const userType = user.user_type.type;
const pointDetails =  page.props.pointDetails;
const summary = page.props.summary || { total_point: '', total_time_text: '', rank_text: '' };
let columns = [
    { title: 'Câu', dataIndex: 'id', key: 'id', align: 'center', width: 80 },
    { title: 'Kết quả', dataIndex: 'result', key: 'result' },
    { title: 'Thời gian', dataIndex: 'time_text', key: 'time_text', align: 'center', width: 120 },
    { title: 'Tổng điểm', key: 'total_point', align: 'center', width: 110,
      customCell: (_, index) => index === 0 ? { rowSpan: data.value.length } : { rowSpan: 0 } },
    { title: 'Tổng thời gian', key: 'total_time', align: 'center', width: 130,
      customCell: (_, index) => index === 0 ? { rowSpan: data.value.length } : { rowSpan: 0 } },
    { title: 'Xếp hạng', key: 'rank', align: 'center', width: 110,
      customCell: (_, index) => index === 0 ? { rowSpan: data.value.length } : { rowSpan: 0 } },
];

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
     data.value = pointDetails.map((v) => {
        return {
            id: v.id,
            name: v.name,
            star_count:v.star_count,
            time_text:v.time_text
        };
    });
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
            <div class="content-page mx-auto w-[98%]">
                <h1 class="text-[20px] font-bold text-[#2C75E3]">{{title}}</h1>
                <h1 class="font-bold text-[#2C75E3] text-name text-center">{{name_app}}<br>{{name}}</h1>
                <div class="mt-6 flex gap-2">
                    <Link
                        :href="practice_id > 0 ? route('admins.class.showRank', { class_id: class_id, tab_id:tab_id, user_id:user_id, practice_id:practice_id }) : result_learn > 0 ? route('admins.students.result', { class_id: class_id, tab_id:tab_id, user_id:user_id, result_learn:result_learn }) : route('admins.students.result', { class_id: class_id, tab_id:tab_id, user_id:user_id })"
                    >
                        <a-button class="gray-btn" @click="() => window.history.back()">Quay lại</a-button>
                    </Link>
                    <a :href="route('admins.students.json.exportPointDetail', { point_id: query.point_id, user_id: user_id, class_id: class_id })">
                        <a-button type="primary">Xuất Word</a-button>
                    </a>
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
                            <template v-if="column.key === 'result'">
                                <span v-if="!record.time_text" class="color-gray mt-6 font-bold ">Chưa thực hiện</span>
                                <span v-else-if="record.star_count === 0" class="color-red font-bold ">Chưa chính xác</span>
                                <span v-else class="color-green mt-6 font-bold ">Chính xác</span>
                            </template>
                            <template v-if="column.key === 'total_point'">
                                <span class="font-bold text-[#2C75E3] text-[18px]">{{ summary.total_point }}</span>
                            </template>
                            <template v-if="column.key === 'total_time'">
                                <span class="font-bold text-[#2C75E3] text-[18px]">{{ summary.total_time_text }}</span>
                            </template>
                            <template v-if="column.key === 'rank'">
                                <span class="font-bold text-[#2C75E3] text-[18px]">{{ summary.rank_text }}</span>
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
    color:#ffffff !important;
}
.color-red {
    color:red
}
.color-gray {
    color:darkgray;
}
.text-name {
    float:right
}
.color-green {
    color:green
}
.font-bold {
    font-weight:bold
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
.text-name {
    float: right;
    font-size: 20px;
    margin-top: -40px;
}
</style>

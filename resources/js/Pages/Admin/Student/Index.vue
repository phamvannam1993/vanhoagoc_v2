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
const class_id = query.class_id;
const app_id = query.app_id;
const userType = user.user_type.type;
const options = [
  {
    id:1,
    name: "Chánh Kiến 1 – Đánh Thức Ý Nghĩa Cuộc Đời",
    week_id: "canhdieu.lop1.chanhkien.quyen1.tuan1",
    price: 499,
    pricekey: 499000
  },
  {
    id:2,
    name: "Chánh Kiến 2 – Kiến Tạo Con Đường Hạnh Phúc",
    week_id: "canhdieu.lop1.chanhkien.quyen1.tuan4",
    price: 699,
    pricekey: 699000
  },
    {
    id:3,
    name: "Thấu hiểu Nhân tâm",
    week_id: "canhdieu.lop1.huyenmonhoc.quyen1.tuan1",
    price: 499,
    pricekey: 499000
  },
  {
    id:4,
    name: "Nhân tướng trong đời sống",
    week_id: "canhdieu.lop1.huyenmonhoc.quyen1.tuan4",
    price: 999,
    pricekey: 999000
  },
    {
    id:5,
    name: "Dạy con 5 phút",
    week_id: "canhdieu.lop2.daycon.quyen1.tuan3",
    price: 399,
    pricekey: 399000
  },
  {
    id:6,
    name: "Nuôi dưỡng TTCX 1",
    week_id: "canhdieu.lop1.sach.quyen1.tuan1",
    price: 499,
    pricekey: 499000
  },
  {
    id:7,
    name: "Nuôi dưỡng TTCX 2",
    week_id: "canhdieu.lop1.sach.quyen1.tuan2",
    price: 499,
    pricekey: 499000
  },
  {
    id:8,
    name: "Dạy con tư duy nhân quả",
    week_id: "canhdieu.lop2.daycon.quyen1.tuan4",
    price: 499,
    pricekey: 499000
  },
  {
    id:9,
    name: "Bé yêu thiên nhiên",
    week_id: "canhdieu.lop1.sach.quyen1.tuan4",
    price: 499,
    pricekey: 499000
  },
  {
    id:10,
    name: "Vườn cổ tích STT",
    week_id: "canhdieu.lop1.sach.quyen1.tuan5",
    price: 499,
    pricekey: 499000
  },
  {
    id:11,
    name: "Dạy Con Thành công và HP bền vững",
    week_id: "canhdieu.lop2.daycon.quyen1.tuan2",
    price: 499,
    pricekey: 499000
  }
];
const selected = ref(1)
const columns = [
    {
        title: 'STT',
        dataIndex: 'id',
        key: 'id',
        width: 80,
        align: 'center',
    },
    {
        title: 'Họ tên',
        dataIndex: 'name',
        key: 'name',
    },
    {
        title: 'Tên tài khoản',
        dataIndex: 'username',
        key: 'username',
        width: '20%',
    },
    {
        title: 'Số điện thoại',
        key: 'tel',
        dataIndex: 'tel',
        width: '10%',
    },
    {
        title: 'Thời gian tạo',
        key: 'created_at',
        dataIndex: 'created_at',
    },
    {
        title: 'Kích hoạt',
        key: 'active_code',
        dataIndex: 'active_code',
        width: '10%',
    },
    {
        title: 'Giao bài',
        key: 'assign',
        dataIndex: 'assign',
        width: '10%',
    },
    {
        title: 'Kết quả',
        key: 'result',
        dataIndex: 'result',
        width: '10%',
    },
    {
        title: 'Tùy chỉnh',
        key: 'action',
    },
];
const pagination = ref({
    current: 1,
    pageSize: 1,
    total: 0,
});
const data = ref([]);
const formFilter = ref({
    search: '',
    class_id: class_id,
    type_id: null
});
const tableRef = ref(null);
onMounted(async () => {
    await loadData();
});
const orderList = ref([])
const showModalOrder = ref(false)
const showOrders = (orders) => {
   for(var i = 0; i < orders.length;i++) {
        for(var j = 0; j < options.length;j++) {
            if(orders[i].namesubject == options[j].week_id) {
                orders[i].name = options[j].name
            }
        }
   }
   orderList.value = orders
   showModalOrder.value = true
   selected_order.value = orders[0].id
}
const selected_order = ref("")
const confirmCancelOrder = () => {
    var orderId = selected_order.value
    axios
    .delete(route('admins.students.json.deleteOrder', { id: orderId }))
    .then((response) => {
        if (response.status === 200) {
            toast.success('Hủy gói kích hoạt thành công');
            setTimeout(function () {
                location.href = ''
            }, 500);
        }
    })
    .catch(() => {
        toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
}
const loadData = async () => {
    const params = {
        page: pagination.value.current,
        search: formFilter.value.search,
        type_id: formFilter.value.type_id,
        class_id: formFilter.value.class_id,
    };
    const res = await axios.get(route('admins.students.json.jsonList', params));

    if (res.data.status) {
        data.value = res.data.data.data.map((v) => {
            return {
                id: v.id,
                name: v.name,
                orders:v.orders,
                username: v.username,
                app_name: v.app_name,
                class_name: v.class_name,
                is_result: v.is_result,
                class_id: v?.classes[0]?.id || null,
                tel: v.tel,
                app: v?.student_app[0]?.name,
                app_id: v?.student_app[0]?.id || null,
                isAssign: v?.classes.length > 0,
                isAssignTask: v?.is_assign ?? false,
                classes: v?.classes.length > 0 ? v?.classes[0].id : null,
                created_at: v.created_at
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
    formFilter.value.search = '';
    formFilter.value.type_id = null;
    loadData();
};
const listRecordDelete = ref([]);
const showDelete = ref(false)
const rowSelection = ref({
    onChange: (selectedRowKeys, selectedRows) => {
        listRecordDelete.value = selectedRows;
        if(listRecordDelete.value.length > 0) {
            showDelete.value = true
        } else {
            showDelete.value = false
        }
    },
    onSelect: (record, selected, selectedRows) => {

    },
    onSelectAll: (selected, selectedRows, changeRows) => {
    },
});
const onKeyDown = (event) => {
    // Chặn tất cả các phím nhập liệu vào input
    if (event.key.length === 1) {
        event.preventDefault();
    }
}
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
const resetPassword = (value) => {
    axios
        .post(route('admins.students.json.resetPassword', { id: value }))
        .then((response) => {
            if (response.status === 200) {
                toast.success('Reset password thành công');
                setTimeout(function () {
                    location.href = route('admins.teachers.index')
                }, 500);
            }
        })
        .catch(() => {
            toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
        });
};
const confirmOrder = async() => {
    const idsToDelete = listRecordDelete.value.map(item => item.id);
    var namesubject = ""
    var price = 0
    for(var i = 0; i < options.length; i++) {
        if(options[i].id == selected.value) {
            namesubject = options[i].week_id
            price = options[i].pricekey
        }
    }
    axios.post(route('admins.students.postOrderMulti'), {
        data: { user_ids: idsToDelete , namesubject:namesubject, price:price}
    })
    .then(response => {
        if (response.status === 200) {
            toast.success('kích hoạt tài khoản thành công');
            setTimeout(function () {
                location.href = route('admins.students.index', { class_id: class_id })
            }, 500);
        }
    })
    .catch(error => {
        toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
}
const popoverVisible = ref(false);
const listApp = ref([]);
const showModal = ref(false)
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
const selectClass = async (userId, appId, classId) => {
    const params = {
        app_id: appId,
        user_id: userId,
        class_id: classId,
    };
    const res = await axios.post(route('admins.students.json.assignClass', params));
    if (res.data.status) {
        toast.success('Phân bổ Nhân viên/Học sinh vào Phòng ban/Lớp thành công');
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
        label: 'Theo tên phòng ban/ Lớp'
    },
    {
        value: 3,
        label: 'Theo tên tài khoản'
    },
    {
        value: 4,
        label: 'Theo họ tên'
    },
    {
        value: 5,
        label: 'Theo số điện thoại'
    },
]);

const breadcrumbs = [
    {'title': 'App', 'url': route('admins.school.index')},
    {'title': page.props.class?.app.name, 'url': route('admins.class.index', {app_id: page.props.class?.app.id})},
    {'title': page.props.class?.name, 'url' : ''},
]

</script>

<template>
    <Head title="Student"/>

    <SchoolLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Student</h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-11/12">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Danh sách Nhân viên/Học sinh</h1>
                <div class="relative flex gap-4">
                    <Link :href="route('admins.class.index', { app_id: app_id })" class="" >
                        <a-button class="mt-6 gray-btn">Quay lại</a-button>
                    </Link>
                    <Link
                        :href="route('admins.students.create', { class_id: class_id })"

                        v-if="userType === USER_TYPE_ADMIN || userType === USER_TYPE_DIRECTOR || userType === USER_TYPE_TEACHER"
                    >
                        <a-button class="mt-6" type="primary">Thêm mới</a-button>
                    </Link>
                    <a-button v-if="showDelete" class="mt-6" type="primary" @click="showModal = true">Kích hoạt</a-button>
                    <img v-if="showDelete" src="/images/delete.svg" @click="confirmDeleteMulti()" class="cursor-pointer mt-6">
                </div>
                <div class="filter-page mt-4 flex gap-5 flex-col md:flex-row">
                    <a-select
                        class="input-search md:w-[300px]"
                        v-model:value="formFilter.type_id"
                        show-search
                        placeholder="Tìm kiếm theo"
                        size="large"
                        :options="optionsType"
                        :filter-option="filterOption"
                        @keydown="onKeyDown"
                    ></a-select>
                    <a-input class="md:w-[300px]"
                        placeholder="Nhập tên tìm kiếm"
                        :allow-clear="true"
                        v-model:value="formFilter.search"
                    >
                        <!-- Sử dụng slot suffix để đặt icon -->
                        <template #suffix>
                            <SearchOutlined @click="onSearch" style="cursor: pointer"/>
                        </template>
                    </a-input>
                    <a-button @click="removeFilter" size="large">Xóa lọc</a-button>
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
                        :row-selection="rowSelection"
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
                            <template v-if="column.key === 'username'">
                                <div class="flex gap-2">
                                    <p
                                        :class="`flex items-center font-bold ${record.status === 'off' ? 'hide-class' : ''}`"
                                    >
                                        {{ record.username }}
                                    </p>
                                </div>
                            </template>
                            <template v-if="column.key === 'tel'">
                                <div class="flex gap-2">
                                    <p
                                        :class="`flex items-center font-bold ${record.status === 'off' ? 'hide-class' : ''}`"
                                    >
                                        {{ record.tel }}
                                    </p>
                                </div>
                            </template>
                            <template v-if="column.key === 'app'">
                                <div class="flex gap-2">
                                    <p
                                        :class="`flex items-center font-bold ${record.status === 'off' ? 'hide-class' : ''}`"
                                    >
                                        {{ record.app }}
                                    </p>
                                </div>
                            </template>
                            <template v-if="column.key === 'assignClass'">
                                <div class="flex gap-2">
                                    <a-dropdown>
                                        <template #overlay v-if="userType === USER_TYPE_ADMIN">
                                            <a-menu mode="vertical" :trigger-sub-menu-action="'click'">
                                                <a-sub-menu key="assign" >
                                                    <template #title>
                                                        <div class="flex items-center gap-2" @mouseenter="handleClickTitle(record.app_id)">
                                                            <span>Phòng ban/Lớp</span>
                                                        </div>
                                                    </template>
                                                    <a-menu-item :class="item.value === record.classes ? 'bg-blue-100' : ''" v-for="(item, index) in listApp" :key="index" @click="selectClass(record.id,record.app_id, item.value)">{{ item.label }}</a-menu-item>
                                                </a-sub-menu>
                                            </a-menu>
                                        </template>
                                        <a-button
                                            v-if="record.isAssign"
                                            :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                            size="large"
                                            type="primary"
                                        >
                                            {{record.class_name}}
                                        </a-button>
                                        <a-button
                                            v-else
                                            :class="`bg-[#b1b1b1] text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                            size="large"
                                        >
                                           Phân bổ
                                        </a-button>

                                    </a-dropdown>
                                </div>
                            </template>
                            <template v-if="column.key === 'active_code'">
                                <Link v-if="record.orders.length == 0">
                                    <a-button
                                        :class="`bg-[#b1b1b1] text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                    >
                                      Chưa kích hoạt
                                    </a-button>
                                </Link>
                                 <a-button v-else
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                        @click="showOrders(record.orders)"
                                        type="primary"
                                    >Đã kích hoạt
                                    </a-button>
                            </template>
                            <template v-if="column.key === 'assign'">
                                <Link v-if="!record.isAssignTask" :href="route('admins.practices.index', { class_id: class_id, student_id: record.id })">
                                    <a-button
                                        class="bg-[#b1b1b1] text-white"
                                        size="large"
                                    >Chưa giao</a-button>
                                </Link>
                                <Link v-else :href="route('admins.practices.index', { class_id: class_id, student_id: record.id })">
                                    <a-button
                                        class="text-white"
                                        size="large"
                                        type="primary"
                                    >Đã giao</a-button>
                                </Link>
                            </template>
                            <template v-if="column.key === 'result'">
                                <Link v-if="!record.is_result">
                                    <a-button
                                        :class="`bg-[#b1b1b1] text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                    >
                                     Kết quả
                                    </a-button>
                                </Link>
                                <Link v-else :href="route('admins.students.result', { user_id: record.id, class_id: record.class_id ? record.class_id : class_id })">
                                    <a-button
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                        type="primary"
                                    >Kêt quả
                                    </a-button>
                                </Link>

                            </template>
                            <template v-else-if="column.key === 'action'">
                                <div class="flex gap-6">
                                    <div
                                        :class="`cursor-pointer text-[15px] font-semibold ${record.status === 'off' ? 'hide-class' : ''}`"
                                    >
                                        <Link
                                            :href="
                                            route('admins.students.edit', {
                                              user_id: record.id,
                                              class_id: class_id
                                            })
                                          "
                                        >Sửa</Link
                                        >
                                    </div>
                                    <div
                                        :class="`cursor-pointer text-[15px] font-semibold ${record.status === 'off' ? 'hide-class' : ''}`"
                                    >
                                        <a-popconfirm
                                            placement="topRight"
                                            ok-text="Xóa"
                                            cancel-text="Bỏ qua"
                                            @confirm="confirm(record.id)"
                                        >
                                            <template #title>
                                                <p>Bạn có chắc chắn muốn xoá tài khoản này?</p>
                                            </template>
                                            Xóa
                                        </a-popconfirm>
                                    </div>
                                    <div
                                        :class="`cursor-pointer text-[15px] font-semibold ${record.status === 'off' ? 'hide-class' : ''}`"
                                    >
                                        <a-popconfirm
                                            placement="topRight"
                                            ok-text="Có"
                                            cancel-text="Bỏ qua"
                                            @confirm="resetPassword(record.id)"
                                        >
                                            <template #title>
                                                <p>Bạn có chắc chắn muốn reset mật khẩu?</p>
                                            </template>
                                            Reset mật khẩu
                                        </a-popconfirm>
                                    </div>
                                </div>
                            </template>
                        </template>
                        <template #footer>
                            <div class="flex items-center justify-end">
                                <!-- Pagination -->
                                <a-pagination v-bind="pagination" @change="onPageChange"/>
                                <!-- Icon plus -->
                                <Link :href="route('admins.students.create', { class_id: class_id })">
                                    <img
                                        v-if="userType === USER_TYPE_ADMIN || userType === USER_TYPE_DIRECTOR || userType === USER_TYPE_TEACHER"
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
        <a-modal
            v-model:open="showModal"
            centered
            :closable="false"
            :footer="null"
            :maskClosable="false"
        >
            <div class="flex flex-col items-center justify-center p-4">
                 <!-- Select box -->
                <div class="w-full">
                    <label for="option" class="block text-gray-700 font-medium mb-2">Chọn gói kích hoạt</label>
                     <select
                        id="option"
                        v-model="selected"
                        class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option v-for="item in options" :key="item.id" :value="item.id">
                            {{ item.name }}
                        </option>
                    </select>
                </div>
                <!-- Buttons -->
                <div class="flex justify-end w-full space-x-3 pt-4 border-t border-gray-200">
                    <button class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition" @click="showModal = false">
                    Đóng
                    </button>
                    <button class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition" @click="confirmOrder()">
                        Xác nhận
                    </button>
                </div>
            </div>
        </a-modal>

        <a-modal
            v-model:open="showModalOrder"
            centered
            :closable="false"
            :footer="null"
            :maskClosable="false"
        >
            <div class="flex flex-col items-center justify-center p-4">
                 <!-- Select box -->
                <div class="w-full">
                    <label for="option" class="block text-gray-700 font-medium mb-2">Chọn gói kích hoạt</label>
                     <select
                        id="option"
                        v-model="selected_order"
                        class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option v-for="item in orderList" :key="item.id" :value="item.id">
                            {{item.name}}
                        </option>
                    </select>
                </div>
                <!-- Buttons -->
                <div class="flex justify-end w-full space-x-3 pt-4 border-t border-gray-200">
                    <button class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition" @click="showModalOrder = false">
                    Đóng
                    </button>
                    <button class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition" @click="confirmCancelOrder()">
                        Hủy kích hoạt
                    </button>
                </div>
            </div>
        </a-modal>
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
.gray-btn {
  background-color: #d9d9d9;
  border: 1px solid #d9d9d9;
}
</style>

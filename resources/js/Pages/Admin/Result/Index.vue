<script setup>
import {Head, usePage} from '@inertiajs/vue3';
import {SearchOutlined} from '@ant-design/icons-vue';
import {ref, onMounted, nextTick} from 'vue';
import {Link} from '@inertiajs/vue3';
import {useToast} from 'vue-toastification';
import Sortable from 'sortablejs';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";

const props = defineProps({
    record: {
        type: Object,
    },
});
const toast = useToast();
const page = usePage();
const user = page.props.auth.user;
const showAdminMenu = !!user.user_type_id && user.user_type_id == 2;
const query = page.props.query;
const app_id = query.app_id;
const columns = [
    {
        title: 'STT',
        dataIndex: 'id',
        key: 'id',
        width: 80,
        align: 'center',
    },
    {
        title: 'Bài tập',
        dataIndex: 'name',
        key: 'name',
    },
    {
        title: 'Nhân viên/Học sinh',
        dataIndex: 'user',
        key: 'user',
        width: '20%',
    },
    {
        title: 'KQ luyện tập (Sao)',
        key: 'practical_result',
        dataIndex: 'practical_result',
    },
    {
        title: 'Thời gian hoàn thành',
        key: 'time_complete',
        dataIndex: 'time_complete',
    },
    {
        title: 'KQ học lý thuyết (Sao)',
        key: 'theoretical_result',
        dataIndex: 'theoretical_result',
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
    app_id: app_id
});
const tableRef = ref(null);
onMounted(async () => {
    await loadData();
});
const loadData = async () => {
    const params = {
        page: pagination.value.current,
        search: formFilter.value.search,
        app_id: formFilter.value.app_id
    };
    const res = await axios.get(route('admins.points.json.getResult', params));

    if (res.data.status || res.data.success) {
        data.value = res.data.data.data.map((v) => {
            return {
                id: v.id,
                name: v.name,
                user: v.user,
                practical_result: v.practical_result,
                time_complete: v.time_complete,
                theoretical_result: v.theoretical_result
            };
        });

        pagination.value.pageSize = res.data.data.per_page;
        pagination.value.total = res.data.data.total;
        pagination.value.current = res.data.data.current_page;
    }
};
const setupSortable = () => {
    nextTick(() => {
        const tbody = tableRef.value?.$el.querySelector('tbody');
        if (tbody) {
            Sortable.create(tbody, {
                animation: 150,
                onEnd: (evt) => {
                    let from = evt.oldIndex;
                    let to = evt.newIndex;
                    if (from === to || from === undefined || to === undefined) return;
                    //Nếu table có scroll thì phải giảm index đi 1
                    from -= 1
                    to -= 1
                    const updated = [...data.value];
                    const fromId = updated[from].id;
                    const toId = updated[to].id;
                    const movedItem = updated.splice(from, 1)[0];
                    updated.splice(to, 0, movedItem);
                    data.value = updated;
                    const params = {
                        from_id: fromId,
                        to_id: toId,
                    };
                    const res = axios.patch(route('apps.json.updatePosition', params));
                },
            });
        }
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
    loadData();
};
const rowSelection = ref({
    onChange: (selectedRowKeys, selectedRows) => {
        console.log(`selectedRowKeys: ${selectedRowKeys}`, 'selectedRows: ', selectedRows);
    },
    onSelect: (record, selected, selectedRows) => {
        console.log(record, selected, selectedRows);
    },
    onSelectAll: (selected, selectedRows, changeRows) => {
        console.log(selected, selectedRows, changeRows);
    },
});
const goBack = () => {
    window.location = route('admins.class.index', { app_id: app_id });
};
</script>

<template>
    <Head title="Result"/>

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Result</h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-11/12">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">Kết quả: {{ props.record.name }}</h1>
                <div class="flex items-center mt-4">
                    <a-button @click="goBack" class="custom-bg text-black" size="large">
                        Quay lại
                    </a-button>
                    <div class="ml-auto text-[#2C75E3] font-semibold">
                        Xuất kết quả
                    </div>
                </div>

                <div class="mt-4">
                    <a-table
                        :columns="columns"
                        :data-source="data"
                        :pagination="false"
                        ref="tableRef"
                        rowKey="id"
                        :scroll="{ x: 'max-content' }"
                        :row-selection="rowSelection"
                    >
                        <template #bodyCell="{ column, record, index }">
                            <template v-if="column.key === 'id'">
                                {{ index + 1 + pagination.pageSize * (pagination.current - 1) }}
                            </template>
<!--                            <template v-if="column.key === 'name'">-->
<!--                                <div class="flex gap-2">-->
<!--                                    <p-->
<!--                                        :class="`flex items-center font-bold ${record.status === 'off' ? 'hide-class' : ''}`"-->
<!--                                    >-->
<!--                                        {{ record.name }}-->
<!--                                    </p>-->
<!--                                </div>-->
<!--                            </template>-->
                            <template v-if="column.key === 'menu'">
                                <Link
                                    :href="
                                        route('books.create', {
                                          appId: record.id,
                                        })
                                      "
                                    v-if="!record.menu"
                                >
                                    <a-button
                                        :class="`bg-[#b1b1b1] text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                    >{{ record.menu }}
                                    </a-button>
                                </Link>
                                <Link
                                    :href="
                                        route('books.index', {
                                          appId: record.id,
                                        })
                                    "
                                    v-else
                                >
                                    <a-button
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                        type="primary"
                                    >{{ record.menu }}
                                    </a-button>
                                </Link>
                            </template>
                            <template v-if="column.key === 'import' && showAdminMenu">
                                <div class="flex gap-4 text-center">
                                    <a-button
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                        type="primary"
                                    >NV/HS
                                    </a-button>
                                    <a-button
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                        type="primary"
                                    >QL/GV
                                    </a-button>
                                </div>
                            </template>
                            <template v-if="column.key === 'export' && showAdminMenu">
                                <div class="flex gap-4 text-center">
                                    <a-button
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                        type="primary"
                                    >NV/HS
                                    </a-button>
                                    <a-button
                                        :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                        type="primary"
                                    >QL/GV
                                    </a-button>
                                </div>
                            </template>
                            <template v-if="column.key === 'result' && showAdminMenu">
                                <a-button
                                    :class="`bg-[#b1b1b1] text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                    size="large"
                                    v-if="!record.result"
                                >
                                    {{ record.result }}
                                </a-button>
                                <a-button
                                    v-else
                                    :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                    size="large"
                                    type="primary"
                                >{{ record.result }}
                                </a-button>
                            </template>
                            <template v-if="column.key === 'point' && showAdminMenu">
                                <Link href="#" v-if="!record.point">
                                    <a-button
                                        :class="`bg-[#b1b1b1] text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                        size="large"
                                    >
                                        {{ record.point }}
                                    </a-button>
                                </Link>
                                <a-button
                                    v-else
                                    :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                                    size="large"
                                    type="primary"
                                >{{ record.point }}
                                </a-button>
                            </template>
                            <template v-else-if="column.key === 'action'">
                                <div
                                    :class="`cursor-pointer text-[15px] font-semibold ${record.status === 'off' ? 'hide-class' : ''}`"
                                >
                                    <Link
                                        :href="
                                            route('admins.class.edit', {
                                              app_id: app_id,
                                              id: record.id,
                                            })
                                          "
                                    >Sửa</Link
                                    >
                                </div>
                            </template>
                        </template>
                        <template #footer>
                            <div class="flex items-center justify-end">
                                <!-- Pagination -->
                                <a-pagination v-bind="pagination" @change="onPageChange"/>
                                <!-- Icon plus -->
                                <Link :href="route('admins.class.create', { app_id: app_id })">
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

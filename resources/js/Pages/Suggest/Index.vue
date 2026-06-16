<script setup>
import { Head } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { reactive, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const query = page.props?.query || {};
const activeKey = ref(query?.activeTab ? query.activeTab : '1');

const options = ref([
    {
        value: '',
        label: 'Tất cả các App',
    },
    {
        value: 1,
        label: 'Ngày',
    },
    {
        value: 2,
        label: 'Tháng',
    },
]);
const optionsTwo = ref([
    {
        value: '',
        label: 'Tất cả',
    },
]);
const optionsThree = ref([
    {
        value: '',
        label: 'Câu',
    },
]);
const handleChange = (value) => {
    console.log(`selected ${value}`);
};
const filterOption = (input, option) => {
    return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};
const value = ref(1);
const valueContent = ref(2);
const valueTwo = ref('');
const valueThree = ref('');
const onSearch = (searchValue) => {
    console.log('use value', searchValue);
    console.log('or use this.value', value.value);
};
const columns = [
    {
        title: 'STT',
        dataIndex: 'key',
        key: 'key',
        width: 80,
        align: 'center',
    },
    {
        title: 'Tài khoản',
        dataIndex: 'user',
        key: 'user',
    },
    {
        title: 'Nội dung comment',
        key: 'content',
        dataIndex: 'content',
    },
    {
        title: 'Hình minh họa',
        key: 'image',
    },
];
const data = [
    {
        key: '1',
        user: 'Tên user',
        content: 'Nhân vật rất dễ thương',
        image: '',
    },
    {
        key: '2',
        user: 'Tên user',
        content: 'Giao diện đơn giản, dễ sử dụng',
        image: '',
    },
    {
        key: '3',
        user: 'Tên user',
        content: 'Màn đăng nhập hơi khó dùng',
        image: '',
    },
    {
        key: '4',
        user: 'Tên user',
        content: '',
        image: '',
    },
];
const data1 = [
    {
        key: '1',
        user: 'Tên user',
        content: 'Bài 1/Câu 1/Nội dung khó hiểu',
        image: '',
    },
    {
        key: '2',
        user: 'Tên user',
        content: 'Bài 1/Câu 1/Nội dung quá dễ với tôi',
        image: '',
    },
    {
        key: '3',
        user: 'Tên user',
        content: '',
        image: '',
    },
    {
        key: '4',
        user: 'Tên user',
        content: '',
        image: '',
    },
];
const pagination = reactive({
    current: 1,
    pageSize: 10,
    total: data.length,
});

const onPageChange = (page) => {
    pagination.current = page;
};
</script>

<template>
    <Head title="Suggest" />

    <SchoolLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Suggest
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-4/5 max-w-7xl sm:px-6 lg:px-8">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Danh sách các comment App 3 gốc
                </h1>
                <div class="relative mt-6 flex gap-4">
                    <a-button class="custom-bg mt-3 text-black" size="middle" @click="() => window.history.back()">
                        Quay lại
                    </a-button>
                    <a-tabs
                        class="w-full"
                        v-model:activeKey="activeKey"
                        :tabBarStyle="{ fontSize: '16px' }"
                    >
                        <a-tab-pane key="1" tab="Tính năng">
                            <div class="filter-page mt-4 flex gap-10">
                                <a-select
                                    class="input-search w-1/3"
                                    v-model:value="value"
                                    show-search
                                    placeholder="Tất cả khóa học"
                                    size="large"
                                    :options="options"
                                    :filter-option="filterOption"
                                    @change="handleChange"
                                ></a-select>
                            </div>
                            <div class="mt-8 w-full">
                                <a-table
                                    :columns="columns"
                                    :data-source="data"
                                    :pagination="false"
                                    :scroll="{ x: 'max-content' }"
                                >
                                    <template #bodyCell="{ column, record }">
                                        <template v-if="column.key === 'user'">
                                            <div class="flex gap-2">
                                                <img
                                                    src="/images/icon-image.png"
                                                />
                                                <a
                                                    class="flex items-center font-bold"
                                                >
                                                    {{ record.user }}
                                                </a>
                                            </div>
                                        </template>
                                        <template
                                            v-if="column.key === 'content'"
                                        >
                                            {{ record.content }}
                                        </template>
                                        <template
                                            v-else-if="column.key === 'image'"
                                        >
                                            <img
                                                v-if="record.image"
                                                :src="record.image"
                                                alt=""
                                            />
                                            <div v-else></div>
                                        </template>
                                    </template>
                                    <template #footer>
                                        <div
                                            class="flex items-center justify-end"
                                        >
                                            <!-- Pagination -->
                                            <a-pagination
                                                v-bind="pagination"
                                                @change="onPageChange"
                                            />
                                        </div>
                                    </template>
                                </a-table>
                            </div>
                        </a-tab-pane>
                        <a-tab-pane key="2" tab="Giao diện" force-render
                            ><div class="filter-page mt-4 flex gap-10">
                                <a-select
                                    class="input-search w-1/3"
                                    v-model:value="value"
                                    show-search
                                    placeholder="Tất cả khóa học"
                                    size="large"
                                    :options="options"
                                    :filter-option="filterOption"
                                    @change="handleChange"
                                ></a-select>
                            </div>
                            <div class="mt-8 w-full">
                                <a-table
                                    :columns="columns"
                                    :data-source="data"
                                    :pagination="false"
                                    :scroll="{ x: 'max-content' }"
                                >
                                    <template #bodyCell="{ column, record }">
                                        <template v-if="column.key === 'user'">
                                            <div class="flex gap-2">
                                                <img
                                                    src="/images/icon-image.png"
                                                />
                                                <a
                                                    class="flex items-center font-bold"
                                                >
                                                    {{ record.user }}
                                                </a>
                                            </div>
                                        </template>
                                        <template
                                            v-if="column.key === 'content'"
                                        >
                                            {{ record.content }}
                                        </template>
                                        <template
                                            v-else-if="column.key === 'image'"
                                        >
                                            <img
                                                v-if="record.image"
                                                :src="record.image"
                                                alt=""
                                            />
                                            <div v-else></div>
                                        </template>
                                    </template>
                                    <template #footer>
                                        <div
                                            class="flex items-center justify-end"
                                        >
                                            <!-- Pagination -->
                                            <a-pagination
                                                v-bind="pagination"
                                                @change="onPageChange"
                                            />
                                        </div>
                                    </template>
                                </a-table>
                            </div>
                        </a-tab-pane>
                        <a-tab-pane key="3" tab="Nội dung"
                            ><div class="filter-page mt-4 flex gap-10">
                                <a-select
                                    class="input-search w-1/3"
                                    v-model:value="valueContent"
                                    show-search
                                    placeholder="Tất cả khóa học"
                                    size="large"
                                    :options="options"
                                    :filter-option="filterOption"
                                    @change="handleChange"
                                ></a-select>
                            <a-select
                                    class="input-search w-1/3"
                                    v-model:value="valueTwo"
                                    show-search
                                    placeholder="Tất cả"
                                    size="large"
                                    :options="optionsTwo"
                                    :filter-option="filterOption"
                                    @change="handleChange"
                                >
                                </a-select>
                                <a-select
                                    class="input-search w-1/3"
                                    v-model:value="valueThree"
                                    show-search
                                    placeholder="Tất cả"
                                    size="large"
                                    :options="optionsThree"
                                    :filter-option="filterOption"
                                    @change="handleChange"
                                ></a-select>
                            </div>
                            <div class="mt-8 w-full">
                                <a-table
                                    :columns="columns"
                                    :data-source="data1"
                                    :pagination="false"
                                    :scroll="{ x: 'max-content' }"
                                >
                                    <template #bodyCell="{ column, record }">
                                        <template v-if="column.key === 'user'">
                                            <div class="flex gap-2">
                                                <img
                                                    src="/images/icon-image.png"
                                                />
                                                <a
                                                    class="flex items-center font-bold"
                                                >
                                                    {{ record.user }}
                                                </a>
                                            </div>
                                        </template>
                                        <template
                                            v-if="column.key === 'content'"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'suggest.detailCommentContent',
                                                        record.key,
                                                    )
                                                "
                                                >{{ record.content }}
                                            </Link>
                                        </template>
                                        <template
                                            v-else-if="column.key === 'image'"
                                        >
                                            <img
                                                v-if="record.image"
                                                :src="record.image"
                                                alt=""
                                            />
                                            <div v-else></div>
                                        </template>
                                    </template>
                                    <template #footer>
                                        <div
                                            class="flex items-center justify-end"
                                        >
                                            <!-- Pagination -->
                                            <a-pagination
                                                v-bind="pagination"
                                                @change="onPageChange"
                                            />
                                        </div>
                                    </template>
                                </a-table>
                            </div>
                        </a-tab-pane>
                    </a-tabs>
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
.custom-background {
    background-color: #ffb800;
}
</style>

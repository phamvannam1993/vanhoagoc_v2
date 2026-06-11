<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import MasterLayout from '@/Layouts/MasterLayout.vue';
import { SearchOutlined } from '@ant-design/icons-vue';
import { computed, nextTick, onMounted, ref } from "vue";
import { Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import Sortable from "sortablejs";

const props = defineProps({
    practice: {
        type: Object,
    }
});

const toast = useToast();
const page = usePage();
const query = page.props.query;
const practice_id = query.practice_id;
const app_id = query.app_id;
const book_id = query.book_id;
const week_id = query.week_id;
const exercise_item_id = query.exercise_item_id;
const formFilter = ref({
    search: '',
    practice_id: parseInt(practice_id),
});
const practiceId = ref(parseInt(practice_id));

const breadcrumbs = [
    {'title': 'App', 'url': route('apps.dashboard')},
    {'title': props.practice.week.book.app.name, 'url': route('books.index', {appId: props.practice.week.book.app.id})},
    {'title': props.practice.week.book.title, 'url': route('weeks.index', {
        app_id: props.practice.week.book.app.id,
        book_id: props.practice.week.book.id
    })},
    {'title': props.practice.week.name, 'url': route('lessons.index', {
        app_id: props.practice.week.book.app.id,
        book_id: props.practice.week.book.id,
        week_id: props.practice.week.id,
    })},
    {'title': props.practice.name, 'url': ''},
]
const listType = ref([
    {
        value: 1,
        route_edit: 'questionEditors.getEditChooseCorrect',
        route_detail: 'questionEditors.getDetailChooseCorrect',
    },
    {
        value: 2,
        route_edit: 'questionEditors.getEditDragDrop',
        route_detail: 'questionEditors.getDetailDragDrop',
    },
    {
        value: 3,
        route_edit: 'questionEditors.getEditConnectSentence',
        route_detail: 'questionEditors.getDetailConnectSentence',
    },
    {
        value: 4,
        route_edit: 'questionEditors.getEditArrange',
        route_detail: 'questionEditors.getDetailArrange',
    },
    {
        value: 5,
        route_edit: 'questionEditors.getEditMatchPhoto',
        route_detail: 'questionEditors.getDetailMatchPhoto',
    },
]);
const options = ref(
    page.props.list.map((v) => {
        return {
            value: v.id,
            label: v.name,
        };
    }),
);

const practiceMoveOptions = page.props.list
.filter(v => v.id != practice_id)
.map(v => ({
    value: v.id,
    label: v.name
}));

const uploadExcel = ref(null);
const triggeUploadExcel = () => {
  if (uploadExcel.value) {
    uploadExcel.value.value = '';
    uploadExcel.value.click();
  }
};
const handleFileExcel = async (event) => {
  const file = event.target.files[0];

  if (!file) return;

  const formData = new FormData();
  formData.append('file', file);
  formData.append('type', 'excel');
  const res = await axios.post('/api/upload-file', formData);
  console.log('b: ', res)
};

const handleChange = (value) => {
    formFilter.value.practice_id = value;
    practiceId.value = value;
    loadData();
};
const filterOption = (input, option) => {
    return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};
const columns = [
    {
        title: 'STT',
        dataIndex: 'id',
        key: 'id',
        width: 80,
        align: 'center',
    },
    {
        title: 'Nội dung câu hỏi',
        dataIndex: 'name',
        key: 'name',
        width: '50%',
    },
    {
        title: 'Mẫu / chọn mẫu',
        key: 'sample',
        dataIndex: 'sample',
    },
    {
        title: '',
        key: 'action',
    },
];
const data = ref([]);
const pagination = ref({
    current: 1,
    pageSize: 20,
    total: 0,
});
const tableRef = ref(null);
onMounted(async () => {
    await loadData();
    setupSortable();
});
const loadData = async () => {
    const params = {
        page: pagination.value.current,
        pageSize: pagination.value.pageSize,
        search: formFilter.value.search,
        practice_id: practiceId.value,
        app_id: formFilter.value.app_id,
        book_id: formFilter.value.book_id,
        week_id: formFilter.value.week_id,
        ...(exercise_item_id && { exercise_item_id }),
    };
    const res = await axios.get(route('questionEditors.json.list', params));

    if (res.data.status) {
        data.value = res.data.data.data.map((v) => {
            return {
                id: v.id,
                name: v.title,
                menu: 'Danh sách câu hỏi',
                count: '20 bài',
                tem_playable_id: v.tem_playable_id,
                template: v.template,
                status: v.status ? v.status : 'on',
                template_question: v?.template_question
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
                    const fromId = updated[from].id
                    const toId = updated[to].id
                    const movedItem = updated.splice(from, 1)[0];
                    updated.splice(to, 0, movedItem);
                    data.value = updated;
                    const params = {
                        from_id: fromId,
                        to_id: toId
                    };
                    const res = axios.patch(route('questionEditors.json.updatePosition', params))
                },
            });
        }
    });
};
// Hàm computed để lấy item route
const getItemRoute = computed(() => {
    return (type) => {
        return listType.value.find((r) => r.value === type);
    };
});
const onPageChange = (page) => {
    pagination.value.current = page;
    loadData();
};
const onSearch = () => {
    loadData();
};
const removeFilter = () => {
    formFilter.value.search = '';
    formFilter.value.selectBook = null;
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
const confirmDeleteMulti = () => {
    const idsToDelete = listRecordDelete.value.map(item => item.id);
    axios.post(route('questionEditors.json.deleteMultiple'), {
       ids: idsToDelete
    })
    .then(response => {
        if (response.status === 200) {
            toast.success('Xóa câu hỏi luyện tập thành công');
            setTimeout(function () {
               location.href = ''
            }, 500);
        }
    })
    .catch(error => {
        toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
}
const confirm = (value) => {
    axios
        .delete(route('questionEditors.json.delete', value))
        .then((response) => {
            if (response.status === 200) {
                toast.success('Xóa câu hỏi luyện tập thành công');
                loadData();
            }
        })
        .catch(() => {
            toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
        });
};
const hideApp = (id) => {
    axios
        .post(route('questionEditors.json.visible', { id: id, status: 'off' }))
        .then((response) => {
            if (response.status === 200) {
                toast.success('Cập nhật status app thành công');
                setTimeout(function () {
                    location.href =
                        route('questionEditors', {
                            practiceId,
                            app_id,
                            book_id,
                            week_id
                        });
                }, 500);
            }
        })
        .catch(() => {
            toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
        });
};
const showApp = (id) => {
    axios
        .post(route('questionEditors.json.visible', { id: id, status: 'on' }))
        .then((response) => {
            if (response.status === 200) {
                toast.success('Cập nhật status app thành công');
                setTimeout(function () {
                    location.href =
                        route('questionEditors', {
                            practiceId,
                            app_id,
                            book_id,
                            week_id
                        });
                }, 500);
            }
        })
        .catch(() => {
            toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
        });
};
// Thay đổi class của dòng theo logic
const rowClassName = (record, index) => {
    if (record.status === 'off') {
        return 'grey-row';
    }
    return '';
};
const goBack = () => {
    window.location = route('lessons.index', { app_id, book_id, week_id });
};
const listPageSize = ref([
    {
        value: 20,
        label: '20/page'
    },
    {
        value: 50,
        label: '50/page'
    },
    {
        value: 100,
        label: '100/page'
    },
    {
        value: 200,
        label: '200/page'
    },
]);
const handleChangePageSize = () => {
    loadData();
}
const onKeyDown = (event) => {
    // Chặn tất cả các phím nhập liệu vào input
    if (event.key.length === 1) {
        event.preventDefault();
    }
}

const showOption = ref(false)
const toggleOption = () => {
  showOption.value = !showOption.value
}

const isCopy = ref(false)
const copyQuestion = async (to_practice_id) => {
  if(listRecordDelete.value.length == 0) {
     toast.error('Vui lòng chọn tuần');
     return false
  }
  if(isCopy.value) {
    return false
  }
  isCopy.value = true
   axios
    .post(route('questionEditors.json.copyData', { app_id:app_id, book_id: book_id, week_id: week_id, to_practice_id: to_practice_id, question_editor_ids: listRecordDelete.value.map(item => item.id) }))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Sao chép tuần thành công');
        showOption.value = false
      }
    })
    .catch(() => {
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
       isCopy.value = false
    });
}
const resetPosition = () => {

    axios
    .post(route('questionEditors.json.reset-position', { book_id: book_id, week_id: week_id, practice_id: practice_id}))
    .then((response) => {
        if (response.status === 200) {
            toast.success('Cập nhật status app thành công');
            loadData();
        }
    }).catch(() => {
        toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
};

const duplicateQuestion = () => {
  if(listRecordDelete.value.length == 0) {
    toast.error('Vui lòng chọn câu hỏi');
    return false
  }
  axios
    .post(route('questionEditors.json.duplicate', { question_ids: listRecordDelete.value.map(item => item.id) }))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Sao chép câu hỏi thành công');
        loadData();
      }
    })
    .catch(() => {
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
}
</script>

<template>
    <Head title="Question Exercise" />

    <MasterLayout :breadcrumbs=breadcrumbs>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Questions Exercise
            </h2>
        </template>

        <div class="app-page py-4">
            <div class="content-page mx-auto w-full md:w-10/12">
                <h1 class="text-[30px] font-bold text-[#2C75E3]">
                    Danh sách câu hỏi luyện tập
                </h1>
                <div class="relative mt-6 flex items-center gap-4 flex-wrap">
                    <a-button @click="goBack" class="custom-bg text-black" size="large">
                        Quay lại
                    </a-button>
                    <Link
                        :href="
                            route('questionEditors.createExercise', {
                                practice_id: practiceId,
                                week_id: week_id,
                                app_id: app_id,
                                book_id: book_id,
                            })
                        "
                    >
                        <a-button type="primary" size="large"
                            >Thêm mới</a-button
                        >
                    </Link>
                    <Link
                        :href="
                            route('questions.upload', {
                                practice_id: practiceId,
                                week_id: week_id,
                                app_id: app_id,
                                book_id: book_id,
                            })
                        "
                    >
                        <a-button type="primary" size="large"
                            >Upload câu hỏi</a-button
                        >
                    </Link>
                    <Link
                        :href="
                            route('questionEditors.importExercise', {
                                practice_id: practiceId,
                                week_id: week_id,
                                app_id: app_id,
                                book_id: book_id,
                            })
                        "
                    >
                        <a-button size="large">Import Excel</a-button>
                    </Link>
                    <a-button type="primary" size="large" @click="resetPosition">Reset thứ tự</a-button>
                    <div class="flex gap-2">
                        <img
                            class="h-[24px] cursor-pointer"
                            src="/images/icon-only-download.png"
                            alt="icon-download"
                            @click="triggeUploadExcel"
                        />
                        <input
                            type="file"
                            accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                            class="hidden"
                            ref="uploadExcel"
                            @change="handleFileExcel"
                        />
                    </div>
                    <div class="relative">
                        <img :src="showOption ? '/images/copy.svg' : '/images/copy_hide.svg'" @click="toggleOption()" class="cursor-pointer">
                        <div class="jump-box" v-if="showOption">
                            <span class="jump-title">Chuyển đến:</span>
                            <ul>
                            <li v-for="(item, index) in practiceMoveOptions" :key="index" @click="copyQuestion(item.value)">
                                {{ item.label }}
                            </li>
                            </ul>
                        </div>
                    </div>
                    <img :src="'/images/copy_paste.svg'" @click="duplicateQuestion()" class="cursor-pointer">
                    <img v-if="showDelete" src="/images/delete.svg" @click="confirmDeleteMulti()" class="cursor-pointer">
                </div>

                <div class="filter-page mt-4 flex flex-col md:flex-row gap-5">
                    <a-select
                        class="input-search md:w-[300px]"
                        v-model:value="practiceId"
                        show-search
                        placeholder="Tất cả khóa học"
                        size="large"
                        :options="options"
                        :filter-option="filterOption"
                        @change="handleChange"
                         @keydown="onKeyDown"
                    ></a-select>
                    <a-input
                        class="md:w-[300px]"
                        placeholder="Tìm kiếm"
                        :allow-clear="true"
                        v-model:value="formFilter.search"
                    >
                        <template #suffix>
                            <SearchOutlined
                                @click="onSearch"
                                style="cursor: pointer"
                            />
                        </template>
                    </a-input>
                    <a-button @click="removeFilter" size="large"
                        >Xóa lọc</a-button
                    >
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
                            <template v-if="column.key === 'name'">
                                <div :class="`flex gap-2 ${record.status === 'off' ? 'hide-class' : ''}`">
                                    <p class="flex items-center font-bold">
                                        <Link :href=" route('questionEditors.editGame',
                                            {
                                                id: record.id,
                                                app_id: app_id,
                                                book_id: book_id,
                                                week_id: week_id,
                                                practice_id:practiceId,
                                                template_id: record.template
                                            })">
                                            {{ record.name }}
                                        </Link>
                                    </p>
                                </div>
                            </template>
                            <template v-if="column.key === 'sample'">
                                <img
                                    v-if="record?.template_question"
                                    :src="record?.template_question.image_url"
                                    alt=""
                                    :class="`w-[114px] h-[62px] cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                                />
                            </template>
                            <template v-else-if="column.key === 'action'">
                                <div class="flex items-center gap-10">
                                    <img
                                        v-if="record.status === 'off'"
                                        @click="showApp(record.id)"
                                        :class="`h-[24px] cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                                        src="/images/icon-eye-closed.png"
                                        alt="eye-closed"
                                    />
                                    <img
                                        v-else
                                        @click="hideApp(record.id)"
                                        :class="`h-[24px] cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                                        src="/images/icon-eye-open.png"
                                        alt="eys-open"
                                    />
                                    <div
                                        :class="`cursor-pointer text-[15px] font-semibold ${record.status === 'off' ? 'hide-class' : ''}`"
                                    >
                                        <Link
                                            :href="
                                                route('questionEditors.editGame',
                                                    {
                                                        id: record.id,
                                                        app_id: app_id,
                                                        book_id: book_id,
                                                        week_id: week_id,
                                                        practice_id:practiceId,
                                                        template_id: record.template
                                                    },
                                                )
                                            "
                                            >Sửa</Link>
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
                                                <p>
                                                    Bạn có chắc chắn muốn xoá
                                                    bài học này?
                                                </p>
                                            </template>
                                            Xóa
                                        </a-popconfirm>
                                    </div>
                                    <img
                                        :class="`h-[24px] cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                                        v-if="
                                            record.key === '2' ||
                                            record.key === '5'
                                        "
                                        src="/images/icon-comment-deactive.png"
                                        alt="comment-deactive"
                                    />
                                    <img
                                        :class="`h-[24px] cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                                        v-else
                                        src="/images/icon-comment-active.png"
                                        alt="comment-active"
                                    />
                                </div>
                            </template>
                        </template>
                        <template #footer>
                            <div
                                v-if="data.length > 0"
                                class="flex items-center justify-end gap-2"
                            >
                                <!-- Pagination -->
                                <a-pagination
                                    v-bind="pagination"
                                    @change="onPageChange"
                                    :show-size-changer="false"
                                />
                                <a-select
                                    ref="select"
                                    v-model:value="pagination.pageSize"
                                    @focus="focus"
                                    @change="handleChangePageSize"
                                >
                                    <a-select-option v-for="(item, index) in listPageSize" :value="item.value">{{ item.label }}</a-select-option>
                                </a-select>
                                <!-- Icon plus -->
                                <Link
                                    :href="
                                        route(
                                            'questionEditors.createExercise',
                                            {
                                                app_id: app_id,
                                                book_id: book_id,
                                                week_id: week_id,
                                                practice_id: practiceId,
                                            },
                                        )
                                    "
                                >
                                    <img
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
    </MasterLayout>
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
.grey-row {
    background-color: darkgray;
}
.jump-box {
  background: #fff;
  border-radius: 10px;
  padding: 10px 14px;
  box-shadow: 0 0 8px rgba(0, 0, 0, 0.15);
  display: inline-block;
  font-family: sans-serif;
  position: absolute;
  z-index: 999;
  margin-left: 0px;
  width: max-content;
}

.jump-title {
  color: #007bff;
  font-weight: bold;
  display: block;
  margin-bottom: 6px;
}

.jump-box ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.jump-box ul li {
  padding: 4px 0;
  cursor: pointer;
  transition: color 0.2s;
}

.jump-box ul li:hover {
  color: #007bff;
}
</style>

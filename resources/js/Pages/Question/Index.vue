<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { SearchOutlined } from '@ant-design/icons-vue';
import { nextTick, onMounted, reactive, ref } from "vue";
import { Link } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import Sortable from "sortablejs";

const page = usePage();
const query = page.props.query;
const practice_id = query.practice_id;
const app_id = query.app_id;
const book_id = query.book_id;
const week_id = query.week_id;
const practiceId = ref(parseInt(practice_id));
const options = ref(
  page.props.list.map((v) => {
    return {
      value: v.id,
      label: v.name,
    };
  }),
);
const toast = useToast();
const onKeyDown = (event) => {
    // Chặn tất cả các phím nhập liệu vào input
    if (event.key.length === 1) {
        event.preventDefault();
    }
}
const handleChange = (value) => {
  formFilter.value.practice_id = value;
  practiceId.value = value;
  loadData();
};
const filterOption = (input, option) => {
  return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const formFilter = ref({
  search: '',
  practice_id: practiceId.value,
});

const columns = [
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
    width: '50%',
  },
  {
    title: 'Thể loại',
    key: 'menu',
    dataIndex: 'menu',
  },
  {
    title: 'Thời gian hiển thị',
    key: 'time_display',
    dataIndex: 'time_display',
  },
  {
    title: '',
    key: 'action',
  },
];

const data = ref([]);
const pagination = ref({
  current: 1,
  pageSize: 1,
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
    search: formFilter.value.search,
    practice_id: formFilter.value.practice_id,
  };
  const res = await axios.get(route('questions.json.list', params));

  if (res.data.status) {
    data.value = res.data.data.data.map((v) => {
      return {
        id: v.id,
        name: v.name,
        menu: v.type,
        count: '20 bài',
        created_at: v.created_at,
        time_display: v.time_display,
        beauty_time_display: v.beauty_time_display,
        has_answer: v.has_answer,
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
                    const res = axios.patch(route('questions.json.updatePosition', params))
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
const goBack = () => {
  window.location = route('lessons.index', { app_id, book_id, week_id });
};
const confirm = (value) => {
  axios
    .delete(route('questions.json.delete', value))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Xóa câu hỏi thành công');
        loadData();
      }
    })
    .catch(() => {
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
};
const showAnswer = ref(false);
const infoAnswer = ref({
    question_id: null,
    practice_id: practiceId,
    app_id: app_id,
    book_id: book_id,
    week_id: week_id,
    question_type: ''
});
const inputs = reactive([]);

const answerPopup = ref(false);
const answer = (questionId, questionType) => {
    activeRowKey.value = questionId;
    inputs.splice(0, inputs.length);
    showAnswer.value = true;
    infoAnswer.value.question_id = questionId;
    infoAnswer.value.question_type = questionType;
    inputs.push( { name: "", right_answer: false, type: infoAnswer.value.question_type, question_id: infoAnswer.value.question_id },)
    answerPopup.value = true;
};
const editAnswer = async (questionId, questionType) => {
    activeRowKey.value = questionId;
    inputs.splice(0, inputs.length);
    showAnswer.value = true;
    infoAnswer.value.question_id = questionId;
    infoAnswer.value.question_type = questionType;
    const res = await axios.get(route('questions.json.answer', {question_id: questionId}));
    if (res.data.status) {
        let arrayAnswer = res.data.data.list;
        inputs.splice(0, inputs.length, ...arrayAnswer);
    }
    answerPopup.value = true;
};
const addInput = () => {
    inputs.push({
        name: "",
        right_answer: false,
        type: infoAnswer.value.question_type,
        question_id: infoAnswer.value.question_id,
    });
};
const collectedValues = ref([]);
// Hàm xóa input theo index
const removeInput = (index) => {
    inputs.splice(index, 1);
};
const save = () => {
    collectedValues.value = [...inputs];
    const params = {
        question_id: infoAnswer.value.question_id,
        answer: collectedValues.value,
        app_id: infoAnswer.value.app_id,
        week_id: infoAnswer.value.week_id,
        book_id: infoAnswer.value.book_id,
        practice_id: infoAnswer.value.practice_id,
    };
    axios
        .post(route('questions.json.storeAnswer'), params)
        .then((response) => {
            if (response.data.status) {
                toast.success('Câu trả lời thành công');
            } else {
                // form error
            }
        })
        .catch((error) => {
            toast.error(error);
        });
    answerPopup.value = false;
};
const activeRowKey = ref(null);
const rowClassName = (record) => {
    return record.id === activeRowKey.value ? 'active-row' : '';
};

const fileInputRef = ref(null);
const importing = ref(false);
const triggerImport = () => fileInputRef.value?.click();
const onFileSelected = async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    event.target.value = '';
    const formData = new FormData();
    formData.append('file', file);
    formData.append('practice_id', practice_id ?? '');
    formData.append('app_id', app_id ?? '');
    formData.append('book_id', book_id ?? '');
    formData.append('week_id', week_id ?? '');
    importing.value = true;
    try {
        const res = await axios.post(route('questions.json.import'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        if (res.data.status) {
            toast.success(`Đã import ${res.data.imported} câu hỏi`);
            loadData();
        } else {
            toast.error(res.data.message ?? 'Import thất bại');
        }
    } catch (e) {
        toast.error('Đã có lỗi xảy ra khi import');
    } finally {
        importing.value = false;
    }
};
</script>

<template>
  <Head title="Question" />

  <SchoolLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Questions
      </h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-11/12">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">
          Danh sách từ khóa/ câu hỏi Video
        </h1>
        <div class="relative mt-6 flex items-center gap-4">
          <a-button @click="goBack" class="custom-bg text-black" size="large">
            Quay lại
          </a-button>
          <Link
            :href="
              route('questions.create', {
                practice_id: practiceId,
                app_id: app_id,
                book_id: book_id,
                week_id: week_id,
              })
            "
          >
            <a-button type="primary" size="large">Thêm mới</a-button>
          </Link>
          <a-button size="large" @click="triggerImport" :loading="importing">
            Import Excel
          </a-button>
          <input ref="fileInputRef" type="file" accept=".xlsx,.xls" class="hidden" @change="onFileSelected" />
        </div>

        <div class="filter-page mt-4 flex gap-5 flex-col md:flex-row">
          <a-select
            class="input-search md:w-[300px]"
            v-model:value="formFilter.practice_id"
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
          >
            <!-- Sử dụng slot suffix để đặt icon -->
            <template #suffix>
              <SearchOutlined @click="onSearch" style="cursor: pointer" />
            </template>
          </a-input>
          <a-button @click="removeFilter" size="large">Xóa lọc</a-button>
        </div>
        <div class="mt-4 relative">
          <a-table class="" :columns="columns" :data-source="data" :pagination="false" ref="tableRef" rowKey="id"
                   :row-class-name="(record) => rowClassName(record)" :scroll="{ x: 'max-content' }">
            <template #bodyCell="{ column, record, index }">
              <template v-if="column.key === 'id'">
                {{ index + 1 + pagination.pageSize * (pagination.current - 1) }}
              </template>
              <template v-if="column.key === 'name'">
                <div class="flex gap-2">
                  <a class="flex items-center font-bold">
                    {{ record.name }}
                  </a>
                </div>
              </template>
              <template v-if="column.key === 'menu'">
                <a-button
                  class="custom-background text-white"
                  size="large"
                  v-if="record.menu === 'Trắc nghiệm'"
                  style="width: 160px"
                >
                  Trắc nghiệm
                </a-button>
                <a-button
                  v-else
                  class="text-white"
                  size="large"
                  style="width: 160px"
                  type="primary"
                >
                  Từ khóa
                </a-button>
              </template>
              <template v-if="column.key === 'time_display'">
                  {{ record.beauty_time_display }}
              </template>
              <template v-else-if="column.key === 'action'">
                <div class="flex items-center gap-4">
                  <div class="cursor-pointer text-[15px] font-semibold">
                    <Link
                      :href="
                        route('questions.edit', {
                          question_id: record.id,
                          practice_id: practiceId,
                          app_id: app_id,
                          book_id: book_id,
                          week_id: week_id,
                        })
                      "
                      >Sửa</Link
                    >
                  </div>
                  <div class="cursor-pointer text-[15px] font-semibold">
                    <a-popconfirm
                      placement="topRight"
                      ok-text="Xóa"
                      cancel-text="Bỏ qua"
                      @confirm="confirm(record.id)"
                    >
                      <template #title>
                        <p>Bạn có chắc chắn muốn xoá câu hỏi này?</p>
                      </template>
                      Xóa
                    </a-popconfirm>
                  </div>
                </div>
              </template>
            </template>
            <template #footer>
              <div v-if="data.length > 0" class="flex items-center justify-end">
                <!-- Pagination -->
                <a-pagination v-bind="pagination" @change="onPageChange" />
                <!-- Icon plus -->
                <Link
                  :href="
                    route('questions.create', {
                      practice_id: practiceId,
                      app_id: app_id,
                      book_id: book_id,
                      week_id: week_id,
                    })
                  "
                >
                  <img src="/images/icon-plus.png" alt="icon-plus" />
                </Link>
              </div>
            </template>
          </a-table>
            <a-modal v-model:open="answerPopup" centered :closable="false" :footer="null" :maskClosable="true">
                <div class="flex flex-col items-center justify-center p-4">
                    <div v-if="showAnswer">
                        <div v-if="infoAnswer.question_type === 'Trắc nghiệm'">
                            <div class="relative mt-6 w-full">
                                <label
                                    for="appNameInput"
                                    class="block text-base font-semibold text-black"
                                >
                                    Câu trả lời
                                </label>
                                <div class="mt-2 italic">
                                    Tích vào ô vuông để chọn câu trả lời đúng
                                </div>
                            </div>
                            <div class="mt-6 w-full">
                                <div class="gap-4">
                                    <div class="mt-4">
                                        <div
                                            class="mt-2 flex gap-4 items-center"
                                            v-for="(input, index) in inputs"
                                            :key="index"
                                        >
                                            <a-checkbox class="custom-checkbox" v-model:checked="inputs[index].right_answer"></a-checkbox>
                                            <a-input
                                                :placeholder="`Câu trả lời ${index + 1}`"
                                                :allow-clear="true"
                                                size="large"
                                                v-model:value="inputs[index].name"
                                            >
                                            </a-input>
                                            <a-button type="primary" danger @click="removeInput(index)">
                                                Xóa
                                            </a-button>
                                        </div>
                                    </div>
                                </div>
                                <div class="float-right flex mt-4 gap-4">
                                    <img
                                        @click="addInput"
                                        class="cursor-pointer"
                                        src="/images/icon-plus.png"
                                        alt=""
                                    />
                                    <a-button
                                        @click="save"
                                        class="text-white"
                                        size="middle"
                                        type="primary"
                                    >
                                        Lưu
                                    </a-button>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <div class="relative mt-6 w-4/5">
                                <label
                                    for="appNameInput"
                                    class="block text-base font-semibold text-black"
                                >
                                    Câu trả lời
                                </label>
                            </div>
                            <div class="mt-6 w-4/5">
                                <div class="gap-4">
                                    <div class="mt-4">
                                        <div
                                            class="mt-2 flex gap-4 items-center"
                                            v-for="(input, index) in inputs"
                                            :key="index"
                                        >
                                            <a-input
                                                :placeholder="`Câu trả lời ${index + 1}`"
                                                :allow-clear="true"
                                                size="large"
                                                v-model:value="inputs[index].name"
                                            >
                                            </a-input>
                                        </div>
                                        <a-button
                                            class="text-white mt-4"
                                            size="middle"
                                            type="primary"
                                            @click="save"
                                        >
                                            Lưu
                                        </a-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a-modal>

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
.active-row {
    background-color: #e6f7ff !important;
}
</style>

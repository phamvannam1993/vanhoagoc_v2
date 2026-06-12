<script setup>
import { Head } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { SearchOutlined } from '@ant-design/icons-vue';
import { nextTick, onMounted, ref } from "vue";
import { Link, usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import Sortable from "sortablejs";

const props = defineProps({
  listWeek: {
    type: Object,
  },
  week: {
    type: Object,
  }
});

const breadcrumbs = [
    {'title': 'App', 'url': route('apps.dashboard')},
    {'title': props.week.book.app.name, 'url': route('books.index', {appId: props.week.book.app.id})},
    {'title': props.week.book.title, 'url': route('weeks.index', {app_id: props.week.book.app.id, book_id: props.week.book.id})},
    {'title': props.week.name, 'url': ''},
]

const toast = useToast();
const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const book_id = query.book_id;
const week_id = query.week_id;
const weekId = ref(parseInt(week_id));
const weekOptions = props.listWeek.map((v) => {
  return { value: v.id, label: v.name };
});

const weekMoveOptions = props.listWeek
.filter(v => v.id != week_id)
.map(v => ({
    value: v.id,
    label: v.name
}));

const formFilter = ref({
  search: '',
  app_id: app_id,
  book_id: book_id,
  week_id: weekId.value,
});
const handleChangeCourse = (value) => {
  formFilter.value.week_id = value;
  weekId.value = value;
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
    title: 'Tên bài',
    dataIndex: 'name',
    key: 'name',
    width: '20%',
  },
  {
    title: 'Bài đọc',
    dataIndex: 'reading',
    key: 'reading',
    width: '9%',
  },
  {
    title: 'Video',
    key: 'video',
    dataIndex: 'video',
    width: '9%',
  },
  {
    title: 'Từ khóa video',
    key: 'keyVideo',
    dataIndex: 'keyVideo',
    width: '9%',
  },
    {
    title: 'Sách nói',
    key: 'voice',
    dataIndex: 'voice',
    width: '8%',
  },
  {
    title: 'Hành động',
    key: 'action',
    width: '35%',
    align: 'center',
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
    app_id: formFilter.value.app_id,
    book_id: formFilter.value.book_id,
    week_id: formFilter.value.week_id,
  };
  const res = await axios.get(route('lessons.json.list', params));

  if (res.data.status) {
    data.value = res.data.data.data.map((v) => {
      return {
        id: v.id,
        name: v.name,
        menu: 'Danh sách câu hỏi',
        count: '20 bài',
        status: v.status,
        lesson_doc: v.lesson_doc,
        lesson_video: v.lesson_video,
        has_question_editors: v.has_question_editors,
        has_questions: v.has_questions,
        has_questions_voice: v.has_questions_voice,
        count_question: v.count_question,
        question_editors_count: v.question_editors_count,
        img: v.img,
        pdf: v.pdf
      };
    });

    pagination.value.pageSize = res.data.data.per_page;
    pagination.value.total = res.data.data.total;
    pagination.value.current = res.data.data.current_page;
  }
};
const onKeyDown = (event) => {
    // Chặn tất cả các phím nhập liệu vào input
    if (event.key.length === 1) {
        event.preventDefault();
    }
}
const onPageChange = (page) => {
  pagination.value.current = page;
  loadData();
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
                    // from -= 1
                    // to -= 1
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
                    const res = axios.patch(route('lessons.json.updatePosition', params))
                },
            });
        }
    });
};
const onSearch = () => {
  loadData();
};
const removeFilter = () => {
  formFilter.value.search = '';
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
    axios.post(route('lessons.json.deleteMultiple'), {
       ids: idsToDelete
    })
    .then(response => {
        if (response.status === 200) {
            toast.success('Xóa bài học thành công');
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
    .delete(route('lessons.json.delete', value))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Xóa bài học thành công');
          loadData();
      }
    })
    .catch(() => {
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
};
const downloadListLesson = () => {
  const params = {
    app_id: formFilter.value.app_id,
    book_id: formFilter.value.book_id,
    week_id: weekId.value,
  };
  axios
    .get(route('lessons.json.downloadLesson', params))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Tải bài học thành công');
        setTimeout(function () {
          location.href = route('lessons.index', {
            app_id: app_id,
            book_id: book_id,
            week_id: weekId.value,
          });
        }, 500);
      }
    })
    .catch(() => {
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
};
const hideApp = (id) => {
  // todo update status
  axios
    .post(route('lessons.json.visible', { id: id, status: 'off' }))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Cập nhật status app thành công');
        setTimeout(function () {
          location.href = route('lessons.index', {
            app_id: app_id,
            book_id: book_id,
            week_id: weekId.value,
          });
        }, 500);
      }
    })
    .catch(() => {
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
};
const showApp = (id) => {
  // todo update status
  axios
    .post(route('lessons.json.visible', { id: id, status: 'on' }))
    .then((response) => {
      if (response.status === 200) {
        toast.success('Cập nhật status app thành công');
        setTimeout(function () {
          location.href = route('lessons.index', {
            app_id: app_id,
            book_id: book_id,
            week_id: weekId.value,
          });
        }, 500);
      }
    })
    .catch(() => {
      toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
};

const goBack = () => {
  window.location = route('weeks.index', { app_id, book_id });
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

const resetPosition = () => {

    axios
    .post(route('lessons.json.reset-position', { book_id: book_id, week_id: week_id, }))
    .then((response) => {
        if (response.status === 200) {
            toast.success('Cập nhật status app thành công');
            loadData();
        }
    }).catch(() => {
        toast.error('Đã có lỗi xảy ra, vui lòng thử lại sau!');
    });
};
const showOption = ref(false)
const toggleOption = () => {
  showOption.value = !showOption.value
}

const isCopy = ref(false)
const copyLesson = async (to_week_id) => {
  if(listRecordDelete.value.length == 0) {
     toast.error('Vui lòng chọn tuần');
     return false
  }
  if(isCopy.value) {
    return false
  }
  isCopy.value = true
   axios
    .post(route('lessons.json.copyData', { app_id:app_id, book_id: book_id, to_week_id: to_week_id, practice_ids: listRecordDelete.value.map(item => item.id) }))
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
</script>

<template>
  <Head title="Lessons" />

  <SchoolLayout :breadcrumbs=breadcrumbs>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Lessons</h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-10/12">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Danh sách bài học</h1>
        <div class="relative mt-6 flex items-center gap-4 flex-wrap">
          <a-button @click="goBack" class="custom-bg text-black" size="large">
            Quay lại
          </a-button>
          <Link
            :href="
              route('lessons.create', {
                app_id: app_id,
                week_id: weekId,
                book_id: book_id,
              })
            "
          >
            <a-button type="primary" size="large">Thêm mới</a-button>
          </Link>
          <Link
            :href="
              route('lessons.generateQuiz', {
                app_id: app_id,
                week_id: weekId,
                book_id: book_id,
              })
            "
          >
            <a-button type="primary" size="large">Upload câu hỏi</a-button>
          </Link>
            <a-button type="primary" size="large" @click="resetPosition">Reset thứ tự</a-button>
          <img v-if="showDelete" src="/images/delete.svg" @click="confirmDeleteMulti()" class="cursor-pointer">
          <img
            @click="downloadListLesson"
            class="h-[24px] cursor-pointer"
            src="/images/icon-download.png"
            alt="icon-download"
          />
            <div class="relative">
                <img :src="showOption ? '/images/copy.svg' : '/images/copy_hide.svg'" @click="toggleOption()" class="cursor-pointer">
                <div class="jump-box" v-if="showOption">
                    <span class="jump-title">Chuyển đến:</span>
                    <ul>
                    <li v-for="(item, index) in weekMoveOptions" :key="index" @click="copyLesson(item.value)">
                        {{ item.label }}
                    </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="filter-page mt-4 flex gap-5 flex-col md:flex-row">
          <a-select
            class="input-search md:w-[300px]"
            v-model:value="formFilter.week_id"
            show-search
            placeholder="Tất cả khóa học"
            size="large"
            :options="weekOptions"
            :filter-option="filterOption"
            @change="handleChangeCourse"
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
        <div class="mt-4">
          <a-table :columns="columns" :data-source="data" :pagination="false" ref="tableRef" rowKey="id"  :row-selection="rowSelection">
            <template #bodyCell="{ column, record, index }">
              <template v-if="column.key === 'id'">
                {{ index + 1 + pagination.pageSize * (pagination.current - 1) }}
              </template>
              <template v-if="column.key === 'name'">
                <div class="flex gap-2">
                  <!--                                    <img src="/images/icon-image.png"  :class="`${record.status === 'off' ? 'hide-class' : ''}`"/>-->
                  <a
                    :class="`flex items-center font-bold ${record.status === 'off' ? 'hide-class' : ''}`"
                  >
                    <Link
                        :href=" route('questionEditors.index', {
                            practice_id: record.id,
                            app_id: app_id,
                            book_id: book_id,
                            week_id: weekId,
                        })"
                    >
                    {{ record.name }}
                    <span v-if="record.question_editors_count > 0">&nbsp;({{ record.question_editors_count }})</span>
                    </Link>
                  </a>
                </div>
              </template>
              <template v-if="column.key === 'reading'">
                <div class="flex items-center gap-2">
                  <Link
                    v-if="record.lesson_doc || record.img || record.pdf"
                    :href="
                      route('lessons.reading.edit', {
                        practice_id: record.id,
                        app_id: app_id,
                        book_id: book_id,
                        week_id: weekId,
                      })
                    "
                  >
                    <img
                      src="/images/icon-edit-text.png"
                      alt=""
                      :class="`h-[38px] cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                    />
                  </Link>
                  <Link
                    v-else
                    :href="
                      route('lessons.reading.create', {
                        practice_id: record.id,
                        app_id: app_id,
                        book_id: book_id,
                        week_id: weekId,
                      })
                    "
                  >
                    <img
                      src="/images/icon-edit-text-grey.png"
                      alt=""
                      :class="`h-[38px] cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                    />
                  </Link>
                </div>
              </template>
              <template v-if="column.key === 'video'">
                <Link
                  v-if="record.lesson_video"
                  :href="
                    route('lessons.video.edit', {
                      practice_id: record.id,
                      app_id: app_id,
                      book_id: book_id,
                      week_id: weekId,
                    })
                  "
                >
                  <img
                    src="/images/icon-edit-video.png"
                    alt=""
                    :class="`h-[38px] cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                  />
                </Link>
                <Link
                  v-else
                  :href="
                    route('lessons.video.create', {
                      practice_id: record.id,
                      app_id: app_id,
                      book_id: book_id,
                      week_id: weekId,
                    })
                  "
                >
                  <img
                    src="/images/icon-edit-video-grey.png"
                    alt=""
                    :class="`h-[38px] cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                  />
                </Link>
              </template>
              <template v-if="column.key === 'keyVideo'">
                <Link
                  v-if="record.has_questions"
                  :href="
                    route('questions.index', {
                      practice_id: record.id,
                      app_id: app_id,
                      book_id: book_id,
                      week_id: weekId,
                    })
                  "
                >
                    <a-button
                        :class="`text-white cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                        class=""
                        size="large"
                        type="primary"
                    >
                        Từ khóa ({{ record.count_question }})
                    </a-button>
                </Link>
                <Link
                  v-else
                  :href="
                    route('questions.index', {
                      practice_id: record.id,
                      app_id: app_id,
                      book_id: book_id,
                      week_id: weekId,
                    })
                  "
                >
                  <img
                    src="/images/icon-key-grey.png"
                    alt=""
                    :class="`h-[38px] cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                  />
                </Link>
              </template>
                <template v-if="column.key === 'voice'">
                    <Link
                        v-if="record.has_questions_voice"
                        :href="
                    route('lessons.voice.edit', {
                      practice_id: record.id,
                      app_id: app_id,
                      book_id: book_id,
                      week_id: weekId,
                    })
                  "
                    >
                        <a-button
                            :class="`text-white cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                            class=""
                            size="large"
                            type="primary"
                        >
                            Sách nói
                        </a-button>
                    </Link>
                    <Link
                        v-else
                        :href="
                    route('lessons.voice.create', {
                      practice_id: record.id,
                      app_id: app_id,
                      book_id: book_id,
                      week_id: weekId,
                    })
                  "
                    >
                        <img
                            src="/images/icon_voice.png"
                            alt=""
                            :class="`h-[38px] cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                        />
                    </Link>
                </template>
              <template v-if="column.key === 'menu'">
                <Link
                  v-if="!record.has_question_editors"
                  :href="
                    route('questionEditors.createExercise', {
                      practice_id: record.id,
                      app_id: app_id,
                      book_id: book_id,
                      week_id: weekId,
                    })
                  "
                >
                  <a-button
                    :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                    size="large"
                    type="primary"
                    style="width: 160px; background-color: #cccccc"
                  >
                    Thêm câu hỏi
                  </a-button>
                </Link>
                <Link
                  v-else
                  :href="
                    route('questionEditors.index', {
                      practice_id: record.id,
                      app_id: app_id,
                      book_id: book_id,
                      week_id: weekId,
                    })
                  "
                >
                  <a-button
                    :class="`text-white ${record.status === 'off' ? 'hide-class' : ''}`"
                    size="large"
                    style="width: 160px"
                    type="primary"
                  >
                    Danh sách câu hỏi
                  </a-button>
                </Link>
              </template>
              <template v-else-if="column.key === 'action'">
                <div class="flex flex-wrap gap-2 items-center justify-center">
                  <Link
                    :href="
                      route('lessons.aiCreate', {
                        practice_id: record.id,
                        app_id: app_id,
                        book_id: book_id,
                        week_id: weekId,
                      })
                    "
                  >
                    <a-button
                      type="primary"
                      size="small"
                      :class="`btn-ai-create ${record.status === 'off' ? 'hide-class' : ''}`"
                    >
                      ✨ Tạo AI
                    </a-button>
                  </Link>
                  <Link
                    :href="
                      route('assignedExercises.index', {
                        app_id: app_id,
                        book_id: book_id,
                        week_id: weekId,
                        practice_id: record.id,
                      })
                    "
                  >
                    <a-button
                      size="small"
                      :class="`${record.status === 'off' ? 'hide-class' : ''}`"
                    >
                      📋 Giao bài
                    </a-button>
                  </Link>
                  <img
                    v-if="record.status === 'off'"
                    @click="showApp(record.id)"
                    :class="`h-[20px] btn-view cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                    src="/images/icon-eye-closed.png"
                    alt="eye-closed"
                  />
                  <img
                    v-else
                    @click="hideApp(record.id)"
                    :class="`h-[20px] btn-view cursor-pointer ${record.status === 'off' ? 'hide-class' : ''}`"
                    src="/images/icon-eye-open.png"
                    alt="eys-open"
                  />
                  <a-dropdown trigger="click">
                    <a class="ant-dropdown-link" @click.prevent>
                      <img src="/images/icon-dots.svg" class="h-[20px] cursor-pointer" alt="menu" />
                    </a>
                    <template #overlay>
                      <a-menu>
                        <a-menu-item>
                          <Link
                          :href="
                          route('lessons.edit', {
                            practice_id: record.id,
                            app_id: app_id,
                            book_id: book_id,
                            week_id: weekId,
                          })
                        ">Sửa</Link>
                        </a-menu-item>
                        <a-menu-item danger>
                          <a-popconfirm
                            placement="topRight"
                            ok-text="Xóa"
                            cancel-text="Bỏ qua"
                            @confirm="confirm(record.id)"
                          >
                            <template #title>
                              <p>Bạn có chắc chắn muốn xoá bài học này?</p>
                            </template>
                            Xóa
                          </a-popconfirm>
                        </a-menu-item>
                      </a-menu>
                    </template>
                  </a-dropdown>
                </div>
              </template>
            </template>
            <template #footer>
              <div v-if="data.length > 0" class="flex items-center justify-end gap-2">
                <!-- Pagination -->
                <a-pagination v-bind="pagination" @change="onPageChange" :show-size-changer="false"/>
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
                    route('lessons.create', {
                      app_id: app_id,
                      week_id: weekId,
                      book_id: book_id,
                    })
                  "
                >
                  <img src="/images/icon-plus.png" alt="icon-plus" />
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

.btn-ai-create.ant-btn-primary {
  background: linear-gradient(120deg, #3b82f6, #6d5cf6);
  border: none;
  font-weight: 600;
  box-shadow: 0 6px 16px rgba(76, 84, 246, 0.3);
}
.btn-ai-create.ant-btn-primary:hover {
  filter: brightness(1.05);
  background: linear-gradient(120deg, #3b82f6, #6d5cf6);
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

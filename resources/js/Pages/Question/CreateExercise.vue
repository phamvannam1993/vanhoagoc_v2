<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import MasterLayout from '@/Layouts/MasterLayout.vue';
import { Field } from 'vee-validate';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const { list, types, practice } = defineProps({
    list: {
        type: Array,
    },
    types: {
        type: Array,
    },
    practice:  {
        type: Object
    },
});

const breadcrumbs = [
    {'title': 'App', 'url': route('apps.dashboard')},
    {'title': practice.week.book.app.name, 'url': route('books.index', {appId: practice.week.book.app.id})},
    {'title': practice.week.book.title, 'url': route('weeks.index', {
        app_id: practice.week.book.app.id,
        book_id: practice.week.book.id
    })},
    {'title': practice.week.name, 'url': route('lessons.index', {
        app_id: practice.week.book.app.id,
        book_id: practice.week.book.id,
        week_id: practice.week.id,
    })},
    {'title': practice.name, 'url': route('questionEditors.index', {
        app_id: practice.week.book.app.id,
        book_id: practice.week.book.id,
        week_id: practice.week.id,
        practice_id: practice.id,
    })},
]

const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const book_id = query.book_id;
const week_id = query.week_id;
const practice_id = query.practice_id || 0;
const open = ref(false);
const type = ref(null);
const template_id = ref(0);
const previewImg = ref('');
const errorMessage = ref('');
const options = ref(types);
const createGame = () => {
  if (template_id.value === 0 || practice_id === 0) {
    errorMessage.value = 'Bạn cần chọn mẫu';
  } else {
    window.location = route('questionEditors.createGame', {
      practice_id,
      template_id: template_id.value,
      app_id,
      book_id,
      week_id,
    });
  }
};

const handleChange = (value) => {
  type.value = value;
  template_id.value = 0;
};

const selectSample = (id, img) => {
  template_id.value = id;
  previewImg.value = img
};

const getImageSrc = (item) => {
  return `/images/games/${item}`;
};

const handleOk = () => {
  open.value = false;
};

const viewSample = () => {
  open.value = true;
};
const goBack = () => {
  window.location = route('questionEditors.index', {
    app_id,
    book_id,
    week_id,
    practice_id,
  });
};
</script>

<template>
  <Head title="Create Exercise" />

  <MasterLayout :breadcrumbs=breadcrumbs>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Create Exercise
      </h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Thêm mới câu hỏi</h1>
        <div>
          <div class="relative mt-6 w-4/5">
            <label
              for="appNameInput"
              class="block text-base font-semibold text-black"
            >
              Chọn game
            </label>
            <div class="mt-2">
              <div class="flex gap-4">
                <Field name="type" v-model="type" class="w-1/2">
                  <a-select
                    class="input-search w-1/2"
                    v-model:value="type"
                    show-search
                    placeholder="Tất cả mẫu"
                    size="large"
                    :options="options"
                    @change="handleChange"
                  ></a-select>
                </Field>
                <InputError class="mt-2" :message="errorMessage" />
                <div class="flex cursor-pointer items-center gap-2">
                  <Link
                    :href="
                      route('questionEditors.importExercise', {
                        app_id: app_id,
                        book_id: book_id,
                        week_id: week_id,
                        practice_id: practice_id,
                      })
                    "
                  >
                    <div class="flex gap-2">
                      <span>Tải lên</span>
                      <img
                        class="h-[24px] cursor-pointer"
                        src="/images/icon-upload-file.png"
                        alt="icon-download"
                      />
                    </div>
                  </Link>
                </div>
              </div>
            </div>
          </div>
          <div class="relative mt-6">
            <div class="grid grid-cols-4 gap-4">
              <img
                v-for="(item, index) in list"
                v-show="item.type === type"
                :key="index"
                :src="item.image_url"
                @click="selectSample(item.id, item.image_url)"
                :alt="'Image ' + index"
                :class="`image-sample h-auto w-full cursor-pointer rounded shadow ${item.id === template_id ? 'active' : ''}`"
              />
            </div>
          </div>
          <div class="relative mt-4 flex w-4/5 gap-4">
            <a-button @click="goBack" class="custom-bg text-black" size="large">
              Quay lại
            </a-button>
            <span
              class="css-1p3hq3p ant-btn ant-btn-primary ant-btn-lg cursor-pointer rounded-lg border border-solid bg-[#2C75E3] p-[0.5rem] text-white"
              @click="createGame"
            >
              Đi tiếp
            </span>
            <a-button
              @click="viewSample"
              class="custom-bg text-black"
              size="large"
            >
              Xem mẫu
            </a-button>
          </div>
        </div>
      </div>
    </div>
    <a-modal v-model:open="open" @ok="handleOk" :width="800">
      <template #title>
        <h1 class="text-[20px] font-bold text-[#2C75E3]">Mẫu</h1>
      </template>
      <div
        class="custom-height-sample relative mt-6 flex w-full items-center justify-center rounded-2xl border border-solid border-blue-400 bg-white p-4 pt-2 max-md:max-w-full"
      >
        <img :src="previewImg" alt="" />
      </div>
      <template #footer>
        <a-button key="submit" type="primary" @click="handleOk"
          >Xác nhận
        </a-button>
      </template>
    </a-modal>
  </MasterLayout>
</template>
<style scoped>
.active {
  border: 2px solid red;
}
</style>

<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SchoolLayout from "@/Layouts/SchoolLayout.vue";
import { ErrorMessage, Field, Form } from 'vee-validate';
import * as yup from 'yup';
import InputError from '@/Components/InputError.vue';
import { reactive, ref } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();
const page = usePage();
const query = page.props.query;
const app_id = query.app_id;
const book_id = query.book_id;
const week_id = query.week_id;
const practice_id = query.practice_id;

const form = useForm({
  name: '',
  question_type: 'choice',
  answer_des: '',
  point_true: 10,
  point_false: 5,
  time_display: 325,
});

const options = ref([
  { value: 'choice', label: 'Trắc nghiệm' },
  { value: 'button', label: 'Từ khóa' },
]);

const answers = reactive([
  { name: '', right_answer: false },
]);

const rules = {
  name: yup.string().required('Câu hỏi không được để trống'),
};

const addAnswer = () => {
  answers.push({ name: '', right_answer: false });
};

const removeAnswer = (index) => {
  answers.splice(index, 1);
};

const handleChange = () => {};

const filterOption = (input, option) =>
  option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;

const goBack = () => {
  window.location = route('questions.index', { app_id, book_id, week_id, practice_id });
};

const removeContent = () => {
  form.name = '';
  form.question_type = 'choice';
  form.answer_des = '';
  form.point_true = 10;
  form.point_false = 5;
  form.time_display = 325;
  answers.splice(0, answers.length, { name: '', right_answer: false });
};

const handleSubmit = async () => {
  const params = {
    name: form.name,
    question_type: form.question_type,
    answer_des: form.answer_des,
    point_true: form.point_true,
    point_false: form.point_false,
    time_display: form.time_display,
    practice_id,
  };

  try {
    const res = await axios.post(route('questions.json.store'), params);
    if (!res.data.status) return;

    const questionId = res.data.data?.question_id;

    if (form.question_type === 'choice' && questionId) {
      const answerParams = {
        question_id: questionId,
        answer: answers.map(a => ({
          name: a.name,
          right_answer: a.right_answer,
          type: form.question_type,
          question_id: questionId,
        })),
        practice_id,
        app_id,
        book_id,
        week_id,
      };
      await axios.post(route('questions.json.storeAnswer'), answerParams);
    }

    toast.success('Tạo câu hỏi thành công');
    setTimeout(() => {
      location.href = route('questions.index', { app_id, book_id, week_id, practice_id });
    }, 500);
  } catch (error) {
    toast.error('Đã có lỗi xảy ra');
  }
};
</script>

<template>
  <Head title="Create Question" />

  <SchoolLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">App</h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-4/5 sm:px-6 lg:px-8">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Thêm câu hỏi / từ khóa</h1>
        <Form @submit="handleSubmit">
          <div class="relative mt-6 w-4/5">
            <label class="block text-base font-semibold text-black">Câu hỏi</label>
            <div class="mt-2">
              <Field :rules="rules.name" name="question" v-model="form.name">
                <a-textarea placeholder="Bạn điền nội dung" :allow-clear="true" v-model:value="form.name" />
              </Field>
              <ErrorMessage class="text-sm text-red-600" name="question" />
              <InputError class="mt-2" :message="form.errors.name" />
            </div>
          </div>

          <div class="relative mt-6 w-4/5">
            <div class="flex gap-4">
              <div class="relative mt-4 w-1/2">
                <label class="block text-base font-normal text-black">Thời gian hiển thị / bấm nút (giây)</label>
                <div class="mt-2">
                  <a-input :allow-clear="true" size="large" v-model:value="form.time_display" />
                </div>
              </div>
              <div class="relative mt-4 w-1/2">
                <label class="block text-base font-normal text-black">Loại câu trả lời</label>
                <div class="mt-2">
                  <a-select class="input-search w-full" v-model:value="form.question_type" size="large"
                    :options="options" :filter-option="filterOption" @change="handleChange" />
                </div>
              </div>
            </div>
            <div class="flex gap-4">
              <div class="relative mt-4 w-1/2">
                <label class="block text-base font-normal text-black">Điểm cộng trả lời đúng</label>
                <div class="mt-2">
                  <a-input :allow-clear="true" size="large" v-model:value="form.point_true" />
                </div>
              </div>
              <div class="relative mt-4 w-1/2">
                <label class="block text-base font-normal text-black">Điểm trừ trả lời sai</label>
                <div class="mt-2">
                  <a-input :allow-clear="true" size="large" v-model:value="form.point_false" />
                </div>
              </div>
            </div>
          </div>

          <!-- Phần đáp án trắc nghiệm -->
          <div v-if="form.question_type === 'choice'" class="relative mt-6 w-4/5">
            <label class="block text-base font-semibold text-black">Đáp án</label>
            <div class="mt-1 text-sm italic text-gray-500">Tích vào ô vuông để chọn câu trả lời đúng</div>
            <div class="mt-4 space-y-3">
              <div v-for="(item, index) in answers" :key="index" class="flex items-center gap-4">
                <a-checkbox v-model:checked="answers[index].right_answer" />
                <a-input :placeholder="`Câu trả lời ${index + 1}`" :allow-clear="true" size="large"
                  v-model:value="answers[index].name" class="flex-1" />
                <a-button type="primary" danger @click="removeAnswer(index)">Xóa</a-button>
              </div>
            </div>
            <div class="mt-4 flex justify-end gap-3">
              <a-button @click="addAnswer">+ Thêm đáp án</a-button>
            </div>
          </div>

          <div class="relative mt-6 flex w-4/5 justify-end gap-4">
            <img @click="goBack" class="h-[34px] cursor-pointer" src="/images/icon-button-back.png" alt="" />
            <a-button @click="removeContent" class="text-white" size="middle" type="primary" danger>Xóa</a-button>
            <a-button class="text-white" size="middle" type="primary" html-type="submit">Lưu</a-button>
          </div>
        </Form>
      </div>
    </div>
  </SchoolLayout>
</template>

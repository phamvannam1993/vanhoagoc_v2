<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import MasterLayout from '@/Layouts/MasterLayout.vue';
import { ref, onMounted } from 'vue';
import { useToast } from 'vue-toastification';
import { Field, Form } from 'vee-validate';

const toast = useToast();
const page = usePage();
const query = page.props.query;
const type_id = parseInt(query.type_id);
const user_id = parseInt(query.user_id);
const listApps = ref([]);

onMounted(async () => {
  await loadData();
});
onMounted(async () => {
  await loadData();
});

const form = useForm({
    app_ids: [],
    book_ids: [],
    week_ids: [],
});

const loadData = async () => {
    const params = {
        user_id: user_id,
    };
    const res = await axios.get(route('users.json.jsonEditAccessModule', params));

    if (res.data.status) {
        let userModules = res.data.userModules;

        listApps.value = res.data.data.data.map((v) => {
            // Find out if any week in any book is active (for the app)
            let isChildWeekActive = v.books.some((book) =>
                book.weeks.some((week) =>
                    userModules.some((userModule) => userModule.module_id == week.id && userModule.module_type == 3)
                )
            );
            // Find out if any book is active (for the app)
            let isChildBookActive = v.books.some((book) =>
                userModules.some((userModule) => userModule.module_id == book.id && userModule.module_type == 2)
            );
            // App active if either directly active, or any child is active
            let isActive = userModules.some((userModule) => userModule.module_id == v.id && userModule.module_type == 1);

            if (isActive) {
                form.app_ids.push(v.id);
            }
            isActive = isActive || isChildBookActive || isChildWeekActive;

            return {
                id: v.id,
                name: v.name,
                active: isActive,
                books: v.books.map((book) => {
                    // Book active if directly active or any child week is active
                    let isChildWeekActive = book.weeks.some((week) =>
                        userModules.some(
                            (userModule) =>
                                userModule.module_id == week.id &&
                                userModule.module_type == 3
                        )
                    );
                    let isActive = userModules.some(
                        (userModule) => userModule.module_id == book.id && userModule.module_type == 2
                    );
                    if (isActive) {
                        form.book_ids.push(book.id);
                    }
                    isActive = isActive || isChildWeekActive;

                    return {
                        id: book.id,
                        title: book.title,
                        active: isActive,
                        weeks: book.weeks.map((week) => {
                            let isActive = userModules.some((userModule) => userModule.module_id == week.id && userModule.module_type == 3);
                            if (isActive) {
                                form.week_ids.push(week.id);
                            }
                            return {
                                id: week.id,
                                name: week.name,
                                active: isActive,
                            };
                        }),
                    };
                }),
            };
        });
    }
};

const goBack = () => {
  window.location = route('users.member', { type_id: type_id });
};

const handleSubmit = async () => {

    const payload = {
        ...form,
        user_id: user_id,
    }
    try {
        let res = await axios.post(route('users.updateAccessModule'), payload);
        if (res.data.status) {
            toast.success('Cập nhật quyền sử dụng thành công');
            window.location.href = route('users.member', { type_id: type_id });
        } else {
            toast.error(res.data.messages);
        }

    } catch (error) {
        console.error('Error submitting form:', error);
    }
};

const toggleApp = (appIndex) => {
    listApps.value[appIndex].active = !listApps.value[appIndex]?.active;
};

const toggleBook = (appIndex, bookIndex) => {
    listApps.value[appIndex].books[bookIndex].active = !listApps.value[appIndex].books[bookIndex]?.active;
};
</script>

<template>
  <Head title="Assign role" />

  <MasterLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Assign Role
      </h2>
    </template>

    <div class="app-page py-4">
      <div class="content-page mx-auto w-full md:w-10/12">
        <h1 class="text-[30px] font-bold text-[#2C75E3]">Cấp quyền sử dụng</h1>
        <Form @submit="handleSubmit" v-slot="{ errors }" class="w-full">
            <div class="mt-2">
                <div v-for="(app, appIndex) in listApps" :key="app.id" class="mb-6 border border-gray-200 p-4 rounded-lg hover:bg-gray-50 transition-all duration-200">
                    <!-- Book Name -->
                    <div class="flex items-center text-lg font-semibold text-gray-800 cursor-pointer">
                        <Field
                        v-model="form.app_ids"
                        :name="`app_ids_${app.id}`"
                        type="checkbox"
                        :value="app.id"
                        class="mr-2 h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-0"
                        />
                        <div @click="toggleApp(appIndex)" class="flex items-center">
                            <div>{{ app.name }}</div>
                            <span v-if="app.books.length > 0" class="text-blue-500 text-xl mr-2">+</span>
                        </div>

                    </div>

                    <!-- Display weeks if book.active is true -->
                    <div v-if="app.active" v-for="(book, bookIndex) in app.books" :key="book.id" class="ml-5 mt-4 pl-4 border-l-4 border-blue-500">
                        <div class="flex items-center text-md font-medium text-gray-700 cursor-pointer">
                            <Field
                                v-model="form.book_ids"
                                :name="`book_ids_${book.id}`"
                                type="checkbox"
                                :value="book.id"
                                class="mr-2 h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-0"
                                />
                                <div @click="toggleBook(appIndex, bookIndex)" class="flex items-center">
                                    <div>{{ book.title }}</div>
                                    <span v-if="book.weeks.length > 0" class="text-blue-500 text-xl mr-2">+</span>
                                </div>
                        </div>

                        <!-- Display practices if week.active is true -->
                        <div v-if="book.active" class="ml-5 mt-2">
                            <div v-for="week in book.weeks" :key="week.id" class="flex items-center mb-4">
                                <!-- Custom checkbox styling -->
                                <Field
                                v-model="form.week_ids"
                                :name="`week_ids_${week.id}`"
                                type="checkbox"
                                :value="week.id"
                                class="mr-2 h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-0"
                                />
                                <label class="text-gray-600 text-sm mt-1 cursor-pointer" >{{ week.name }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex justify-center gap-4">
                <img @click="goBack" class="h-[34px] cursor-pointer" src="/images/icon-button-back.png" alt="" />
                <div class="text-center">
                    <a-row>
                        <a-col :span="24">
                            <a-button class="w-[100px] text-white" size="middle" type="primary" html-type="submit" >
                                Lưu
                            </a-button>
                        </a-col>
                    </a-row>
                </div>
            </div>
        </Form>
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

.grey-row {
  background-color: darkgray;
}
</style>

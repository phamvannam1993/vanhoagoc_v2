<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { Field, Form, ErrorMessage } from 'vee-validate';
import * as yup from 'yup';
import InputError from '@/Components/InputError.vue';

const form = useForm({
  email: '',
  password: '',
  checked: false,
});

const rules = {
  email: yup.string().required('Email không được để trống'),
  password: yup.string().required('Password không được để trống'),
};

const handleSubmit = () => {
  form.post(route('hr.login'), {
    onFinish: () => form.errors = {},
  });
};

const changeInput = () => {
  form.errors = {};
};
</script>

<template>
  <Head title="Login" />
  <div class="login-page">
    <div class="main-page w-full">
      <div class="relative min-h-[80px] w-full bg-[#041C38]">
        <img
          class="absolute left-auto top-1/4 px-20"
          src="/images/logo.png"
          alt="logo"
        />
      </div>
      <div class="info-login mt-28 overflow-hidden">
        <div class="text-center text-[32px] font-bold text-[#2C75E3]">
          Quản lý nhân sự
        </div>
        <div class="email-password m-auto mb-5 w-4/12">
          <Form @submit="handleSubmit">
            <div>
              <Field name="email" :rules="rules.email" v-model="form.email">
                <a-input
                  class="mt-12"
                  size="large"
                  placeholder="Email hoặc tên đăng nhập"
                  v-model:value="form.email"
                  @change="changeInput('email')"
                >
                  <template #prefix>
                    <img src="/images/icon-email.png" />
                  </template>
                </a-input>
              </Field>
              <ErrorMessage class="text-sm text-red-600" name="email" />
              <InputError class="mt-2" :message="form.errors.email" />
            </div>
            <div>
              <Field
                name="password"
                :rules="rules.password"
                v-model="form.password"
              >
                <a-input-password
                  class="mt-12"
                  size="large"
                  placeholder="Mật khẩu"
                  v-model:value="form.password"
                  @change="changeInput('password')"
                >
                  <template #prefix>
                    <img
                      class="h-[22px] w-[22px]"
                      src="/images/icon-password.png"
                    />
                  </template>
                </a-input-password>
              </Field>
              <ErrorMessage class="text-sm text-red-600" name="password" />
              <InputError class="mt-2" :message="form.errors.password" />
            </div>
            <a-checkbox class="mt-8" v-model:checked="form.checked"
              >Lưu mật khẩu</a-checkbox
            >
            <button
              type="submit"
              class="mt-8 w-full rounded-2xl bg-blue-600 px-16 py-3 font-medium text-[#FFFFFF] focus:outline-none max-md:max-w-full max-md:px-5"
              tabindex="0"
            >
              Đăng nhập
            </button>
          </Form>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped lang="scss">
.login-page {
  .main-page {
    .info-login {
      .email-password {
        .ant-input-affix-wrapper {
          border-color: #4096ff;
          border-inline-end-width: 1px;
        }
      }
    }
  }
}
</style>

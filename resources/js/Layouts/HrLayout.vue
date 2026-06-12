<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import MenuNavLink from '@/Components/MenuNavLink.vue';
import { useToast } from 'vue-toastification';

const showingNavigationDropdown = ref(false);
const notificationCount = ref(99);
const toast = useToast();
const page = usePage();
const user = page.props.auth.user;
</script>

<template>
  <div>
    <div class="min-h-screen ">
      <nav class="relative border-b border-gray-100 bg-[#041C38]">
        <!-- Primary Navigation Menu -->
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex h-16 justify-between">
        
            <div class="hidden gap-4 sm:ms-6 sm:flex sm:items-center">
              <div class="flex gap-4"></div>
              <!-- Settings Dropdown -->
              <div class="relative ms-3 text-center">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center rounded-md border border-transparent text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                      >
                        <img
                          class="h-[35px] w-[35px]"
                          src="/images/icon-avatar-default.png"
                          alt="avatar"
                        />
                      </button>
                    </span>
                    <p class="text-sm text-white">{{ user.name }}</p>
                  </template>

                  <template #content>
                    <DropdownLink :href="route('profile.edit')">
                      Profile
                    </DropdownLink>
                    <DropdownLink
                      :href="route('logout')"
                      method="post"
                      as="button"
                    >
                      Log Out
                    </DropdownLink>
                  </template>
                </Dropdown>
              </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
              <button
                @click="showingNavigationDropdown = !showingNavigationDropdown"
                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
              >
                <svg
                  class="h-6 w-6"
                  stroke="currentColor"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <path
                    :class="{
                      hidden: showingNavigationDropdown,
                      'inline-flex': !showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                  <path
                    :class="{
                      hidden: !showingNavigationDropdown,
                      'inline-flex': showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div
          :class="{
            block: showingNavigationDropdown,
            hidden: !showingNavigationDropdown,
          }"
          class="sm:hidden"
        >
          <!-- Responsive Settings Options -->
          <div class="border-t border-gray-200 pb-1 pt-4">
            <div class="px-4">
              <div class="text-base font-medium text-gray-800">
                {{ $page.props.auth.user.name }}
              </div>
              <div class="text-sm font-medium text-gray-500">
                {{ $page.props.auth.user.email }}
              </div>
            </div>

            <div class="mt-3 space-y-1">
              <ResponsiveNavLink :href="route('profile.edit')">
                Profile
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('logout')"
                method="post"
                as="button"
              >
                Log Out
              </ResponsiveNavLink>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Content -->
      <main class="min-h-[calc(100vh-4rem)] bg-white">
        <slot />
      </main>
    </div>
  </div>
</template>
<style lang="scss">
.custom-bg {
  background-color: #cccccc;
}
.hide-class {
  opacity: 30%;
}
.ant-input,
.ant-input-affix-wrapper,
.ant-input-textarea,
.ant-select-selector {
  border-width: 1px !important; /* Đảm bảo ghi đè CSS của Ant Design */
  border-color: black !important;
}
.ant-input-textarea-show-count {
  border: none !important;
  padding: 0 !important;
  background: transparent !important;
  box-shadow: none !important;
}
</style>

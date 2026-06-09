<template>
    <nav class="hidden md:block bg-gray-200 shadow-md w-full z-50 text-[18px]">
        <div class="w-10/12 mx-auto px-4 sm:px-6 lg:px-0">
            <div class="flex justify-between h-16 items-center">
                <!-- MENU CHÍNH BÊN TRÁI -->
                <div class="flex items-center space-x-6">
                    <template v-for="(menu, index) in menus" :key="index">
                        <!-- Có menu con -->
                        <div v-if="menu.children && menu.permission" class="relative group" @click="() => {openDropdown = openDropdown ===index ? null : index}" >
                            <button class="flex items-center space-x-1 hover:text-blue-600 transition font-semibold">
                                <span :class="menu.active ? 'text-blue-600' : ''">{{ menu.name }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <transition name="fade">
                                <div v-if="openDropdown === index"
                                    class="absolute z-[100] w-[max-content] left-0 mt-2 w-48 bg-white shadow-lg rounded-md overflow-hidden border border-gray-100" >
                                    <template v-for="(child, cIndex) in menu.children">
                                        <Link v-if="child.permission"
                                            :key="cIndex"
                                            :href="child.link"
                                            :class="child.active ? 'text-blue-600' : ''"
                                            class="ml-4 text-black block px-4 py-2 hover:bg-gray-100 font-semibold"
                                            >{{ child.name }}
                                        </Link>
                                    </template>
                                </div>
                            </transition>
                        </div>
                        <Link v-else-if="menu.permission" :href="menu.link" :class="menu.active ? 'text-blue-600' : ''" class="font-semibold hover:text-blue-600 transition" >{{ menu.name }}</Link>
                    </template>
                </div>

                <!-- AVATAR -->
                <div class="flex items-center ml-auto">
                    <!-- Avatar -->
                    <div class="relative hidden md:block">
                        <button @click="isProfileOpen = !isProfileOpen" class="flex items-center space-x-2 focus:outline-none">
                            <img :src="user.avatar ?? '/images/icon-avatar-default.png'" alt="avatar" class="w-9 h-9 rounded-full border" />
                            <span class="">{{ user.name }}</span>
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown người dùng -->
                        <transition name="fade">
                        <div
                            v-if="isProfileOpen"
                            class="absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-md overflow-hidden border border-gray-100"
                        >
                            <a :href="route('profile.edit')" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
                            <a :href="route('logout')" class="block px-4 py-2 hover:bg-gray-100 text-red-600">Đăng xuất</a>
                        </div>
                        </transition>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <nav class="md:hidden bg-gray-200">
        <!-- Nút menu mobile -->
        <div class="p-1 w-fit">
            <button @click="isOpen = !isOpen" class=" hover:text-blue-600 focus:outline-none">
            <svg v-if="!isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M6 18L18 6M6 6l12 12" />
            </svg>
            </button>
        </div>
        <!-- MENU MOBILE -->
        <transition name="slide-fade">
            <div v-if="isOpen" class="top-10 bg-white shadow-lg border-t w-full">
                <div class="px-4 pt-4 pb-6 space-y-3">
                    <template v-for="(menu, index) in menus" :key="index">
                        <div v-if="menu.children">
                        <button @click="toggleMobileMenu(index)"
                            class="flex justify-between w-full  hover:text-blue-600">
                            <span :class="menu.active ? 'text-blue-600' : ''">{{ menu.name }}</span>
                            <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': openMobileDropdown === index }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-if="openMobileDropdown === index" class="pl-4 mt-2 space-y-2">
                            <a v-for="(child, cIndex) in menu.children" :key="cIndex" :href="child.link"
                                class="block hover:text-blue-600" :class="child.active ? 'text-blue-600' : ''">
                            {{ child.name }}
                            </a>
                        </div>
                        </div>

                        <a v-else :href="menu.link" :class="menu.active ? 'text-blue-600' : ''" class="block  hover:text-blue-600"> {{ menu.name }}</a>
                    </template>
                    <div class="border-t pt-4">
                        <div class="flex items-center space-x-3 mb-3">
                            <img :src="user.avatar ?? '/images/icon-avatar-default.png'" class="w-9 h-9 rounded-full" alt="avatar" />
                            <span class="text-gray-800">{{ user.name }}</span>
                        </div>
                        <a :href="route('profile.edit')" class="block hover:text-blue-600 py-2">Profile</a>
                        <a :href="route('logout')" class="block text-red-600 hover:text-red-700 py-2">Đăng xuất</a>
                    </div>
                </div>
            </div>
        </transition>
    </nav>
</template>

<script setup>
import { ref } from 'vue'
import {Link, usePage} from '@inertiajs/vue3';
import { USER_TYPE_ADMIN, USER_TYPE_DIRECTOR, USER_TYPE_TEACHER, USER_EDITOR } from "@/const.js";

const page = usePage();
const user = page.props.auth.user;
const userType = user.user_type.type;

const menus = [
    {
        name: 'Tool editor', link: route('apps.dashboard'),
        active: !route().current().includes('admins'),
        permission: userType === USER_TYPE_ADMIN || userType === USER_EDITOR  || userType === USER_TYPE_TEACHER  || userType === USER_TYPE_DIRECTOR
    },
    {
        name: 'Quản lý',
        link: '#',
        children: [
            {
                name: 'Đơn vị(App)',
                link: route('admins.school.index'),
                active: route().current().includes('admins.school'),
                permission: userType === USER_TYPE_ADMIN || userType === USER_TYPE_DIRECTOR
            },
            {
                name: 'Giám đốc/ Hiệu trưởng',
                link: route('admins.directors.index'),
                active: route().current().includes('admins.directors'),
                permission: userType === USER_TYPE_ADMIN
            },
            {
                name: 'Phòng ban/Lớp',
                link: route('admins.class.index'),
                active: route().current().includes('admins.class'),
                permission: userType === USER_TYPE_TEACHER
            },
            {
                name: 'Quản lý/ Giáo viên',
                link: route('admins.teachers.index'),
                active: route().current().includes('admins.teachers'),
                permission: userType === USER_TYPE_ADMIN || userType === USER_TYPE_DIRECTOR
            },
            {
                name: 'Nhân viên/ Học sinh',
                link: route('admins.students.index'),
                active: route().current().includes('admins.students'),
                permission: userType === USER_TYPE_ADMIN || userType === USER_TYPE_DIRECTOR
            }
        ]
    },
    {
        name: 'Mã kích hoạt',
        link: route('admins.active-codes.index'),
        active: route().current().includes('admins.active-codes'),
        permission: userType === USER_TYPE_ADMIN
    },
    {
        name: 'Sự kiện',
        link: route('admins.event.index'),
        active: route().current().includes('admins.event'),
        permission: userType === USER_TYPE_ADMIN || userType === USER_TYPE_TEACHER || userType === USER_TYPE_DIRECTOR
    },
    {
        name: 'Cài đặt tài khoản',
        link: route('users.index'),
        active: route().current().includes('users.index'),
        permission: userType === USER_TYPE_ADMIN
    },
    {
        name: 'PCNL/NDCD',
        link: '#',
        children: [
            {
                name: 'Phẩm chất Năng lực (PCNL)',
                link: route('admins.competencies.index'),
                active: route().current().includes('admins.competencies'),
                permission: userType === USER_TYPE_ADMIN
            },
            {
                name: 'Nội dung Giáo dục (NDGD)',
                link: route('admins.educational-contents.index'),
                active: route().current().includes('admins.educational-contents'),
                permission: userType === USER_TYPE_ADMIN
            },
        ]
    },
]

menus.forEach(menu => {
    if (menu.children) {
        // 1. Chỉ giữ lại những child mà user có quyền
        menu.children = menu.children.filter(child => child.permission);

        // 2. Nếu không còn child nào, ẩn parent luôn
        if (menu.children.length === 0) {
            menu.permission = false;
        }else{
            menu.permission = true;
        }

        // 3. Cập nhật active của parent dựa trên các child còn quyền
        menu.active = menu.children.some(child => child.active);
    }
});

const isOpen = ref(false)
const openDropdown = ref(null)
const openMobileDropdown = ref(null)
const isProfileOpen = ref(false)

function toggleMobileMenu(index) {
  openMobileDropdown.value = openMobileDropdown.value === index ? null : index
}

</script>

<style scoped>
    .slide-fade-enter-active,
    .slide-fade-leave-active {
        transition: all 0.3s ease;
    }
    .slide-fade-enter-from,
    .slide-fade-leave-to {
        opacity: 0;
        transform: translateY(-10px);
    }
    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.2s ease;
    }
    .fade-enter-from,
    .fade-leave-to {
        opacity: 0;
    }
</style>

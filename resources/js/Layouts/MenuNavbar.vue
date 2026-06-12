<template>
    <!-- ===================== DESKTOP SIDEBAR ===================== -->
    <aside class="hidden md:flex md:flex-col md:w-64 md:shrink-0 md:h-screen md:sticky md:top-0 bg-white border-r border-gray-200 z-40">
        <!-- Brand -->
        <Link :href="route('apps.dashboard')" class="flex items-center gap-3 h-16 px-5 bg-[#041C38] shrink-0">
            <img src="/images/logo-new.png" alt="logo" class="h-8 w-auto" />
            <span class="text-white font-bold text-[17px] leading-tight">Văn Hoá <span class="text-[#E0A43B]">Gốc</span></span>
        </Link>

        <!-- Nav groups -->
        <nav class="flex-1 overflow-y-auto py-3">
            <template v-for="(group, gi) in groups" :key="gi">
                <div v-if="group.permission" class="px-3 mb-1">
                    <div class="px-2 mt-3 mb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ group.label }}</div>
                    <template v-for="(item, ii) in group.items" :key="ii">
                        <Link v-if="item.permission"
                            :href="item.link"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-lg text-[14.5px] font-semibold transition',
                                item.active ? 'bg-[#e8f0fe] text-[#2b7de9] border-l-[3px] border-[#2b7de9] pl-[9px]' : 'text-gray-700 hover:bg-gray-100'
                            ]">
                            <svg class="w-[19px] h-[19px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="icons[item.icon]"></svg>
                            <span class="flex-1 min-w-0 truncate">{{ item.name }}</span>
                            <span v-if="item.badge" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#2b7de9] text-white">{{ item.badge }}</span>
                        </Link>
                    </template>
                </div>
            </template>
        </nav>

        <!-- User block -->
        <div class="relative border-t border-gray-200 p-3 shrink-0">
            <button @click="isProfileOpen = !isProfileOpen" class="flex items-center gap-3 w-full rounded-lg p-2 hover:bg-gray-100 transition">
                <img :src="user.avatar ?? '/images/icon-avatar-default.png'" alt="avatar" class="w-9 h-9 rounded-full border object-cover" />
                <span class="flex-1 min-w-0 text-left">
                    <span class="block text-[14px] font-semibold text-gray-800 truncate">{{ user.name }}</span>
                    <span class="block text-[12px] text-gray-400 truncate">{{ user.email }}</span>
                </span>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': isProfileOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
            </button>
            <transition name="fade">
                <div v-if="isProfileOpen" class="absolute bottom-[72px] left-3 right-3 bg-white shadow-lg rounded-lg overflow-hidden border border-gray-100">
                    <Link :href="route('profile.edit')" class="block px-4 py-2.5 text-[14px] hover:bg-gray-100">Hồ sơ</Link>
                    <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-4 py-2.5 text-[14px] text-red-600 hover:bg-gray-100">Đăng xuất</Link>
                </div>
            </transition>
        </div>
    </aside>

    <!-- ===================== MOBILE TOP BAR + DRAWER ===================== -->
    <nav class="md:hidden bg-[#041C38]">
        <div class="flex items-center justify-between h-14 px-3">
            <button @click="isOpen = !isOpen" class="text-white p-1 focus:outline-none">
                <svg v-if="!isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <span class="text-white font-bold text-[16px]">Văn Hoá <span class="text-[#E0A43B]">Gốc</span></span>
            <img :src="user.avatar ?? '/images/icon-avatar-default.png'" class="w-8 h-8 rounded-full border" alt="avatar" />
        </div>

        <transition name="slide-fade">
            <div v-if="isOpen" class="bg-white shadow-lg border-t w-full max-h-[80vh] overflow-y-auto">
                <div class="px-3 py-3">
                    <template v-for="(group, gi) in groups" :key="gi">
                        <div v-if="group.permission" class="mb-1">
                            <div class="px-2 mt-3 mb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ group.label }}</div>
                            <template v-for="(item, ii) in group.items" :key="ii">
                                <Link v-if="item.permission" :href="item.link"
                                    :class="[
                                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-[15px] font-semibold',
                                        item.active ? 'bg-[#e8f0fe] text-[#2b7de9]' : 'text-gray-700 hover:bg-gray-100'
                                    ]">
                                    <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="icons[item.icon]"></svg>
                                    <span class="flex-1">{{ item.name }}</span>
                                    <span v-if="item.badge" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#2b7de9] text-white">{{ item.badge }}</span>
                                </Link>
                            </template>
                        </div>
                    </template>
                    <div class="border-t mt-3 pt-3">
                        <div class="flex items-center gap-3 mb-2 px-2">
                            <img :src="user.avatar ?? '/images/icon-avatar-default.png'" class="w-9 h-9 rounded-full" alt="avatar" />
                            <span class="text-gray-800 font-semibold">{{ user.name }}</span>
                        </div>
                        <Link :href="route('profile.edit')" class="block px-3 py-2 rounded-lg hover:bg-gray-100">Hồ sơ</Link>
                        <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-3 py-2 rounded-lg text-red-600 hover:bg-gray-100">Đăng xuất</Link>
                    </div>
                </div>
            </div>
        </transition>
    </nav>
</template>

<script setup>
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3';
import { USER_TYPE_ADMIN, USER_TYPE_DIRECTOR, USER_TYPE_TEACHER, USER_EDITOR } from "@/const.js";

const page = usePage();
const user = page.props.auth.user;
const userType = user.user_type.type;

// current route name; layouts are mounted per page so this is correct on each navigation
const cur = route().current() || '';
const isActive = (matches) => matches.some(m => cur.includes(m));
const has = (...roles) => roles.includes(userType);

// inline icon paths (24x24, stroke=currentColor)
const icons = {
    app:      '<path d="M12 2 2 7l10 5 10-5-10-5Z"/><path d="m2 17 10 5 10-5"/><path d="m2 12 10 5 10-5"/>',
    message:  '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
    building: '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4M9 6h.01M15 6h.01M9 10h.01M15 10h.01M9 14h.01M15 14h.01"/>',
    userTie:  '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    users:    '<path d="M16 21v-2a4 4 0 0 0-3-3.87"/><path d="M5 21v-2a4 4 0 0 1 3-3.87"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.85"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    grid:     '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
    id:       '<rect x="2" y="4" width="20" height="16" rx="2"/><circle cx="8" cy="11" r="2"/><path d="M14 9h4M14 13h4M5 17c0-1.5 1.5-2.5 3-2.5s3 1 3 2.5"/>',
    shield:   '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>',
    send:     '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
    chart:    '<path d="M3 3v18h18"/><rect x="7" y="11" width="3" height="7"/><rect x="12" y="7" width="3" height="11"/><rect x="17" y="13" width="3" height="5"/>',
    trophy:   '<path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4Z"/><path d="M5 4H3v2a3 3 0 0 0 3 3M19 4h2v2a3 3 0 0 1-3 3"/>',
    award:    '<circle cx="12" cy="8" r="6"/><path d="M8.5 13.5 7 22l5-3 5 3-1.5-8.5"/>',
    book:     '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>',
    settings: '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/>',
    user:     '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
};

// Grouped navigation. Only functions that open standalone are surfaced directly;
// the App entry is the gateway to the drill-down (App → Sách → Tuần → Bài học).
const rawGroups = [
    { label: 'Nội dung', items: [
        { name: 'App',          icon: 'app',     link: route('apps.dashboard'),  match: ['apps','books','weeks','lessons','questions','questionEditors','templates','assignedExercises'], permission: has(USER_TYPE_ADMIN, USER_EDITOR, USER_TYPE_TEACHER, USER_TYPE_DIRECTOR) },
        { name: 'Bình luận',    icon: 'message', link: route('comments.index'),  match: ['comments'], permission: has(USER_TYPE_ADMIN, USER_EDITOR) },
    ]},
    { label: 'Trường học', items: [
        { name: 'Đơn vị(App)',          icon: 'building', link: route('admins.school.index'),     match: ['admins.school'],       permission: has(USER_TYPE_ADMIN, USER_TYPE_DIRECTOR) },
        { name: 'Giám đốc/ Hiệu trưởng', icon: 'userTie', link: route('admins.directors.index'),  match: ['admins.directors'],    permission: has(USER_TYPE_ADMIN) },
        { name: 'Quản lý/ Giáo viên',    icon: 'users',   link: route('admins.teachers.index'),   match: ['admins.teachers'],     permission: has(USER_TYPE_ADMIN, USER_TYPE_DIRECTOR) },
        { name: 'Phòng ban/Lớp',         icon: 'grid',    link: route('admins.class.index'),      match: ['admins.class'],        permission: has(USER_TYPE_TEACHER) },
        { name: 'Nhân viên/ Học sinh',   icon: 'id',      link: route('admins.students.index'),   match: ['admins.students'],     permission: has(USER_TYPE_ADMIN, USER_TYPE_DIRECTOR) },
        { name: 'Mã kích hoạt',          icon: 'shield',  link: route('admins.active-codes.index'), match: ['admins.active-codes'], permission: has(USER_TYPE_ADMIN) },
    ]},
    { label: 'Giảng dạy', items: [
        { name: 'Giao bài',  icon: 'send',   link: route('admins.teaching.assign'), match: ['admins.teaching.assign','admins.practices','admins.class.assignment'], permission: has(USER_TYPE_TEACHER) },
        { name: 'Kết quả',   icon: 'chart',  link: route('admins.teaching.result'), match: ['admins.teaching.result','admins.points','admins.class.result','admins.class.rank'], permission: has(USER_TYPE_ADMIN, USER_TYPE_DIRECTOR, USER_TYPE_TEACHER) },
        { name: 'Sự kiện',   icon: 'trophy', link: route('admins.event.index'),     match: ['admins.event'], permission: has(USER_TYPE_ADMIN, USER_TYPE_DIRECTOR, USER_TYPE_TEACHER) },
    ]},
    { label: 'Khung năng lực', items: [
        { name: 'Phẩm chất Năng lực (PCNL)', icon: 'award', link: route('admins.competencies.index'),        match: ['admins.competencies'],        permission: has(USER_TYPE_ADMIN) },
        { name: 'Nội dung Giáo dục (NDGD)',  icon: 'book',  link: route('admins.educational-contents.index'), match: ['admins.educational-contents'], permission: has(USER_TYPE_ADMIN) },
    ]},
    { label: 'Hệ thống', items: [
        { name: 'Cài đặt tài khoản', icon: 'settings', link: route('users.index'),   match: ['users.index'], permission: has(USER_TYPE_ADMIN) },
        { name: 'Hồ sơ',             icon: 'user',     link: route('profile.edit'),  match: ['profile'],     permission: true },
    ]},
];

// resolve active + drop forbidden items, hide empty groups
const groups = rawGroups.map(g => {
    const items = g.items
        .filter(it => it.permission)
        .map(it => ({ ...it, active: isActive(it.match) }));
    return { label: g.label, items, permission: items.length > 0 };
});

const isOpen = ref(false)
const isProfileOpen = ref(false)
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

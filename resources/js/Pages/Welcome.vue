<script setup>
import { ref, computed, nextTick } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
    laravelVersion: { type: String, required: false, default: '' },
    phpVersion: { type: String, required: false, default: '' },
});

const form = useForm({
    email: '',
    password: '',
    checked: false,
});

const showPwd = ref(false);

// ----- Bước chọn vai trò (theo design); chỉ cá nhân hoá giao diện,
// backend vẫn xác thực bằng email/mật khẩu (vai trò suy từ tài khoản) -----
const role = ref(null);
const emailRef = ref(null);
const roleIcons = {
    shield: '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>',
    edit: '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
    building: '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4M9 6h.01M15 6h.01M9 10h.01M15 10h.01M9 14h.01M15 14h.01"/>',
    graduate: '<path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 3 6 3s6-2 6-3v-5"/>',
};
const roles = [
    { id: 'admin',   name: 'Quản trị hệ thống', sub: 'Toàn quyền nội dung, trường học & hệ thống', icon: 'shield',   color: '#1d4ed8' },
    { id: 'editor',  name: 'Biên tập viên',     sub: 'Soạn & xuất bản học liệu, câu hỏi, bài tập', icon: 'edit',     color: '#7c3aed' },
    { id: 'sadmin',  name: 'Quản trị trường',   sub: 'Giáo viên, lớp, học sinh & mã kích hoạt',    icon: 'building', color: '#0d9488' },
    { id: 'teacher', name: 'Giáo viên',         sub: 'Giao bài, theo dõi kết quả lớp phụ trách',   icon: 'graduate', color: '#ea580c' },
];
const selectRole = (r) => { role.value = r; nextTick(() => emailRef.value?.focus()); };

const errorList = computed(() => Object.values(form.errors).filter(Boolean));

const handleSubmit = () => {
    form.post(route('login'));
};

const features = [
    { t: 'Tạo nội dung bằng AI', s: 'Bài đọc, sách nói, video, câu hỏi & game tương tác' },
    { t: 'Quản lý theo cấp', s: 'Ứng dụng → Sách → Tuần → Bài học, mạch lạc' },
    { t: 'Giao bài & chấm điểm', s: 'Theo dõi kết quả, điểm số và bảng xếp hạng' },
    { t: 'Phân quyền theo vai trò', s: 'Quản trị · Biên tập · Trường · Giáo viên' },
];
</script>

<template>
    <Head title="Đăng nhập" />

    <div class="v10-auth">
        <!-- ===== Hero trái ===== -->
        <div class="v10-intro">
            <div class="vi-top">
                <span class="vi-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="26" height="26"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </span>
                <h1 class="vi-title">Văn Hoá <span>Gốc</span></h1>
            </div>
            <p class="vi-tag">Hệ thống quản trị &amp; tạo học liệu thông minh cho nhà trường — số hoá toàn bộ quy trình từ soạn bài, giao bài đến theo dõi kết quả học tập.</p>

            <div class="vi-features">
                <div class="vi-feat">
                    <span class="vf-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M18.4 5.6l-2.1 2.1M7.7 16.3l-2.1 2.1"/><circle cx="12" cy="12" r="3"/></svg></span>
                    <span><span class="vf-t">{{ features[0].t }}</span><span class="vf-s">{{ features[0].s }}</span></span>
                </div>
                <div class="vi-feat">
                    <span class="vf-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M12 2 2 7l10 5 10-5-10-5Z"/><path d="m2 17 10 5 10-5"/><path d="m2 12 10 5 10-5"/></svg></span>
                    <span><span class="vf-t">{{ features[1].t }}</span><span class="vf-s">{{ features[1].s }}</span></span>
                </div>
                <div class="vi-feat">
                    <span class="vf-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg></span>
                    <span><span class="vf-t">{{ features[2].t }}</span><span class="vf-s">{{ features[2].s }}</span></span>
                </div>
                <div class="vi-feat">
                    <span class="vf-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg></span>
                    <span><span class="vf-t">{{ features[3].t }}</span><span class="vf-s">{{ features[3].s }}</span></span>
                </div>
            </div>

            <div class="vi-stats">
                <div class="vi-stat"><div class="vs-num">12</div><div class="vs-lbl">Trường học</div></div>
                <div class="vi-stat"><div class="vs-num">3.240</div><div class="vs-lbl">Học sinh</div></div>
                <div class="vi-stat"><div class="vs-num">212</div><div class="vs-lbl">Bài học</div></div>
            </div>
        </div>

        <!-- ===== Panel đăng nhập phải ===== -->
        <div class="v10-authpanel v5-login">
            <!-- View 2: đã chọn vai trò → form đăng nhập -->
            <div class="card" v-if="role">
                <div class="form-head">
                    <button type="button" class="back-btn" @click="role = null" title="Quay lại">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                    </button>
                    <div class="form-role">
                        <span class="fr-ic" :style="{ background: role.color }"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20" v-html="roleIcons[role.icon]"></svg></span>
                        <div><div class="fr-name">{{ role.name }}</div><div class="fr-sub">Đăng nhập với tư cách này</div></div>
                    </div>
                </div>

                <div v-if="errorList.length" class="lg-error">
                    <div v-for="(e, i) in errorList" :key="i">{{ e }}</div>
                </div>

                <form @submit.prevent="handleSubmit">
                    <div class="lg-field">
                        <label>Email / Tên đăng nhập</label>
                        <div class="inp">
                            <svg class="lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/></svg>
                            <input ref="emailRef" v-model="form.email" type="text" autocomplete="username" placeholder="Nhập email hoặc tên đăng nhập" />
                        </div>
                    </div>

                    <div class="lg-field">
                        <label>Mật khẩu</label>
                        <div class="inp">
                            <svg class="lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input v-model="form.password" :type="showPwd ? 'text' : 'password'" autocomplete="current-password" placeholder="Nhập mật khẩu" />
                            <button type="button" class="toggle-eye" @click="showPwd = !showPwd" :title="showPwd ? 'Ẩn' : 'Hiện'">
                                <svg v-if="!showPwd" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M9.9 4.2A9 9 0 0 1 12 4c6 0 10 8 10 8a18 18 0 0 1-2.2 3.2M6.6 6.6A18 18 0 0 0 2 12s4 8 10 8a9 9 0 0 0 4-1M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="row-between">
                        <label class="remember" @click="form.checked = !form.checked">
                            <span class="cbox" :class="{ on: form.checked }">
                                <svg v-if="form.checked" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" width="12" height="12"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            Lưu đăng nhập
                        </label>
                        <a href="#" class="forgot" @click.prevent>Quên mật khẩu?</a>
                    </div>

                    <button type="submit" class="submit" :style="{ '--rc': role.color }" :disabled="form.processing">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="m10 17 5-5-5-5"/><path d="M15 12H3"/></svg>
                        {{ form.processing ? 'Đang đăng nhập…' : 'Đăng nhập' }}
                    </button>
                </form>

                <div class="alt-role">Không phải bạn? <button type="button" @click="role = null">Chọn vai trò khác</button></div>
            </div>

            <!-- View 1: chọn vai trò -->
            <div class="card" v-else>
                <div class="welcome">
                    <h1>Chào mừng trở lại</h1>
                    <p>Chọn vai trò để đăng nhập vào hệ thống</p>
                </div>
                <div class="roles">
                    <button v-for="r in roles" :key="r.id" type="button" class="role" :style="{ '--rc': r.color }" @click="selectRole(r)">
                        <span class="r-ic" :style="{ background: r.color }"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22" v-html="roleIcons[r.icon]"></svg></span>
                        <span class="r-main"><span class="r-name">{{ r.name }}</span><span class="r-sub">{{ r.sub }}</span></span>
                        <span class="r-arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="m9 18 6-6-6-6"/></svg></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* tokens (port từ design, để self-contained — không ảnh hưởng trang khác) */
.v10-auth{
    --navy:#0a2342; --gold:#e0a43b; --blue:#2b7de9; --blue-600:#2563eb;
    --ink:#1f2a37; --muted:#6b7280; --muted-2:#9aa3af; --line:#e4e7ec; --line-2:#d0d5dd; --bg:#f6f8fb;
    font-family:'Be Vietnam Pro', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    display:grid; grid-template-columns:1.08fr .92fr; min-height:100vh;
}

/* hero trái */
.v10-intro{position:relative;overflow:hidden;color:#fff;padding:56px;display:flex;flex-direction:column;justify-content:center;gap:22px;
    background:linear-gradient(155deg,#0b3a53 0%,#0e4a69 55%,#10405b 100%)}
.v10-intro::before{content:"";position:absolute;inset:0;pointer-events:none;background:
    radial-gradient(620px 360px at 12% -8%, rgba(224,164,59,.18), transparent 60%),
    radial-gradient(540px 420px at 108% 112%, rgba(43,125,233,.28), transparent 58%)}
.v10-intro > *{position:relative;z-index:1}
.vi-top{display:flex;align-items:center;gap:15px}
.vi-badge{width:54px;height:54px;border-radius:15px;background:#fff;color:#0b3a53;display:grid;place-items:center;flex:none;box-shadow:0 10px 24px rgba(0,0,0,.25)}
.vi-title{font-size:36px;font-weight:800;margin:0;letter-spacing:-.6px}
.vi-title span{color:var(--gold)}
.vi-tag{font-size:16px;line-height:1.65;color:rgba(255,255,255,.82);max-width:480px;margin:0}
.vi-features{display:flex;flex-direction:column;gap:13px;margin-top:4px}
.vi-feat{display:flex;align-items:center;gap:14px}
.vi-feat .vf-ic{width:44px;height:44px;border-radius:12px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.14);display:grid;place-items:center;color:#fff;flex:none}
.vi-feat .vf-t{display:block;font-weight:700;font-size:15px;line-height:1.25}
.vi-feat .vf-s{display:block;font-size:13px;color:rgba(255,255,255,.62);margin-top:2px}
.vi-stats{display:flex;gap:30px;margin-top:12px;padding-top:24px;border-top:1px solid rgba(255,255,255,.14)}
.vi-stat .vs-num{font-size:26px;font-weight:800;letter-spacing:-.5px}
.vi-stat .vs-lbl{font-size:12.5px;color:rgba(255,255,255,.6);margin-top:2px}

/* panel phải */
.v10-authpanel{position:relative;display:flex;align-items:center;justify-content:center;padding:40px 30px;background:var(--bg);overflow:hidden}
.v10-authpanel::before{content:"";position:absolute;inset:0;pointer-events:none;background:
    radial-gradient(700px 420px at 60% -10%, #e8f0fe 0%, rgba(232,240,254,0) 60%)}
.card{position:relative;z-index:1;width:min(440px,100%);background:#fff;border:1px solid var(--line);border-radius:18px;
    box-shadow:0 24px 60px rgba(16,30,60,.14);padding:36px 36px 32px}
.welcome{text-align:center;margin-bottom:26px}
.welcome h1{margin:0;font-size:27px;font-weight:800;color:var(--navy);letter-spacing:-.4px}
.welcome p{margin:7px 0 0;color:var(--muted);font-size:14.5px}

/* role chooser */
.roles{display:flex;flex-direction:column;gap:13px}
.role{display:flex;align-items:center;gap:15px;width:100%;border:1.5px solid var(--line);border-radius:13px;
    padding:14px 16px;background:#fff;cursor:pointer;text-align:left;transition:.16s;font-family:inherit}
.role:hover{border-color:var(--rc,#2563eb);box-shadow:0 8px 22px rgba(20,40,80,.10);transform:translateY(-1px)}
.role .r-ic{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;color:#fff;flex:none;background:var(--rc)}
.role .r-main{flex:1;min-width:0;display:flex;flex-direction:column;gap:2px}
.role .r-name{font-weight:700;font-size:16px;color:var(--ink)}
.role .r-sub{font-size:12.8px;color:var(--muted)}
.role .r-arrow{color:var(--muted-2);transition:.16s;flex:none}
.role:hover .r-arrow{color:var(--rc);transform:translateX(3px)}

/* form head (đã chọn vai trò) */
.form-head{display:flex;align-items:center;gap:13px;margin-bottom:22px}
.back-btn{width:38px;height:38px;border-radius:10px;border:1px solid var(--line-2);background:#fff;color:var(--muted);display:grid;place-items:center;flex:none;cursor:pointer}
.back-btn:hover{background:#f3f5f8;color:var(--ink)}
.form-role{display:flex;align-items:center;gap:11px}
.form-role .fr-ic{width:40px;height:40px;border-radius:11px;display:grid;place-items:center;color:#fff;flex:none}
.form-role .fr-name{font-weight:800;font-size:17px;color:var(--navy);line-height:1.2}
.form-role .fr-sub{font-size:12.5px;color:var(--muted)}

.forgot{font-size:13.5px;color:var(--blue);text-decoration:none;font-weight:600}
.forgot:hover{text-decoration:underline}
.alt-role{text-align:center;margin-top:18px;font-size:13.5px;color:var(--muted)}
.alt-role button{border:none;background:transparent;color:var(--blue);font-weight:700;font-family:inherit;font-size:13.5px;cursor:pointer}
.alt-role button:hover{text-decoration:underline}

.lg-error{background:#fdeceb;border:1px solid #f6c9c5;color:#b42318;border-radius:11px;padding:11px 14px;font-size:13.5px;margin-bottom:18px}
.lg-error > div + div{margin-top:3px}

.lg-field{margin-bottom:15px}
.lg-field label{display:block;font-size:13px;font-weight:600;color:var(--ink);margin-bottom:7px}
.inp{position:relative;display:flex;align-items:center}
.inp .lead{position:absolute;left:14px;color:var(--muted-2);pointer-events:none}
.inp input{width:100%;height:50px;border:1.5px solid var(--line-2);border-radius:11px;padding:0 44px 0 44px;
    font-size:15px;font-family:inherit;outline:none;background:#fff;transition:.14s;color:var(--ink)}
.inp input:focus{border-color:var(--blue);box-shadow:0 0 0 4px rgba(37,99,235,.12)}
.inp .toggle-eye{position:absolute;right:12px;width:34px;height:34px;border:none;background:transparent;color:var(--muted);display:grid;place-items:center;border-radius:8px;cursor:pointer}
.inp .toggle-eye:hover{background:#f1f3f6;color:var(--ink)}
.row-between{display:flex;align-items:center;justify-content:space-between;margin:4px 0 22px}
.remember{display:flex;align-items:center;gap:9px;font-size:13.5px;color:var(--ink);cursor:pointer;user-select:none}
.cbox{width:18px;height:18px;border:1.6px solid var(--line-2);border-radius:5px;display:grid;place-items:center;transition:.12s;flex:none}
.cbox.on{background:var(--blue);border-color:var(--blue);color:#fff}
.submit{width:100%;height:52px;border:none;border-radius:12px;background:var(--rc,var(--blue));color:#fff;
    font-weight:700;font-size:16px;display:flex;align-items:center;justify-content:center;gap:9px;transition:.14s;cursor:pointer}
.submit:hover{filter:brightness(1.06)}
.submit:disabled{opacity:.7;cursor:default}

@media(max-width:880px){
    .v10-auth{grid-template-columns:1fr;min-height:auto}
    .v10-intro{padding:40px 30px 36px;gap:18px}
    .vi-title{font-size:30px}
    .vi-features{display:none}
}
</style>

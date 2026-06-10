<script setup>
import { ref, computed } from 'vue';
import Icon from '@/Components/AiLessonIcon.vue';
import CheckBox from './CheckBox.vue';
import { CLASSES, ROSTER, GROUP_BY_ID, GRP_ORDER } from './data.js';

const props = defineProps({
  groupBai: { type: Object, default: () => ({}) },
  totalAssigned: { type: Number, default: 0 },
  title: { type: String, default: '' },
});
const emit = defineEmits(['close', 'done']);

const totalBai = computed(() => GRP_ORDER.reduce((a, g) => a + (props.groupBai[g] || 0), 0));

const cls = ref('2a');
const picked = ref(new Set(ROSTER['2a'].map((s) => s.id)));
const date = ref('2026-06-16');
const time = ref('17:00');
const note = ref('');
const done = ref(false);

const roster = computed(() => ROSTER[cls.value] || []);
const changeClass = (id) => { cls.value = id; picked.value = new Set(ROSTER[id].map((s) => s.id)); };
const toggleStu = (id) => {
  const n = new Set(picked.value);
  n.has(id) ? n.delete(id) : n.add(id);
  picked.value = n;
};
const grpStudents = (g) => roster.value.filter((s) => s.grp === g);
const toggleGrp = (g) => {
  const ids = grpStudents(g).map((s) => s.id);
  const allOn = ids.every((i) => picked.value.has(i));
  const n = new Set(picked.value);
  ids.forEach((i) => (allOn ? n.delete(i) : n.add(i)));
  picked.value = n;
};
const pickedCount = computed(() => roster.value.filter((s) => picked.value.has(s.id)).length);
const clsName = computed(() => CLASSES.find((c) => c.id === cls.value)?.name);
const dateDisp = computed(() => date.value.split('-').reverse().join('/'));

const onOverlay = (e) => { if (e.target === e.currentTarget) emit('close'); };
</script>

<template>
  <div class="overlay" @mousedown="onOverlay">
    <!-- màn thành công -->
    <div v-if="done" class="assign-modal">
      <div class="done-hero" style="padding: 40px 24px">
        <div class="ring"><Icon name="check" :size="40" :stroke="3" /></div>
        <h3>Đã giao bài thành công!</h3>
        <p>
          Đã giao <b>{{ totalBai }} bài</b> cho <b>{{ pickedCount }} học sinh</b> {{ clsName }}.<br />
          Hạn nộp: <b>{{ time }} ngày {{ dateDisp }}</b>.
        </p>
        <button class="btn btn-primary btn-lg" style="margin-top: 18px" @click="emit('close')">
          <Icon name="check" :size="16" :stroke="3" />Hoàn tất
        </button>
      </div>
    </div>

    <!-- form giao bài -->
    <div v-else class="assign-modal">
      <div class="hist-head">
        <h3><Icon name="send" :size="18" />Giao bài cho học sinh</h3>
        <button class="wz-close" @click="emit('close')"><Icon name="x" :size="18" /></button>
      </div>
      <div class="assign-body">
        <div class="as-field">
          <label class="lbl-sm">Chọn lớp</label>
          <div class="cls-tabs">
            <button v-for="c in CLASSES" :key="c.id" class="cls-tab" :class="{ on: cls === c.id }" @click="changeClass(c.id)">
              {{ c.name }}<span class="ct-n">{{ ROSTER[c.id].length }} HS</span>
            </button>
          </div>
        </div>

        <div class="as-field">
          <div class="as-row-label">
            <label class="lbl-sm" style="margin: 0">Chọn học sinh nhận bài</label>
            <span class="muted" style="font-size: 13px">Đã chọn <b style="color: var(--ink)">{{ pickedCount }}</b>/{{ roster.length }} HS</span>
          </div>
          <div class="grp-cards">
            <template v-for="g in GRP_ORDER" :key="g">
              <div v-if="grpStudents(g).length" class="grp-card">
                <div class="gc-head" @click="toggleGrp(g)">
                  <CheckBox
                    :on="grpStudents(g).every((s) => picked.has(s.id))"
                    :indet="grpStudents(g).some((s) => picked.has(s.id)) && !grpStudents(g).every((s) => picked.has(s.id))"
                  />
                  <span class="ac-dot" :style="{ background: GROUP_BY_ID[g].color }"></span>
                  <span class="gc-name">{{ GROUP_BY_ID[g].name }}</span>
                  <span class="gc-q" :style="{ color: GROUP_BY_ID[g].color, background: GROUP_BY_ID[g].bg }">nhận {{ groupBai[g] || 0 }} bài</span>
                </div>
                <div class="gc-students">
                  <label v-for="s in grpStudents(g)" :key="s.id" class="stu" :class="{ on: picked.has(s.id) }" @click="toggleStu(s.id)">
                    <CheckBox :on="picked.has(s.id)" />{{ s.name }}
                  </label>
                </div>
              </div>
            </template>
          </div>
        </div>

        <div class="as-due">
          <div>
            <label class="lbl-sm"><Icon name="calendar" :size="14" /> Hạn nộp — ngày</label>
            <input type="date" class="ctrl" v-model="date" />
          </div>
          <div>
            <label class="lbl-sm"><Icon name="clock2" :size="14" /> Giờ</label>
            <input type="time" class="ctrl" v-model="time" />
          </div>
          <div class="as-note">
            <label class="lbl-sm">Lời nhắn cho học sinh (tuỳ chọn)</label>
            <input class="ctrl" placeholder="VD: Các em hoàn thành trước giờ học nhé!" v-model="note" />
          </div>
        </div>
      </div>

      <div class="wz-foot">
        <button class="btn" @click="emit('close')">Huỷ</button>
        <div style="display: flex; align-items: center; gap: 14px">
          <span class="foot-info">Giao <b>{{ totalBai }}</b> bài cho <b>{{ pickedCount }}</b> HS {{ clsName }}</span>
          <button class="btn btn-primary btn-lg" :disabled="pickedCount === 0" @click="done = true">
            <Icon name="send" :size="16" />Giao bài
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

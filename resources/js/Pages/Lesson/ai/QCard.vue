<script setup>
import { ref } from 'vue';
import Icon from '@/Components/AiLessonIcon.vue';
import LvlBadge from './LvlBadge.vue';
import { KIND_LABEL, REGEN_BANK } from './data.js';

const props = defineProps({
  q: { type: Object, required: true },
  n: { type: Number, default: 1 },
  deletable: { type: Boolean, default: true },
});
const emit = defineEmits(['delete']);

const cur = ref(JSON.parse(JSON.stringify(props.q)));
const mode = ref('view'); // view | edit
const regen = ref(false);
const flash = ref(false);
const draft = ref(null);
let variant = 0;

const letter = (i) => String.fromCharCode(65 + i);

const doRegen = () => {
  regen.value = true;
  setTimeout(() => {
    const bank = REGEN_BANK[cur.value.kind] || [];
    variant = (variant + 1) % (bank.length || 1);
    const v = bank[variant] || {};
    cur.value = { ...cur.value, ...JSON.parse(JSON.stringify(v)) };
    regen.value = false;
    flash.value = true;
    setTimeout(() => (flash.value = false), 1400);
  }, 950);
};
const startEdit = () => { draft.value = JSON.parse(JSON.stringify(cur.value)); mode.value = 'edit'; };
const saveEdit = () => { cur.value = draft.value; mode.value = 'view'; flash.value = true; setTimeout(() => (flash.value = false), 1200); };

const setCorrect = (i) => { draft.value.options = draft.value.options.map((x, j) => ({ ...x, correct: j === i })); };
</script>

<template>
  <div class="q-card inc" :class="{ 'just-regen': flash, regenning: regen }">
    <div class="q-head">
      <span class="q-num">{{ n }}</span>
      <span class="q-kind">{{ KIND_LABEL[cur.kind] }}</span>
      <div v-if="mode === 'view'" class="q-text">{{ cur.text }}</div>
      <textarea v-else class="q-edit-field" rows="2" v-model="draft.text"></textarea>
      <LvlBadge :level="cur.level" />
      <div v-if="mode === 'view'" class="q-acts">
        <button class="q-mini edit" @click="startEdit"><Icon name="edit" :size="13" />Sửa</button>
        <button class="q-mini regen" :disabled="regen" @click="doRegen"><Icon name="sparkles" :size="13" fill />Tạo lại</button>
        <button v-if="deletable" class="q-mini del" @click="emit('delete')"><Icon name="trash" :size="13" /></button>
      </div>
    </div>

    <!-- view -->
    <div v-if="mode === 'view' && cur.kind === 'chon'" class="q-opts">
      <div v-for="(o, i) in cur.options" :key="i" class="q-opt" :class="{ correct: o.correct }">
        <span class="oi">{{ letter(i) }}</span>{{ o.t }}
        <span v-if="o.correct" class="ans-mark"><Icon name="check" :size="13" :stroke="3" />Đáp án</span>
      </div>
    </div>
    <div v-if="mode === 'view' && cur.kind === 'sapxep'" class="q-seq">
      <div v-for="(s, i) in cur.seq" :key="i" class="si"><span class="ord">{{ i + 1 }}</span>{{ s }}</div>
    </div>
    <div v-if="mode === 'view' && cur.kind === 'noi'" class="q-match">
      <template v-for="(p, i) in cur.pairs" :key="i">
        <div class="mcell">{{ p[0] }}</div>
        <div class="mline"><Icon name="link2" :size="16" /></div>
        <div class="mcell">{{ p[1] }}</div>
      </template>
    </div>

    <!-- edit -->
    <div v-if="mode === 'edit' && draft.kind === 'chon'" class="q-opts">
      <div class="edit-hint">Sửa nội dung đáp án, bấm vào ô tròn để chọn đáp án đúng.</div>
      <div v-for="(o, i) in draft.options" :key="i" class="q-opt-edit" :class="{ correct: o.correct }">
        <button class="oi pick" :class="{ on: o.correct }" title="Đặt làm đáp án đúng" @click="setCorrect(i)">
          <Icon v-if="o.correct" name="check" :size="12" :stroke="3" /><template v-else>{{ letter(i) }}</template>
        </button>
        <input class="opt-input" v-model="o.t" />
      </div>
    </div>
    <div v-if="mode === 'edit' && draft.kind === 'sapxep'" class="q-seq">
      <div class="edit-hint">Sửa nội dung từng bước. Thứ tự hiện tại là đáp án đúng.</div>
      <div v-for="(s, i) in draft.seq" :key="i" class="si-edit"><span class="ord">{{ i + 1 }}</span>
        <input class="opt-input" v-model="draft.seq[i]" />
      </div>
    </div>
    <div v-if="mode === 'edit' && draft.kind === 'noi'" class="q-match-edit">
      <div class="edit-hint">Sửa cặp từ ở cột A và nghĩa tương ứng ở cột B.</div>
      <div v-for="(p, i) in draft.pairs" :key="i" class="pair-edit">
        <input class="opt-input" v-model="draft.pairs[i][0]" />
        <Icon name="link2" :size="15" />
        <input class="opt-input" v-model="draft.pairs[i][1]" />
      </div>
    </div>
    <div v-if="mode === 'edit'" class="q-edit-actions">
      <button class="btn btn-ghost" @click="mode = 'view'">Huỷ</button>
      <button class="btn btn-primary" @click="saveEdit"><Icon name="check" :size="14" :stroke="3" />Lưu câu hỏi</button>
    </div>

    <div v-if="regen" class="q-regen-overlay"><span class="spin"></span>Đang tạo lại câu hỏi…</div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import Icon from '@/Components/AiLessonIcon.vue';
import LvlBadge from './LvlBadge.vue';
import { buildBaiQuestions, mixStr, KIND_LABEL, LEVEL_STYLE, SUG_NHOM } from './data.js';

const props = defineProps({
  bais: { type: Array, default: () => [] },
  color: { type: String, default: '#16a34a' },
  counts: { type: Object, default: () => ({}) },
  totalBai: { type: Number, default: 0 },
  totalCau: { type: Number, default: 0 },
  // 'giao'  -> bài tập giao học sinh (bản đồ nhóm + giao bài)
  // 'chung' -> bài luyện tập chung (1 bộ dùng chung, không giao)
  mode: { type: String, default: 'giao' },
});
const emit = defineEmits(['assign', 'save', 'save-single', 'question-update', 'question-delete']);

const open = ref(0);
const editingKey = ref(null); // "bai-0-q-0"
const editingData = ref(null);

const toggle = (i) => { open.value = open.value === i ? -1 : i; };
const letter = (i) => String.fromCharCode(65 + i);

const giaoMap = computed(() => [
  { grp: 'Nhóm yếu', level: 'Dễ', baiName: 'Bài dễ', n: props.counts['Dễ'] || 0 },
  { grp: 'Nhóm khá', level: 'Trung bình', baiName: 'Bài trung bình', n: props.counts['Trung bình'] || 0 },
  { grp: 'Nhóm giỏi', level: 'Khó', baiName: 'Bài khó', n: props.counts['Khó'] || 0 },
]);
const lv = (level) => LEVEL_STYLE[level] || LEVEL_STYLE['Dễ'];

const startEdit = (baiIdx, qIdx, q) => {
  editingKey.value = `bai-${baiIdx}-q-${qIdx}`;
  editingData.value = JSON.parse(JSON.stringify(q));
};
const cancelEdit = () => { editingKey.value = null; editingData.value = null; };
const saveEdit = (baiIdx, qIdx) => { emit('question-update', { baiIdx, qIdx, data: editingData.value }); cancelEdit(); };
const deleteQuestion = (baiIdx, qIdx) => { emit('question-delete', { baiIdx, qIdx }); };
const saveSingleExercise = (baiIdx) => { emit('save-single', { baiIdx, exerciseItem: props.bais[baiIdx], mode: props.mode }); };
</script>

<template>
  <div>
    <!-- chỉ ở chế độ giao: mô tả + bản đồ nhóm -->
    <template v-if="mode === 'giao'">
      <p class="sec-sub">Các bài tập tạo theo mức độ, giao cho từng nhóm học sinh phù hợp. Mỗi bài 10 câu, có thể sửa / xoá trước khi giao.</p>
      <div class="map-note">
        <Icon name="info" :size="15" />
        <span>Tự động giao theo mức độ của bài:</span>
        <span class="map-pair"><LvlBadge level="Dễ" /><Icon name="chevRight" :size="13" /><b>Nhóm yếu</b></span>
        <span class="map-pair"><LvlBadge level="Trung bình" /><Icon name="chevRight" :size="13" /><b>Nhóm khá</b></span>
        <span class="map-pair"><LvlBadge level="Khó" /><Icon name="chevRight" :size="13" /><b>Nhóm giỏi</b></span>
      </div>
      <div class="giao-grid">
        <div v-for="g in giaoMap" :key="g.grp" class="giao-card" :style="{ borderColor: lv(g.level).bg }">
          <div class="gc-grp"><span class="gc-grp-ic" :style="{ background: lv(g.level).bg, color: lv(g.level).color }"><Icon name="users" :size="16" /></span>{{ g.grp }}</div>
          <div class="gc-arrow"><Icon name="chevDown" :size="16" /></div>
          <div class="gc-bai" :style="{ background: lv(g.level).bg, color: lv(g.level).color }"><LvlBadge :level="g.level" />{{ g.baiName }}</div>
          <div class="gc-stat"><b>{{ g.n }}</b> bài · <b>{{ g.n * 10 }}</b> câu</div>
        </div>
      </div>
    </template>

    <template v-if="mode === 'chung'">
      <p class="sec-sub">Một bộ <b>10 câu hỏi</b> (3 Dễ · 4 TB · 3 Khó) dùng chung cho <b>cả lớp</b>. Bạn có thể sửa hoặc xoá từng câu.</p>
    </template>

    <div class="bai-list">
      <div v-for="(b, i) in bais" :key="i" class="bai-item" :class="{ open: open === i }">
        <button class="bai-head" @click="toggle(i)">
          <span class="bai-idx" :style="{ background: color }">{{ i + 1 }}</span>
          <div class="bai-head-main">
            <span class="bai-name">{{ mode === 'chung' ? 'Bài luyện tập chung' : 'Bài ' + (i + 1) }}</span>
            <span class="bai-sub">{{ (b.realQuestions?.length || 10) }} câu · {{ mixStr(b.mix) }}<template v-if="mode === 'giao'"> · giao {{ SUG_NHOM[b.level] }}</template></span>
          </div>
          <LvlBadge v-if="mode === 'giao'" :level="b.level" />
          <span class="acc-caret"><Icon name="chevDown" :size="17" /></span>
        </button>
        <div v-if="open === i" class="bai-body">
          <div class="bai-mixline">
            <span class="muted">{{ b.realQuestions?.length || 10 }} câu hỏi:</span>
            <span class="mix-chip" :style="{ background: '#e8f7ee', color: '#15803d' }">{{ b.mix['Dễ'] }} Dễ</span>
            <span class="mix-chip" :style="{ background: '#fdf3e0', color: '#b45309' }">{{ b.mix['Trung bình'] }} TB</span>
            <span class="mix-chip" :style="{ background: '#fde8e6', color: '#c2410c' }">{{ b.mix['Khó'] }} Khó</span>
          </div>
          <div class="q-list" style="margin-top: 12px">
            <!-- Câu hỏi thật từ API -->
            <template v-if="b.realQuestions && b.realQuestions.length > 0">
              <div v-for="(q, n) in b.realQuestions" :key="`real-${n}`" class="q-card inc">
                <div class="q-head">
                  <span class="q-num">{{ n + 1 }}</span>
                  <span class="q-kind">{{ q.kind === 'chon' ? 'Chọn' : q.kind === 'sx' ? 'Sắp xếp' : 'Nối' }}</span>
                  <div class="q-text">{{ q.tieu_de || 'Câu hỏi' }}</div>
                  <LvlBadge :level="q.muc_do" />
                  <div class="q-acts">
                    <button class="q-mini edit" @click="startEdit(i, n, q)"><Icon name="edit" :size="13" />Sửa</button>
                    <button class="q-mini del" @click="deleteQuestion(i, n)"><Icon name="trash" :size="13" /></button>
                  </div>
                </div>
                <!-- form sửa inline -->
                <div v-if="editingKey === `bai-${i}-q-${n}`" class="q-edit-form">
                  <div class="form-group">
                    <label>Câu hỏi</label>
                    <textarea v-model="editingData.tieu_de" class="form-input" placeholder="Nhập tiêu đề câu hỏi" rows="2"></textarea>
                  </div>
                  <div v-if="editingData.kind === 'chon'" class="form-group">
                    <label>Các lựa chọn</label>
                    <div v-for="(opt, idx) in editingData.options" :key="idx" class="opt-edit">
                      <span class="opt-label">{{ letter(idx) }}.</span>
                      <textarea v-model="editingData.options[idx]" class="form-input" placeholder="Nhập lựa chọn" rows="1"></textarea>
                    </div>
                    <div class="form-group">
                      <label>Đáp án đúng (A/B/C/D/E)</label>
                      <input v-model="editingData.dap_an_dung" class="form-input" placeholder="A" maxlength="1" style="max-width: 80px" />
                    </div>
                  </div>
                  <div v-else-if="editingData.kind === 'sx'" class="form-group">
                    <label>Các mục sắp xếp</label>
                    <div v-for="(item, idx) in editingData.options" :key="idx" class="opt-edit">
                      <span class="opt-label">{{ idx + 1 }}.</span>
                      <textarea v-model="editingData.options[idx]" class="form-input" placeholder="Nhập mục sắp xếp" rows="1"></textarea>
                    </div>
                  </div>
                  <div v-else-if="editingData.kind === 'noi'" class="form-group">
                    <div class="match-cols">
                      <div>
                        <label>Cột A</label>
                        <div v-for="(item, idx) in editingData.cot_a" :key="`a-${idx}`" class="opt-edit">
                          <textarea v-model="editingData.cot_a[idx]" class="form-input" placeholder="Nhập mục A" rows="1"></textarea>
                        </div>
                      </div>
                      <div>
                        <label>Cột B</label>
                        <div v-for="(item, idx) in editingData.cot_b" :key="`b-${idx}`" class="opt-edit">
                          <textarea v-model="editingData.cot_b[idx]" class="form-input" placeholder="Nhập mục B" rows="1"></textarea>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-actions">
                    <button class="btn btn-primary btn-sm" @click="saveEdit(i, n)"><Icon name="check" :size="14" :stroke="3" />Lưu</button>
                    <button class="btn btn-sm" @click="cancelEdit"><Icon name="x" :size="14" />Huỷ</button>
                  </div>
                </div>
                <!-- hiển thị nội dung -->
                <template v-else>
                  <div v-if="q.kind === 'chon' && q.options" class="q-opts">
                    <div v-for="(o, oi) in q.options" :key="oi" class="q-opt" :class="{ correct: letter(oi) === q.dap_an_dung }">
                      <span class="oi">{{ letter(oi) }}</span>{{ o }}
                      <span v-if="letter(oi) === q.dap_an_dung" class="ans-mark"><Icon name="check" :size="13" :stroke="3" />Đáp án</span>
                    </div>
                  </div>
                  <div v-else-if="q.kind === 'sx' && q.options" class="q-seq">
                    <div v-for="(s, si) in q.options" :key="si" class="si"><span class="ord">{{ si + 1 }}</span>{{ s }}</div>
                  </div>
                  <div v-else-if="q.kind === 'noi' && q.cot_a && q.cot_b" class="q-match">
                    <template v-for="(item, pi) in q.cot_a" :key="pi">
                      <div class="mcell">{{ item }}</div>
                      <div class="mline"><Icon name="link2" :size="16" /></div>
                      <div class="mcell">{{ q.cot_b[pi] }}</div>
                    </template>
                  </div>
                </template>
              </div>
            </template>
            <!-- fallback câu hỏi mẫu khi chưa có dữ liệu thật -->
            <template v-else>
              <div v-for="(q, n) in buildBaiQuestions(b.mix)" :key="q.uid" class="q-card inc">
                <div class="q-head">
                  <span class="q-num">{{ n + 1 }}</span>
                  <span class="q-kind">{{ KIND_LABEL[q.kind] }}</span>
                  <div class="q-text">{{ q.text }}</div>
                  <LvlBadge :level="q.level" />
                </div>
                <div v-if="q.kind === 'chon'" class="q-opts">
                  <div v-for="(o, oi) in q.options" :key="oi" class="q-opt" :class="{ correct: o.correct }">
                    <span class="oi">{{ letter(oi) }}</span>{{ o.t }}
                    <span v-if="o.correct" class="ans-mark"><Icon name="check" :size="13" :stroke="3" />Đáp án</span>
                  </div>
                </div>
                <div v-else-if="q.kind === 'sapxep'" class="q-seq">
                  <div v-for="(s, si) in q.seq" :key="si" class="si"><span class="ord">{{ si + 1 }}</span>{{ s }}</div>
                </div>
                <div v-else-if="q.kind === 'noi'" class="q-match">
                  <template v-for="(p, pi) in q.pairs" :key="pi">
                    <div class="mcell">{{ p[0] }}</div>
                    <div class="mline"><Icon name="link2" :size="16" /></div>
                    <div class="mcell">{{ p[1] }}</div>
                  </template>
                </div>
              </div>
            </template>
          </div>
          <!-- Footer for individual exercise save -->
          <div class="bai-footer" style="display: flex; gap: 8px; margin-top: 16px; padding-top: 12px; border-top: 1px solid #e5e7eb;">
            <button class="btn btn-sm" @click="() => { console.log('click save single', i); saveSingleExercise(i); }" style="flex: 1; background: #3b82f6; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">
              <Icon name="save" :size="14" style="margin-right: 4px;" />Lưu bài {{ i + 1 }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="mode === 'giao'" class="acc-actions">
      <span class="foot-info"><b>{{ totalBai }}</b> bài giao cho <b>3</b> nhóm học sinh</span>
      <div style="display: flex; gap: 10px">
        <button class="btn" @click="() => { console.log('click save giao'); emit('save', { mode: 'giao' }); }"><Icon name="save" :size="15" />Lưu bài</button>
        <button class="btn btn-assign" @click="() => { console.log('click assign'); emit('assign'); }"><Icon name="send" :size="15" />Giao bài cho học sinh</button>
      </div>
    </div>

    <div v-if="mode === 'chung'" class="acc-actions">
      <span class="foot-info"><b>{{ totalBai }}</b> bài luyện tập chung</span>
      <div style="display: flex; gap: 10px">
        <button class="btn" @click="() => { console.log('click save chung'); emit('save', { mode: 'chung' }); }"><Icon name="save" :size="15" />Lưu bài</button>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.q-edit-form {
  padding: 12px;
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
  border-radius: 0 0 6px 6px;

  .form-group { margin-bottom: 12px; }
  .form-group label { display: block; font-size: 13px; font-weight: 500; margin-bottom: 6px; color: #374151; }
  .form-input {
    width: 100%; padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 6px;
    font-size: 14px; font-family: inherit; resize: vertical; transition: all 0.2s;
  }
  .form-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
  .opt-edit { display: flex; gap: 8px; margin-bottom: 8px; align-items: flex-start; }
  .opt-edit .opt-label { font-weight: 500; padding-top: 8px; min-width: 24px; color: #6b7280; }
  .opt-edit .form-input { flex: 1; }
  .match-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .match-cols > div label { display: block; font-size: 13px; font-weight: 500; margin-bottom: 6px; color: #374151; }
  .form-actions { display: flex; gap: 8px; margin-top: 12px; }
  .form-actions .btn { display: flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 13px; border-radius: 6px; cursor: pointer; }
  .form-actions .btn.btn-sm { background: white; border: 1px solid #e5e7eb; color: #374151; }
  .form-actions .btn.btn-sm:hover { background: #f3f4f6; border-color: #d1d5db; }
  .form-actions .btn.btn-primary { background: #3b82f6; color: white; border: none; }
  .form-actions .btn.btn-primary:hover { background: #2563eb; }
}
</style>

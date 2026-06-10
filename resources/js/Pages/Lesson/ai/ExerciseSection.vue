<script setup>
import { ref } from 'vue';
import Icon from '@/Components/AiLessonIcon.vue';
import LvlBadge from './LvlBadge.vue';
import { buildBaiQuestions, mixStr, KIND_LABEL } from './data.js';

const props = defineProps({
  bais: { type: Array, default: () => [] },
  questions: { type: Array, default: () => [] },
  color: { type: String, default: '#16a34a' },
  totalBai: { type: Number, default: 0 },
  totalCau: { type: Number, default: 0 },
});
const emit = defineEmits(['assign', 'question-update', 'question-delete']);

const open = ref(0);
const editingKey = ref(null); // "bai-0-q-0" format
const editingData = ref(null);

const toggle = (i) => { open.value = open.value === i ? -1 : i; };
const letter = (i) => String.fromCharCode(65 + i);

const startEdit = (baiIdx, qIdx, q) => {
  editingKey.value = `bai-${baiIdx}-q-${qIdx}`;
  editingData.value = JSON.parse(JSON.stringify(q));
};

const cancelEdit = () => {
  editingKey.value = null;
  editingData.value = null;
};

const saveEdit = (baiIdx, qIdx) => {
  emit('question-update', { baiIdx, qIdx, data: editingData.value });
  cancelEdit();
};

const deleteQuestion = (baiIdx, qIdx) => {
  emit('question-delete', { baiIdx, qIdx });
};
</script>

<template>
  <div>
    <div class="map-note">
      <Icon name="info" :size="15" />
      <span>Giao tự động theo mức độ của bài:</span>
      <span class="map-pair"><LvlBadge level="Dễ" /><Icon name="chevRight" :size="13" /><b>Nhóm yếu</b></span>
      <span class="map-pair"><LvlBadge level="Trung bình" /><Icon name="chevRight" :size="13" /><b>Nhóm khá</b></span>
      <span class="map-pair"><LvlBadge level="Khó" /><Icon name="chevRight" :size="13" /><b>Nhóm giỏi</b></span>
    </div>

    <div class="bai-list">
      <div v-for="(b, i) in bais" :key="i" class="bai-item" :class="{ open: open === i }">
        <button class="bai-head" @click="toggle(i)">
          <span class="bai-idx" :style="{ background: color }">{{ i + 1 }}</span>
          <div class="bai-head-main">
            <span class="bai-name">Bài tập {{ i + 1 }}</span>
            <span class="bai-sub">10 câu · {{ mixStr(b.mix) }}</span>
          </div>
          <LvlBadge :level="b.level" />
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
            <!-- Real questions from API -->
            <template v-if="b.realQuestions && b.realQuestions.length > 0">
              <div v-for="(q, n) in b.realQuestions" :key="`real-${n}`" class="q-card inc">
                <div class="q-head">
                  <span class="q-num">{{ n + 1 }}</span>
                  <span class="q-kind">{{ q.kind === 'chon' ? 'Chọn' : q.kind === 'sx' ? 'Sắp xếp' : 'Nối' }}</span>
                  <div class="q-text">{{ q.tieu_de || 'Câu hỏi' }}</div>
                  <LvlBadge :level="q.muc_do" />
                  <div class="q-actions">
                    <button class="btn-icon" @click="startEdit(props.bais.indexOf(b), n, q)" title="Sửa"><Icon name="edit" :size="16" /></button>
                    <button class="btn-icon btn-delete" @click="deleteQuestion(props.bais.indexOf(b), n)" title="Xóa"><Icon name="trash" :size="16" /></button>
                  </div>
                </div>
                <!-- Edit form inline -->
                <div v-if="editingKey === `bai-${props.bais.indexOf(b)}-q-${n}`" class="q-edit-form">
                  <div class="form-group">
                    <label>Câu hỏi</label>
                    <textarea v-model="editingData.tieu_de" class="form-input" placeholder="Nhập tiêu đề câu hỏi" rows="2"></textarea>
                  </div>

                  <!-- Multiple Choice Options -->
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

                  <!-- Sequencing Options -->
                  <div v-else-if="editingData.kind === 'sx'" class="form-group">
                    <label>Các mục sắp xếp</label>
                    <div v-for="(item, idx) in editingData.options" :key="idx" class="opt-edit">
                      <span class="opt-label">{{ idx + 1 }}.</span>
                      <textarea v-model="editingData.options[idx]" class="form-input" placeholder="Nhập mục sắp xếp" rows="1"></textarea>
                    </div>
                  </div>

                  <!-- Matching Options -->
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
                    <button class="btn btn-primary btn-sm" @click="saveEdit(props.bais.indexOf(b), n)"><Icon name="check" :size="14" :stroke="3" />Lưu</button>
                    <button class="btn btn-sm" @click="cancelEdit"><Icon name="x" :size="14" />Huỷ</button>
                  </div>
                </div>

                <!-- Display content -->
                <template v-else>
                  <!-- Multiple Choice -->
                  <div v-if="q.kind === 'chon' && q.options" class="q-opts">
                    <div v-for="(o, oi) in q.options" :key="oi" class="q-opt" :class="{ correct: letter(oi) === q.dap_an_dung }">
                      <span class="oi">{{ letter(oi) }}</span>{{ o }}
                      <span v-if="letter(oi) === q.dap_an_dung" class="ans-mark"><Icon name="check" :size="13" :stroke="3" />Đáp án</span>
                    </div>
                  </div>
                  <!-- Sequencing -->
                  <div v-else-if="q.kind === 'sx' && q.options" class="q-seq">
                    <div v-for="(s, si) in q.options" :key="si" class="si"><span class="ord">{{ si + 1 }}</span>{{ s }}</div>
                  </div>
                  <!-- Matching -->
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
            <!-- Mock questions fallback -->
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
        </div>
      </div>
    </div>

    <div class="acc-actions">
      <span class="foot-info"><b>{{ totalBai }}</b> bài tập · <b>{{ totalCau }}</b> câu hỏi</span>
      <button class="btn btn-assign" @click="emit('assign')"><Icon name="send" :size="15" />Giao bài cho học sinh</button>
    </div>
  </div>
</template>

<style scoped lang="scss">
.q-actions {
  display: flex;
  gap: 6px;
  margin-left: auto;

  .btn-icon {
    width: 32px;
    height: 32px;
    border: 1px solid #e5e7eb;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    color: #6b7280;

    &:hover {
      background: #f3f4f6;
      border-color: #d1d5db;
      color: #374151;
    }

    &.btn-delete {
      &:hover {
        background: #fef2f2;
        border-color: #fca5a5;
        color: #dc2626;
      }
    }
  }
}

.q-edit-form {
  padding: 12px;
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
  border-radius: 0 0 6px 6px;

  .form-group {
    margin-bottom: 12px;

    label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 6px;
      color: #374151;
    }
  }

  .form-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
    resize: vertical;
    transition: all 0.2s;

    &:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
  }

  .opt-edit {
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
    align-items: flex-start;

    .opt-label {
      font-weight: 500;
      padding-top: 8px;
      min-width: 24px;
      color: #6b7280;
    }

    .form-input {
      flex: 1;
    }
  }

  .match-cols {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;

    > div {
      label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 6px;
        color: #374151;
      }
    }
  }

  .form-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;

    .btn {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 6px 12px;
      font-size: 13px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.2s;

      &.btn-sm {
        background: white;
        border: 1px solid #e5e7eb;
        color: #374151;

        &:hover {
          background: #f3f4f6;
          border-color: #d1d5db;
        }
      }

      &.btn-primary {
        background: #3b82f6;
        color: white;

        &:hover {
          background: #2563eb;
        }
      }
    }
  }
}
</style>

<script setup>
import { ref, computed } from 'vue';
import Icon from '@/Components/AiLessonIcon.vue';

const props = defineProps({
  questions: { type: Array, default: () => [] },
  selectedQuestions: { type: Set, default: () => new Set() },
});

const emit = defineEmits(['update:selectedQuestions', 'delete', 'edit']);

const expandedId = ref(null);

const toggleSelect = (qId) => {
  const newSelected = new Set(props.selectedQuestions);
  if (newSelected.has(qId)) {
    newSelected.delete(qId);
  } else {
    newSelected.add(qId);
  }
  emit('update:selectedQuestions', newSelected);
};

const selectAll = () => {
  if (props.selectedQuestions.size === props.questions.length) {
    emit('update:selectedQuestions', new Set());
  } else {
    const allIds = new Set(props.questions.map((_, i) => i));
    emit('update:selectedQuestions', allIds);
  }
};

const isSelected = (idx) => props.selectedQuestions.has(idx);
const allSelected = computed(() => props.selectedQuestions.size === props.questions.length);

const getOptionText = (opt) => {
  if (typeof opt === 'string') return opt;
  if (typeof opt === 'object') {
    return opt.noi_dung || opt.value || opt.text || opt.answer_val || '';
  }
  return '';
};

const letter = (i) => String.fromCharCode(65 + i);
</script>

<template>
  <div class="exercise-list">
    <!-- Select All -->
    <div class="select-all-bar">
      <input type="checkbox" :checked="allSelected" @change="selectAll" />
      <span>Chọn tất cả ({{ selectedQuestions.size }}/{{ questions.length }})</span>
    </div>

    <!-- Questions -->
    <div class="q-list">
      <div v-for="(q, idx) in questions" :key="idx" class="q-card" :class="{ selected: isSelected(idx) }">
        <div class="q-head">
          <input type="checkbox" :checked="isSelected(idx)" @change="toggleSelect(idx)" />
          <span class="q-num">{{ idx + 1 }}</span>
          <span class="q-kind">{{ q.kind }}</span>
          <div class="q-text">{{ q.tieu_de || q.title || 'Câu hỏi' }}</div>
          <div class="q-actions">
            <button @click="emit('edit', idx)" class="btn-icon">✏️</button>
            <button @click="emit('delete', idx)" class="btn-icon">🗑️</button>
            <button @click="expandedId = expandedId === idx ? null : idx" class="btn-icon">
              {{ expandedId === idx ? '▼' : '▶' }}
            </button>
          </div>
        </div>

        <!-- Expanded Details -->
        <div v-if="expandedId === idx" class="q-detail">
          <!-- Multiple Choice -->
          <div v-if="q.kind === 'chon' && q.options" class="q-opts">
            <div v-for="(o, oi) in q.options" :key="oi" class="q-opt" :class="{ correct: o === q.dap_an }">
              <span class="oi">{{ letter(oi) }}</span>
              <span>{{ getOptionText(o) }}</span>
              <span v-if="o === q.dap_an" class="ans-mark">✓ Đáp án</span>
            </div>
          </div>

          <!-- Matching -->
          <div v-else-if="q.kind === 'noi' && q.cot_a && q.cot_b" class="q-match">
            <div class="match-cols">
              <div class="col-a">
                <div v-for="(item, i) in q.cot_a" :key="i" class="match-item">{{ item }}</div>
              </div>
              <div class="col-b">
                <div v-for="(item, i) in q.cot_b" :key="i" class="match-item">{{ item }}</div>
              </div>
            </div>
          </div>

          <!-- Sequencing -->
          <div v-else-if="q.kind === 'sx' && q.options" class="q-seq">
            <div v-for="(opt, oi) in q.options" :key="oi" class="seq-item">
              <span class="ord">{{ oi + 1 }}</span>
              <span>{{ getOptionText(opt) }}</span>
            </div>
          </div>

          <!-- Explanation -->
          <div v-if="q.trich_dan_dap_an" class="q-explain">
            <strong>Giải thích:</strong>
            <div v-html="q.trich_dan_dap_an"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.exercise-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.select-all-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px;
  background: #f0f9ff;
  border-radius: 8px;
  font-weight: 500;

  input[type='checkbox'] {
    width: 18px;
    height: 18px;
    cursor: pointer;
  }
}

.q-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.q-card {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.2s;

  &.selected {
    border-color: #3b82f6;
    background: #eff6ff;
  }

  &:hover {
    border-color: #d1d5db;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }
}

.q-head {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #fafafa;
  border-bottom: 1px solid #e5e7eb;

  input[type='checkbox'] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    flex-shrink: 0;
  }

  .q-num {
    font-weight: bold;
    color: #3b82f6;
    min-width: 24px;
  }

  .q-kind {
    padding: 2px 8px;
    background: #dbeafe;
    color: #1e40af;
    font-size: 12px;
    border-radius: 4px;
    font-weight: 500;
  }

  .q-text {
    flex: 1;
    font-weight: 500;
  }

  .q-actions {
    display: flex;
    gap: 6px;

    .btn-icon {
      width: 32px;
      height: 32px;
      border: 1px solid #e5e7eb;
      background: white;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      transition: all 0.2s;

      &:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
      }
    }
  }
}

.q-detail {
  padding: 16px;
  background: white;
  border-top: 1px solid #e5e7eb;
}

.q-opts {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.q-opt {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: #f9fafb;

  &.correct {
    background: #ecfdf5;
    border-color: #a7f3d0;
  }

  .oi {
    font-weight: bold;
    width: 24px;
    text-align: center;
  }

  .ans-mark {
    margin-left: auto;
    color: #10b981;
    font-weight: 500;
    font-size: 12px;
  }
}

.q-match {
  display: flex;
  gap: 24px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 6px;

  .match-cols {
    display: flex;
    gap: 24px;
    width: 100%;
  }

  .col-a,
  .col-b {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .match-item {
    padding: 8px 12px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    font-size: 14px;
  }
}

.q-seq {
  display: flex;
  flex-direction: column;
  gap: 8px;

  .seq-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 4px;

    .ord {
      font-weight: bold;
      color: #3b82f6;
      min-width: 24px;
    }
  }
}

.q-explain {
  margin-top: 12px;
  padding: 12px;
  background: #fef3c7;
  border: 1px solid #fcd34d;
  border-radius: 6px;
  font-size: 14px;

  strong {
    display: block;
    margin-bottom: 8px;
  }
}
</style>

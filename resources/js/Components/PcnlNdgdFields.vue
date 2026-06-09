<script setup>
import { ref, watch, onMounted, computed } from 'vue';

const props = defineProps({
    competencyId: { type: [Number, String], default: null },
    competencyComponentId: { type: [Number, String], default: null },
    pcnlDetail: { type: String, default: '' },
    educationalContentId: { type: [Number, String], default: null },
    ndgdRequirement: { type: String, default: '' },
});

const emit = defineEmits([
    'update:competencyId',
    'update:competencyComponentId',
    'update:pcnlDetail',
    'update:educationalContentId',
    'update:ndgdRequirement',
]);

const competencies = ref([]);
const components = ref([]);
const educationalContents = ref([]);
const isInitialLoad = ref(true);

// Computed v-model proxies for a-select controlled mode
const selectedCompetencyId = computed({
    get: () => props.competencyId ?? undefined,
    set: (val) => emit('update:competencyId', val ?? null),
});

const selectedComponentId = computed({
    get: () => props.competencyComponentId ?? undefined,
    set: (val) => emit('update:competencyComponentId', val ?? null),
});

const selectedEducationalContentId = computed({
    get: () => props.educationalContentId ?? undefined,
    set: (val) => emit('update:educationalContentId', val ?? null),
});

onMounted(async () => {
    const [resC, resE] = await Promise.all([
        axios.get(route('admins.competencies.json.list', { per_page: 200 })),
        axios.get(route('admins.educational-contents.json.list', { per_page: 200 })),
    ]);
    if (resC.data.status) {
        competencies.value = resC.data.data.data.map(v => ({ value: v.id, label: v.name }));
    }
    if (resE.data.status) {
        educationalContents.value = resE.data.data.data.map(v => ({ value: v.id, label: v.name }));
    }
    // Load components for initial saved value without resetting it
    if (props.competencyId) {
        await loadComponents(props.competencyId);
    }
    isInitialLoad.value = false;
});

async function loadComponents(competencyId) {
    if (!competencyId) { components.value = []; return; }
    const res = await axios.get(route('admins.competency-components.json.list', {
        competency_id: competencyId,
        per_page: 200,
    }));
    if (res.data.status) {
        components.value = res.data.data.data.map(v => ({ value: v.id, label: v.name }));
    }
}

// Only reset component when user actively changes competency (not on initial mount)
watch(() => props.competencyId, (val, oldVal) => {
    if (isInitialLoad.value) return;
    emit('update:competencyComponentId', null);
    loadComponents(val);
});
</script>

<template>
    <div class="mt-4 border-t pt-4">
        <!-- PCNL -->
        <div class="mb-4">
            <p class="font-bold mb-2">PCNL</p>
            <div class="flex gap-3 mb-2">
                <a-select
                    class="w-1/2"
                    placeholder="Phẩm chất năng lực"
                    v-model:value="selectedCompetencyId"
                    :options="competencies"
                    allow-clear
                    size="large"
                    show-search
                    :filter-option="(input, opt) => opt.label.toLowerCase().includes(input.toLowerCase())"
                />
                <a-select
                    class="w-1/2"
                    placeholder="Thành phần năng lực"
                    v-model:value="selectedComponentId"
                    :options="components"
                    :disabled="!competencyId"
                    allow-clear
                    size="large"
                    show-search
                    :filter-option="(input, opt) => opt.label.toLowerCase().includes(input.toLowerCase())"
                />
            </div>
            <a-input
                placeholder="Biểu hiện cụ thể của thành phần năng lực"
                :value="pcnlDetail"
                size="large"
                @input="e => emit('update:pcnlDetail', e.target.value)"
            />
        </div>

        <!-- NDGD -->
        <div>
            <p class="font-bold mb-2">NDGD</p>
            <a-select
                class="w-full mb-2"
                placeholder="Nội dung giáo dục"
                v-model:value="selectedEducationalContentId"
                :options="educationalContents"
                allow-clear
                size="large"
                show-search
                :filter-option="(input, opt) => opt.label.toLowerCase().includes(input.toLowerCase())"
            />
            <a-input
                placeholder="Yêu cầu cần đạt của NDGD"
                :value="ndgdRequirement"
                size="large"
                @input="e => emit('update:ndgdRequirement', e.target.value)"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: Object, required: true },   // активные фильтры
    references: { type: Object, default: null },     // справочники
});
const emit = defineEmits(['update:modelValue', 'reset']);

function update(key, value) {
    emit('update:modelValue', { ...props.modelValue, [key]: value || null });
}

const hasActive = computed(() =>
    Object.entries(props.modelValue).some(([k, v]) => k !== 'page' && v)
);
</script>

<template>
    <div class="bg-white border rounded-xl p-3" style="border-color: var(--color-line)">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-2.5 items-end">
            <div class="xl:col-span-2">
                <label class="stat-label block mb-1">Поиск</label>
                <input class="field" type="text" placeholder="назначение, клиент, проект…"
                       :value="modelValue.search" @input="update('search', $event.target.value)" />
            </div>

            <div>
                <label class="stat-label block mb-1">Проект</label>
                <select class="field" :value="modelValue.project_id" @change="update('project_id', $event.target.value)">
                    <option value="">Все</option>
                    <option v-for="p in references?.projects || []" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
            </div>

            <div>
                <label class="stat-label block mb-1">Юрлицо</label>
                <select class="field" :value="modelValue.legal_entity_id" @change="update('legal_entity_id', $event.target.value)">
                    <option value="">Все</option>
                    <option v-for="e in references?.legal_entities || []" :key="e.id" :value="e.id">{{ e.name }}</option>
                </select>
            </div>

            <div>
                <label class="stat-label block mb-1">Этап / услуга</label>
                <select class="field" :value="modelValue.service_stage" @change="update('service_stage', $event.target.value)">
                    <option value="">Все</option>
                    <option v-for="s in references?.service_stages || []" :key="s" :value="s">{{ s }}</option>
                </select>
            </div>

            <div>
                <label class="stat-label block mb-1">Статус акта</label>
                <select class="field" :value="modelValue.act_status" @change="update('act_status', $event.target.value)">
                    <option value="">Любой</option>
                    <option v-for="st in references?.act_statuses || []" :key="st.value" :value="st.value">{{ st.label }}</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2 xl:col-span-1">
                <div>
                    <label class="stat-label block mb-1">С даты</label>
                    <input class="field" type="date" :value="modelValue.date_from" @change="update('date_from', $event.target.value)" />
                </div>
                <div>
                    <label class="stat-label block mb-1">По дату</label>
                    <input class="field" type="date" :value="modelValue.date_to" @change="update('date_to', $event.target.value)" />
                </div>
            </div>
        </div>

        <div v-if="hasActive" class="mt-2.5 flex justify-end">
            <button class="btn btn-ghost" @click="emit('reset')">Сбросить фильтры</button>
        </div>
    </div>
</template>

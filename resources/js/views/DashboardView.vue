<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import api from '../api.js';
import SummaryCards from '../components/SummaryCards.vue';
import FiltersBar from '../components/FiltersBar.vue';
import PaymentsTable from '../components/PaymentsTable.vue';
import Pagination from '../components/Pagination.vue';

const references = ref(null);
const summary = ref(null);
const payments = ref([]);
const meta = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 });
const loading = ref(false);

const emptyFilters = () => ({
    search: '', project_id: '', legal_entity_id: '',
    service_stage: '', act_status: '', date_from: '', date_to: '',
});
const filters = reactive(emptyFilters());
const page = ref(1);

function activeParams() {
    const params = {};
    for (const [k, v] of Object.entries(filters)) if (v) params[k] = v;
    return params;
}

async function loadData() {
    loading.value = true;
    try {
        const params = activeParams();
        // Сводка и таблица грузятся параллельно и с одними фильтрами — цифры согласованы.
        const [sum, list] = await Promise.all([
            api.summary(params),
            api.payments({ ...params, page: page.value, per_page: 25 }),
        ]);
        summary.value = sum;
        payments.value = list.data;
        meta.value = list.meta;
    } finally {
        loading.value = false;
    }
}

let debounce;
watch(filters, () => {
    page.value = 1;
    clearTimeout(debounce);
    debounce = setTimeout(loadData, 250); // мягкий дебаунс для поиска/фильтров
}, { deep: true });

watch(page, loadData);

function resetFilters() {
    Object.assign(filters, emptyFilters());
}

function changePage(p) { page.value = p; }

// Локальное обновление строки после изменения акта (без полной перезагрузки).
function onActUpdated({ paymentId, act }) {
    const row = payments.value.find((p) => p.id === paymentId);
    if (row) {
        row.act = act;
        row.status = act.status;
    }
    // Обновляем только сводку (агрегаты могли измениться).
    api.summary(activeParams()).then((s) => (summary.value = s));
}

onMounted(async () => {
    references.value = await api.references();
    await loadData();
});
</script>

<template>
    <div class="space-y-4">
        <SummaryCards :summary="summary" />
        <FiltersBar v-model="filters" :references="references" @reset="resetFilters" />
        <PaymentsTable :payments="payments" :loading="loading" @act-updated="onActUpdated" />
        <Pagination :meta="meta" @change="changePage" />
    </div>
</template>

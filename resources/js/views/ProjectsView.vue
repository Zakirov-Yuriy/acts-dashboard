<script setup>
import { ref, onMounted } from 'vue';
import api from '../api.js';
import { money } from '../format.js';

const projects = ref([]);
const loading = ref(true);

const docLabel = { closed: 'Закрыт', attention: 'Требует внимания', in_progress: 'В работе' };
const docColor = { closed: 'green', attention: 'red', in_progress: 'amber' };

onMounted(async () => {
    projects.value = await api.projects();
    loading.value = false;
});
</script>

<template>
    <div class="bg-white border rounded-xl overflow-hidden" style="border-color: var(--color-line)">
        <div class="px-4 py-3 border-b" style="border-color: var(--color-line)">
            <div class="font-bold">Проекты и документооборот</div>
            <div class="text-xs" style="color: var(--color-muted)">сводка по каждому проекту: оплаты, закрытые акты, статус</div>
        </div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Проект</th>
                        <th>Юрлицо · ИНН</th>
                        <th class="text-right">Сумма оплат</th>
                        <th class="text-center">Оплат</th>
                        <th class="text-center">Закрыто / открыто</th>
                        <th class="text-center">Внимание</th>
                        <th>Документы</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in projects" :key="p.project_id"
                        :class="{ attention: p.doc_status === 'attention' }">
                        <td class="font-medium">{{ p.project_name }}</td>
                        <td>
                            <div>{{ p.legal_entity_name }}</div>
                            <div class="text-xs" style="color: var(--color-muted)">ИНН {{ p.inn }}</div>
                        </td>
                        <td class="num text-right font-semibold whitespace-nowrap">{{ money(p.total_amount) }}</td>
                        <td class="num text-center">{{ p.payments_count }}</td>
                        <td class="num text-center">
                            <span style="color:#1a7f43">{{ p.closed_acts_count }}</span>
                            <span style="color: var(--color-muted)"> / </span>
                            <span style="color:#9a6306">{{ p.open_acts_count }}</span>
                        </td>
                        <td class="num text-center">
                            <span v-if="p.needs_attention_count > 0" class="badge badge-red">{{ p.needs_attention_count }}</span>
                            <span v-else style="color: var(--color-muted)">—</span>
                        </td>
                        <td><span class="badge" :class="`badge-${docColor[p.doc_status]}`">{{ docLabel[p.doc_status] }}</span></td>
                    </tr>
                    <tr v-if="!loading && projects.length === 0">
                        <td colspan="7" class="text-center py-8" style="color: var(--color-muted)">Нет проектов</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import StatusBadge from './StatusBadge.vue';
import { money, date } from '../format.js';

const props = defineProps({
    payments: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
});
const emit = defineEmits(['act-updated']);

// Локальная сортировка текущей страницы (бизнес-фильтрация — на бэкенде).
const sort = ref({ key: 'payment_date', dir: 'desc' });
const savingId = ref(null);

function toggleSort(key) {
    if (sort.value.key === key) {
        sort.value.dir = sort.value.dir === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value = { key, dir: 'asc' };
    }
}

const sorted = computed(() => {
    const rows = [...props.payments];
    const { key, dir } = sort.value;
    const mul = dir === 'asc' ? 1 : -1;
    return rows.sort((a, b) => {
        let av, bv;
        if (key === 'amount') { av = a.amount; bv = b.amount; }
        else if (key === 'project') { av = a.project.name; bv = b.project.name; }
        else if (key === 'status') { av = a.status.label; bv = b.status.label; }
        else { av = a.payment_date; bv = b.payment_date; }
        if (av < bv) return -1 * mul;
        if (av > bv) return 1 * mul;
        return 0;
    });
});

function caret(key) {
    if (sort.value.key !== key) return '';
    return sort.value.dir === 'asc' ? ' ↑' : ' ↓';
}

// Отметить отправлен/подписан через API и обновить строку.
import api from '../api.js';
async function patch(payment, payload) {
    if (!payment.act) return;
    savingId.value = payment.id;
    try {
        const act = await api.updateAct(payment.act.id, payload);
        emit('act-updated', { paymentId: payment.id, act });
    } finally {
        savingId.value = null;
    }
}

async function saveComment(payment, event) {
    const value = event.target.value;
    if (payment.act && value !== payment.act.manager_comment) {
        await patch(payment, { manager_comment: value });
    }
}
</script>

<template>
    <div class="bg-white border rounded-xl overflow-hidden" style="border-color: var(--color-line)">
        <div class="overflow-x-auto" style="max-height: 62vh">
            <table class="tbl">
                <thead>
                    <tr>
                        <th class="sortable" @click="toggleSort('payment_date')">Дата{{ caret('payment_date') }}</th>
                        <th class="sortable" @click="toggleSort('project')">Проект / юрлицо{{ caret('project') }}</th>
                        <th>Назначение · этап</th>
                        <th class="sortable text-right" @click="toggleSort('amount')">Сумма{{ caret('amount') }}</th>
                        <th class="text-center">Отправлен</th>
                        <th class="text-center">Подписан</th>
                        <th class="sortable" @click="toggleSort('status')">Статус{{ caret('status') }}</th>
                        <th>Комментарий</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in sorted" :key="p.id" :class="{ attention: p.status.value === 'needs_attention' }">
                        <td class="num whitespace-nowrap">{{ date(p.payment_date) }}</td>
                        <td>
                            <div class="font-medium">{{ p.project.name }}</div>
                            <div class="text-xs" style="color: var(--color-muted)">
                                {{ p.legal_entity.name }} · ИНН {{ p.legal_entity.inn }}
                            </div>
                        </td>
                        <td class="max-w-[260px]">
                            <div class="text-xs leading-snug" style="color: var(--color-muted)">{{ p.payment_purpose }}</div>
                            <span class="chip mt-1 inline-block">{{ p.service_stage }}</span>
                        </td>
                        <td class="num text-right font-semibold whitespace-nowrap">{{ money(p.amount) }}</td>
                        <td class="text-center">
                            <input type="checkbox" class="toggle" :checked="p.act?.is_sent"
                                   :disabled="savingId === p.id"
                                   @change="patch(p, { is_sent: $event.target.checked })" />
                        </td>
                        <td class="text-center">
                            <input type="checkbox" class="toggle" :checked="p.act?.is_signed"
                                   :disabled="savingId === p.id"
                                   @change="patch(p, { is_signed: $event.target.checked })" />
                        </td>
                        <td><StatusBadge :status="p.status" /></td>
                        <td class="min-w-[180px]">
                            <input class="field" type="text" placeholder="заметка…"
                                   :value="p.act?.manager_comment"
                                   @change="saveComment(p, $event)" />
                        </td>
                    </tr>
                    <tr v-if="!loading && sorted.length === 0">
                        <td colspan="8" class="text-center py-8" style="color: var(--color-muted)">
                            Ничего не найдено. Измените фильтры.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

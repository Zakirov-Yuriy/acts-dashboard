<script setup>
import { money } from '../format.js';

defineProps({
    summary: { type: Object, default: null },
});
</script>

<template>
    <div v-if="summary" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
        <div class="stat-card">
            <div class="stat-label">Всего оплат</div>
            <div class="stat-value num">{{ money(summary.total_amount) }}</div>
            <div class="stat-sub">{{ summary.payments_count }} платежей · {{ summary.projects_count }} проектов</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Закрыто актами</div>
            <div class="stat-value num" style="color:#1a7f43">{{ money(summary.closed_amount) }}</div>
            <div class="stat-sub">{{ summary.closed_acts_count }} закрытых актов</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Не закрыто</div>
            <div class="stat-value num" style="color:#9a6306">{{ money(summary.open_amount) }}</div>
            <div class="stat-sub">ожидают документов</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Без отправленного акта</div>
            <div class="stat-value num">{{ summary.without_sent_act_count }}</div>
            <div class="stat-sub">акт ещё не выставлен</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Отправлен, не подписан</div>
            <div class="stat-value num">{{ summary.sent_not_signed_count }}</div>
            <div class="stat-sub">ждём подпись клиента</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Требуют внимания</div>
            <div class="stat-value num" style="color:#c0341d">{{ summary.needs_attention_count }}</div>
            <div class="stat-sub">просрочка по документам</div>
        </div>
    </div>
    <div v-else class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
        <div v-for="n in 6" :key="n" class="stat-card animate-pulse">
            <div class="h-3 w-20 bg-gray-200 rounded"></div>
            <div class="h-6 w-24 bg-gray-200 rounded mt-3"></div>
        </div>
    </div>
</template>

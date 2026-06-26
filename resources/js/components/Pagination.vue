<script setup>
const props = defineProps({
    meta: { type: Object, required: true }, // { current_page, last_page, total, from, to }
});
const emit = defineEmits(['change']);

function go(page) {
    if (page >= 1 && page <= props.meta.last_page && page !== props.meta.current_page) {
        emit('change', page);
    }
}
</script>

<template>
    <div class="flex items-center justify-between px-3 py-2.5 text-sm" style="color: var(--color-muted)">
        <div>
            <template v-if="meta.total > 0">
                Показаны {{ meta.from }}–{{ meta.to }} из {{ meta.total }}
            </template>
            <template v-else>Нет данных по фильтрам</template>
        </div>
        <div class="flex items-center gap-1.5" v-if="meta.last_page > 1">
            <button class="btn" :disabled="meta.current_page <= 1" @click="go(meta.current_page - 1)">Назад</button>
            <span class="chip">{{ meta.current_page }} / {{ meta.last_page }}</span>
            <button class="btn" :disabled="meta.current_page >= meta.last_page" @click="go(meta.current_page + 1)">Вперёд</button>
        </div>
    </div>
</template>

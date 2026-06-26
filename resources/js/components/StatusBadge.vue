<script setup>
defineProps({
    status: { type: Object, required: true }, // { value, label, color }
});
</script>

<template>
    <span class="badge" :class="`badge-${status.color}`">{{ status.label }}</span>
</template>

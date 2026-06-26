<script setup>
import { RouterLink, RouterView } from 'vue-router';
</script>

<template>
    <div class="min-h-full">
        <header class="bg-white border-b" style="border-color: var(--color-line)">
            <div class="max-w-[1400px] mx-auto px-5 py-3 flex items-center gap-6">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-sm font-bold"
                         style="background: var(--color-accent)">А</div>
                    <div>
                        <div class="font-bold leading-tight">Акт-контроль</div>
                        <div class="text-xs" style="color: var(--color-muted)">оплаты · этапы · закрывающие документы</div>
                    </div>
                </div>
                <nav class="flex items-center gap-1 ml-2">
                    <RouterLink to="/" class="btn btn-ghost" active-class="btn-accent" exact-active-class="btn-accent">Оплаты</RouterLink>
                    <RouterLink to="/projects" class="btn btn-ghost" active-class="btn-accent">Проекты</RouterLink>
                </nav>
            </div>
        </header>

        <main class="max-w-[1400px] mx-auto px-5 py-6">
            <RouterView />
        </main>
    </div>
</template>

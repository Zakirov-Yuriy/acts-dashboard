<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Закрывающий документ (акт) по оплате. Один акт на оплату.
// Поля is_sent / is_signed хранятся; итоговый статус ВЫЧИСЛЯЕТСЯ в слое логики
// (App\Support\ActStatus), а не хранится в БД, чтобы не устаревать со временем.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->unique()->constrained('payments')->cascadeOnDelete();
            $table->boolean('is_sent')->default(false);
            $table->date('sent_at')->nullable();
            $table->boolean('is_signed')->default(false);
            $table->date('signed_at')->nullable();
            $table->text('manager_comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acts');
    }
};

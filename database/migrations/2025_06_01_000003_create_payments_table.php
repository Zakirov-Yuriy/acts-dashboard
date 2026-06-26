<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Оплата. Привязана к проекту и плательщику (юрлицу).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('legal_entity_id')->constrained('legal_entities'); // плательщик
            $table->date('payment_date')->index();
            $table->decimal('amount', 14, 2);
            $table->text('payment_purpose');              // назначение платежа из выписки
            $table->string('service_stage');              // этап / тип услуги
            $table->string('invoice_number')->nullable(); // № счёта
            $table->string('contract_number')->nullable();// № договора
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

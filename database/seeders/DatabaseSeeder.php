<?php

namespace Database\Seeders;

use App\Models\Act;
use App\Models\LegalEntity;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Реалистичные мок-данные digital-агентства.
 *
 * Имитируют то, что пришло бы из банковской выписки / CRM:
 * несколько юрлиц-клиентов, по несколько проектов, по несколько оплат на проект,
 * разные типы услуг и акты во всех четырёх статусах.
 *
 * Данные детерминированы (фиксированный seed), чтобы у проверяющего была
 * та же картина, что и в описании.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(20250601);

        // Клиенты (юрлица). ИНН/ОГРН синтетические, но валидной длины.
        $entities = collect([
            ['name' => 'ООО «Кофейный Дом»',   'inn' => '7714056789', 'kpp' => '771401001', 'ogrn' => '1027700132195', 'bank' => 'ПАО Сбербанк',        'person' => 'Орлова Мария'],
            ['name' => 'ООО «Свежий Хлеб»',     'inn' => '5024112233', 'kpp' => '502401001', 'ogrn' => '1095024004412', 'bank' => 'АО «Альфа-Банк»',      'person' => 'Гусев Дмитрий'],
            ['name' => 'ООО «АвтоПрайд»',       'inn' => '3445098765', 'kpp' => '344501001', 'ogrn' => '1063445076210', 'bank' => 'АО «Тинькофф Банк»',   'person' => 'Лебедев Артём'],
            ['name' => 'ИП Кузнецов А. В.',     'inn' => '344301122334', 'kpp' => null,      'ogrn' => '318344300045678', 'bank' => 'АО «Тинькофф Банк»', 'person' => 'Кузнецов Андрей'],
            ['name' => 'ООО «МедСервис Плюс»',  'inn' => '7727445566', 'kpp' => '772701001', 'ogrn' => '1117746889900', 'bank' => 'ПАО Сбербанк',         'person' => 'Власова Елена'],
            ['name' => 'ООО «ТеплоДом»',        'inn' => '6671334455', 'kpp' => '667101001', 'ogrn' => '1086671007711', 'bank' => 'АО «Альфа-Банк»',      'person' => 'Семёнов Игорь'],
        ])->map(fn ($e) => LegalEntity::create([
            'name' => $e['name'],
            'inn' => $e['inn'],
            'kpp' => $e['kpp'],
            'ogrn' => $e['ogrn'],
            'bank_account' => '4070'.str_pad((string) mt_rand(0, 9999999999999999), 16, '0', STR_PAD_LEFT),
            'bank_name' => $e['bank'],
            'contact_person' => $e['person'],
        ]));

        // Проекты: имя => индекс клиента (0-based).
        $projectCatalog = [
            ['Сайт сети кофеен «Кофейный Дом»', 0],
            ['SEO-продвижение «Кофейный Дом»', 0],
            ['Интернет-магазин «Свежий Хлеб»', 1],
            ['Брендинг «Свежий Хлеб»', 1],
            ['Лендинг весенней акции «АвтоПрайд»', 2],
            ['Контекстная реклама «АвтоПрайд»', 2],
            ['Сайт-портфолио Кузнецова', 3],
            ['Сайт клиники + онлайн-запись', 4],
            ['SEO и контент «МедСервис»', 4],
            ['Корпоративный сайт «ТеплоДом»', 5],
        ];

        $projects = collect($projectCatalog)->map(fn ($p) => Project::create([
            'name' => $p[0],
            'legal_entity_id' => $entities[$p[1]]->id,
            'status' => 'active',
        ]));

        $stages = [
            'Разработка сайта', 'SEO', 'Контекстная реклама', 'Дизайн',
            'Контент', 'Сопровождение', 'Брендинг',
            'Этап 1. Прототип', 'Этап 2. Вёрстка', 'Этап 3. Бэкенд',
        ];

        $today = Carbon::today();

        foreach ($projects as $project) {
            $client = $entities->firstWhere('id', $project->legal_entity_id);
            $paymentsCount = mt_rand(2, 5);

            for ($i = 0; $i < $paymentsCount; $i++) {
                // Возраст оплаты: от 3 до 165 дней назад.
                $ageDays = mt_rand(3, 165);
                $date = $today->copy()->subDays($ageDays);
                $stage = $stages[array_rand($stages)];
                $amount = mt_rand(15, 350) * 1000 + mt_rand(0, 999);

                $payment = Payment::create([
                    'project_id' => $project->id,
                    'legal_entity_id' => $client->id, // плательщик = клиент
                    'payment_date' => $date->toDateString(),
                    'amount' => $amount,
                    'payment_purpose' => sprintf(
                        'Оплата по договору %s за %s. Без НДС.',
                        'Д-'.mt_rand(1000, 9999).'/25',
                        mb_strtolower($stage)
                    ),
                    'service_stage' => $stage,
                    'invoice_number' => 'СЧ-'.mt_rand(1000, 9999),
                    'contract_number' => 'Д-'.mt_rand(1000, 9999).'/25',
                ]);

                $this->makeAct($payment, $ageDays, $date, $today);
            }
        }

        $this->command?->info('Создано: юрлиц '.LegalEntity::count()
            .', проектов '.Project::count()
            .', оплат '.Payment::count()
            .', актов '.Act::count());
    }

    /**
     * Создать акт так, чтобы в выборке встречались все 4 статуса.
     * Логика зависит от возраста оплаты, как в реальной операционке.
     */
    private function makeAct(Payment $payment, int $ageDays, Carbon $date, Carbon $today): void
    {
        $roll = mt_rand(1, 100);

        if ($ageDays > 30) {
            // Старые оплаты: чаще закрыты, но есть и «зависшие».
            if ($roll <= 60) {
                // Закрыт: отправлен и подписан.
                $sentAt = $date->copy()->addDays(mt_rand(2, 7));
                Act::create([
                    'payment_id' => $payment->id,
                    'is_sent' => true,
                    'sent_at' => $sentAt->toDateString(),
                    'is_signed' => true,
                    'signed_at' => $sentAt->copy()->addDays(mt_rand(1, 10))->toDateString(),
                    'manager_comment' => null,
                ]);
            } elseif ($roll <= 80) {
                // Требует внимания: акт так и не отправлен.
                Act::create([
                    'payment_id' => $payment->id,
                    'is_sent' => false,
                    'is_signed' => false,
                    'manager_comment' => 'Не выставлен акт, уточнить у бухгалтерии',
                ]);
            } else {
                // Требует внимания: отправлен давно, клиент не подписывает.
                $sentAt = $date->copy()->addDays(mt_rand(2, 6));
                Act::create([
                    'payment_id' => $payment->id,
                    'is_sent' => true,
                    'sent_at' => $sentAt->toDateString(),
                    'is_signed' => false,
                    'manager_comment' => 'Отправлен, ждём подпись более двух недель',
                ]);
            }

            return;
        }

        // Свежие оплаты (<= 30 дней).
        if ($roll <= 40) {
            // Ещё не отправлен (в пределах нормы).
            Act::create([
                'payment_id' => $payment->id,
                'is_sent' => false,
                'is_signed' => false,
                'manager_comment' => null,
            ]);
        } elseif ($roll <= 75) {
            // Ожидает подписи: отправлен недавно.
            $sentAt = $date->copy()->addDays(mt_rand(1, 5));
            Act::create([
                'payment_id' => $payment->id,
                'is_sent' => true,
                'sent_at' => $sentAt->toDateString(),
                'is_signed' => false,
                'manager_comment' => null,
            ]);
        } else {
            // Закрыт быстро.
            $sentAt = $date->copy()->addDays(mt_rand(1, 3));
            Act::create([
                'payment_id' => $payment->id,
                'is_sent' => true,
                'sent_at' => $sentAt->toDateString(),
                'is_signed' => true,
                'signed_at' => $sentAt->copy()->addDays(mt_rand(1, 5))->toDateString(),
                'manager_comment' => null,
            ]);
        }
    }
}

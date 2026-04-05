<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $newStatuses = [
        'submitted',
        'resubmission_required',
        'forwarded_to_mswdo',
        'additional_docs_required',
        'pending_assistance_code',
        'forwarded_to_mayors_office',
        'code_adjustment_required',
        'pending_voucher',
        'forwarded_to_accounting',
        'voucher_adjustment_required',
        'pending_cheque',
        'cheque_on_hold',
        'cheque_ready',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $legacyApplicationStatuses = [
            'submitted',
            'under_review',
            'rejected',
            'resubmission_required',
            'forwarded_to_mswd',
            'pending_additional_docs',
            'approved_by_mswd',
            'coding',
            'forwarded_to_mayor',
            'approved_by_mayor',
            'voucher_preparation',
            'forwarded_to_accounting',
            'forwarded_to_treasury',
            'on_hold',
            'cheque_ready',
            'claimed',
        ];

        $legacyReviewDecisions = [
            'approved',
            'rejected',
            'resubmission_required',
            'adjustment_requested',
            'on_hold',
        ];

        $statusMap = [
            'under_review' => 'submitted',
            'forwarded_to_mswd' => 'forwarded_to_mswdo',
            'pending_additional_docs' => 'additional_docs_required',
            'approved_by_mswd' => 'pending_assistance_code',
            'coding' => 'pending_assistance_code',
            'forwarded_to_mayor' => 'forwarded_to_mayors_office',
            'approved_by_mayor' => 'pending_voucher',
            'voucher_preparation' => 'pending_voucher',
            'forwarded_to_treasury' => 'pending_cheque',
            'on_hold' => 'cheque_on_hold',
            'claimed' => 'cheque_ready',
            'rejected' => 'resubmission_required',
        ];

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        if (Schema::hasTable('application') && Schema::hasColumn('application', 'status')) {
            if (! $isSqlite) {
                DB::statement(
                    sprintf(
                        "ALTER TABLE `application` MODIFY COLUMN `status` ENUM(%s) NOT NULL DEFAULT 'submitted'",
                        $this->enumList(array_values(array_unique(array_merge($legacyApplicationStatuses, $this->newStatuses))))
                    )
                );
            }

            foreach ($statusMap as $from => $to) {
                DB::table('application')
                    ->where('status', $from)
                    ->update(['status' => $to]);
            }

            if (! $isSqlite) {
                DB::statement(
                    sprintf(
                        "ALTER TABLE `application` MODIFY COLUMN `status` ENUM(%s) NOT NULL DEFAULT 'submitted'",
                        $this->enumList($this->newStatuses)
                    )
                );
            }
        }

        if (Schema::hasTable('application_log')) {
            if (Schema::hasColumn('application_log', 'from_status')) {
                foreach ($statusMap as $from => $to) {
                    DB::table('application_log')
                        ->where('from_status', $from)
                        ->update(['from_status' => $to]);
                }
            }

            if (Schema::hasColumn('application_log', 'to_status')) {
                foreach ($statusMap as $from => $to) {
                    DB::table('application_log')
                        ->where('to_status', $from)
                        ->update(['to_status' => $to]);
                }
            }

            if (! Schema::hasColumn('application_log', 'decision')) {
                if ($isSqlite) {
                    Schema::table('application_log', static function (Blueprint $table): void {
                        $table->string('decision')->nullable();
                    });
                } else {
                    DB::statement(
                        sprintf(
                            "ALTER TABLE `application_log` ADD COLUMN `decision` ENUM(%s) NULL AFTER `action`",
                            $this->enumList($this->newStatuses)
                        )
                    );
                }
            }

            if (Schema::hasColumn('application_log', 'decision')) {
                if (! $isSqlite) {
                    DB::statement(
                        sprintf(
                            "ALTER TABLE `application_log` MODIFY COLUMN `decision` ENUM(%s) NULL",
                            $this->enumList(array_values(array_unique(array_merge($this->newStatuses, $legacyApplicationStatuses, $legacyReviewDecisions))))
                        )
                    );
                }

                foreach ($statusMap as $from => $to) {
                    DB::table('application_log')
                        ->where('decision', $from)
                        ->update(['decision' => $to]);
                }

                DB::table('application_log')
                    ->where('decision', 'approved')
                    ->update(['decision' => 'forwarded_to_mswdo']);

                DB::table('application_log')
                    ->where('decision', 'on_hold')
                    ->update(['decision' => 'cheque_on_hold']);

                DB::table('application_log')
                    ->where('decision', 'adjustment_requested')
                    ->update(['decision' => 'code_adjustment_required']);

                DB::table('application_log')
                    ->where('decision', 'rejected')
                    ->update(['decision' => 'resubmission_required']);

                DB::statement('UPDATE `application_log` SET `decision` = `to_status` WHERE `decision` IS NULL AND `to_status` IS NOT NULL');

                if (! $isSqlite) {
                    DB::statement(
                        sprintf(
                            "ALTER TABLE `application_log` MODIFY COLUMN `decision` ENUM(%s) NULL",
                            $this->enumList($this->newStatuses)
                        )
                    );
                }
            }
        }

        if (Schema::hasTable('application_review') && Schema::hasColumn('application_review', 'decision')) {
            if (! $isSqlite) {
                DB::statement(
                    sprintf(
                        "ALTER TABLE `application_review` MODIFY COLUMN `decision` ENUM(%s) NOT NULL",
                        $this->enumList(array_values(array_unique(array_merge($legacyReviewDecisions, $legacyApplicationStatuses, $this->newStatuses))))
                    )
                );
            }

            foreach ($statusMap as $from => $to) {
                DB::table('application_review')
                    ->where('decision', $from)
                    ->update(['decision' => $to]);
            }

            DB::table('application_review')
                ->where('decision', 'approved')
                ->update(['decision' => 'forwarded_to_mswdo']);

            DB::table('application_review')
                ->where('decision', 'on_hold')
                ->update(['decision' => 'cheque_on_hold']);

            DB::table('application_review')
                ->where('decision', 'adjustment_requested')
                ->update(['decision' => 'code_adjustment_required']);

            if (! $isSqlite) {
                DB::statement(
                    sprintf(
                        "ALTER TABLE `application_review` MODIFY COLUMN `decision` ENUM(%s) NOT NULL",
                        $this->enumList($this->newStatuses)
                    )
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $legacyStatuses = [
            'submitted',
            'under_review',
            'rejected',
            'resubmission_required',
            'forwarded_to_mswd',
            'pending_additional_docs',
            'approved_by_mswd',
            'coding',
            'forwarded_to_mayor',
            'approved_by_mayor',
            'voucher_preparation',
            'forwarded_to_accounting',
            'forwarded_to_treasury',
            'on_hold',
            'cheque_ready',
            'claimed',
        ];

        if (Schema::hasTable('application') && Schema::hasColumn('application', 'status')) {
            DB::statement(
                sprintf(
                    "ALTER TABLE `application` MODIFY COLUMN `status` ENUM(%s) NOT NULL DEFAULT 'submitted'",
                    $this->enumList($legacyStatuses)
                )
            );
        }

        if (Schema::hasTable('application_review') && Schema::hasColumn('application_review', 'decision')) {
            DB::statement(
                "ALTER TABLE `application_review` MODIFY COLUMN `decision` ENUM('approved', 'rejected', 'resubmission_required', 'adjustment_requested', 'on_hold') NOT NULL"
            );
        }

        if (Schema::hasTable('application_log') && Schema::hasColumn('application_log', 'decision')) {
            DB::statement('ALTER TABLE `application_log` DROP COLUMN `decision`');
        }
    }

    /**
     * @param  list<string>  $values
     */
    private function enumList(array $values): string
    {
        return implode(', ', array_map(static fn (string $value): string => "'" . str_replace("'", "\\'", $value) . "'", $values));
    }
};

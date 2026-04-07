<?php

namespace App\Support;

final class ApplicationStatuses
{
    public const SUBMITTED = 'submitted';

    public const RESUBMISSION_REQUIRED = 'resubmission_required';

    public const FORWARDED_TO_MSWDO = 'forwarded_to_mswdo';

    public const ADDITIONAL_DOCS_REQUIRED = 'additional_docs_required';

    public const PENDING_ASSISTANCE_CODE = 'pending_assistance_code';

    public const FORWARDED_TO_MAYORS_OFFICE = 'forwarded_to_mayors_office';

    public const CODE_ADJUSTMENT_REQUIRED = 'code_adjustment_required';

    public const PENDING_VOUCHER = 'pending_voucher';

    public const FORWARDED_TO_ACCOUNTING = 'forwarded_to_accounting';

    public const VOUCHER_ADJUSTMENT_REQUIRED = 'voucher_adjustment_required';

    public const PENDING_CHEQUE = 'pending_cheque';

    public const CHEQUE_ON_HOLD = 'cheque_on_hold';

    public const CHEQUE_READY = 'cheque_ready';

    public const CLAIMED = 'claimed';

    public const CHEQUE_CLAIMED = 'cheque_claimed';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::SUBMITTED,
            self::RESUBMISSION_REQUIRED,
            self::FORWARDED_TO_MSWDO,
            self::ADDITIONAL_DOCS_REQUIRED,
            self::PENDING_ASSISTANCE_CODE,
            self::FORWARDED_TO_MAYORS_OFFICE,
            self::CODE_ADJUSTMENT_REQUIRED,
            self::PENDING_VOUCHER,
            self::FORWARDED_TO_ACCOUNTING,
            self::VOUCHER_ADJUSTMENT_REQUIRED,
            self::PENDING_CHEQUE,
            self::CHEQUE_ON_HOLD,
            self::CHEQUE_READY,
        ];
    }

    /**
     * @return list<string>
     */
    public static function aicsPrimaryQueue(): array
    {
        return [
            self::SUBMITTED,
            self::RESUBMISSION_REQUIRED,
            self::FORWARDED_TO_MSWDO,
            self::PENDING_ASSISTANCE_CODE,
        ];
    }

    /**
     * @return list<string>
     */
    public static function pendingReview(): array
    {
        return [
            self::SUBMITTED,
            self::RESUBMISSION_REQUIRED,
        ];
    }

    /**
     * @return list<string>
     */
    public static function pendingStatuses(): array
    {
        return [
            self::SUBMITTED,
            self::PENDING_ASSISTANCE_CODE,
            self::PENDING_VOUCHER,
            self::PENDING_CHEQUE,
            self::CHEQUE_ON_HOLD,
            self::CHEQUE_READY,
        ];
    }

    /**
     * @return list<string>
     */
    public static function forwardedStatuses(): array
    {
        return [
            self::FORWARDED_TO_MSWDO,
            self::FORWARDED_TO_MAYORS_OFFICE,
            self::FORWARDED_TO_ACCOUNTING,
        ];
    }

    /**
     * @return list<string>
     */
    public static function returnedStatuses(): array
    {
        return [
            self::RESUBMISSION_REQUIRED,
            self::ADDITIONAL_DOCS_REQUIRED,
            self::CODE_ADJUSTMENT_REQUIRED,
            self::VOUCHER_ADJUSTMENT_REQUIRED,
        ];
    }

    /**
     * @return list<string>
     */
    public static function assistancesServedStatuses(): array
    {
        return [
            self::CHEQUE_CLAIMED,
            self::CLAIMED,
        ];
    }

    public static function label(string $status): string
    {
        return str($status)->replace('_', ' ')->title()->toString();
    }
}

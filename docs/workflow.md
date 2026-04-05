# Application Workflow

## Overview

The AICS application uses a canonical status set shared by runtime DB, review decisions, and tracking labels. Workflow progression is still linear, with controlled loops for resubmission and adjustment requests.

## Canonical `APPLICATION.status` values

- `submitted`
- `resubmission_required`
- `forwarded_to_mswdo`
- `additional_docs_required`
- `pending_assistance_code`
- `forwarded_to_mayors_office`
- `code_adjustment_required`
- `pending_voucher`
- `forwarded_to_accounting`
- `voucher_adjustment_required`
- `pending_cheque`
- `cheque_on_hold`
- `cheque_ready`

## Main applicant-facing stage bands

- Pending
- In Process
- Cheque Prepared
- Cheque Claimed

> Note: `Cheque Claimed` is a presentation stage based on cheque-claim completion state (and legacy claimed status values where present).

## Workflow steps (current)

### 1) Submission

- Actor: Applicant (or staff-assisted intake)
- Intake form validates required fields and document uploads
- Uploaded JPG/JPEG requirement files are converted to PDF and stored in Supabase
- `application.status` is set to `submitted`

### 2) AICS validation and resubmission request loop

- Actor: AICS Staff
- Review outcome is written to `application_review` (`stage = aics_validation`, `decision = ...`)
- If incomplete, status/decision becomes `resubmission_required`
- Applicant tracking history shows review stage/decision (e.g., `Aics Validation (Resubmission Required)`)
- When applicant successfully resubmits requested docs, a new `Submitted` history event is recorded

### 3) Requested-document resubmission behavior

- Only staff-requested document slots are accepted
- Uploads are validated (JPG/JPEG), converted to PDF, and stored
- Requested document rows are replaced in place (update behavior), not appended
- Superseded file objects are deleted from Supabase after successful replacement
- Status returns to `submitted` only after successful resubmission upload

### 4) Downstream processing stages

- `forwarded_to_mswdo` / `additional_docs_required`
- `pending_assistance_code` / `forwarded_to_mayors_office` / `code_adjustment_required`
- `pending_voucher` / `forwarded_to_accounting` / `voucher_adjustment_required`
- `pending_cheque` / `cheque_on_hold` / `cheque_ready`

## Tracking-history rendering rules (current)

- Applicant detailed history is review-first:
    - Submission events (`Submitted`)
    - `APPLICATION_REVIEW` stage + decision entries
- Directional `from_status → to_status` lines from `application_log` are not used as the primary applicant-facing history format

## Rules

- Statuses must use the canonical enum list
- Review actions should persist `application_review` stage/decision entries
- Resubmission must replace requested document rows and remove old storage files
- Status transitions should remain auditable in logs/reviews and trigger the required notification flow

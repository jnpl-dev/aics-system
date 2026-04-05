# Entity Descriptions

## 1. User

Stores all system user accounts. Role field controls access. All actions are traceable to a User.

## 2. AssistanceCategory

Types of assistance available. Drives the dynamic requirements list shown to applicants.

## 3. Requirement

Requirements per category. is_mandatory controls whether it is always required. Authorization letter is is_mandatory = FALSE and enforced conditionally in code.

## 4. Application

Core record. Stores applicant and beneficiary personal information. submitted_by is nullable — null means self-submitted by applicant, populated means staff-assisted walk-in.

## 5. Document

All uploaded files. requirement_id is nullable — null means the document is not tied to a specific requirement (e.g. authorization letter, allotment slip). For applicant resubmission, requested document rows are replaced/updated and prior file objects are removed from storage.

## 6. ApplicationLog

Operational history and status-related audit trail (e.g. forwards, resubmission uploads, workflow updates). `from_status` / `to_status` may be populated for transitions, but applicant-facing tracking history is not rendered as directional arrows.

## 7. ApplicationReview

Formal review record per stage. Captures stage, decision, and feedback remarks. Applicant-facing detailed history primarily displays these stage/decision review entries.

## 8. SocialCaseStudy

Uploaded social case study form by MSWD Officer. File-based record only — no structured findings fields.

## 9. CodeReference

Lookup table for assistance codes A-F with fixed amounts. Managed by admin only.

## 10. AssistanceCode

Code assigned by AICS Staff referencing CodeReference. adjustment_status handles Mayor's Office adjustment requests without a separate table.

## 11. Voucher

Financial voucher prepared by MSWD Officer. amount may differ from CodeReference amount after adjustments. adjustment_status handles Accountant adjustment requests.

## 12. Cheque

Final financial instrument. claimed_at records when applicant physically claimed it.

## 13. Notification

Auto-SMS log. Every key status change triggers a notification record and SMS send.

## 14. AuditLog

System-wide activity trail for admin. Covers all modules not just application movement.

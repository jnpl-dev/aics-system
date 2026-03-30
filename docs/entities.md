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
All uploaded files. requirement_id is nullable — null means the document is not tied to a specific requirement (e.g. authorization letter, allotment slip).

## 6. ApplicationLog
Parcel-tracking style history of every status change. Every status transition must create a row here.

## 7. ApplicationReview
Formal review record per stage. Captures decision and feedback remarks. Separate from ApplicationLog which only tracks status movement.

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
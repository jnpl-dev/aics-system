# Architectural and Design Decisions

## Decision Log

| #   | Decision                                                                | Reason                                                                                                                                      | Date       |
| --- | ----------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------- | ---------- |
| 1   | Single USER table for all staff roles                                   | Simplifies authentication and access control. Role field differentiates actors                                                              |            |
| 2   | Applicants do not have USER accounts                                    | Applicants access via public portal using reference code and surname for tracking                                                           |            |
| 3   | SocialCaseStudy is a file-based table                                   | Case study is a physical form scanned and uploaded — no structured data needed at this stage                                                |            |
| 4   | CodeReference lookup table for codes A-F                                | Eliminates transitive dependency — code always maps to fixed amount                                                                         |            |
| 5   | Adjustment requests handled within same table                           | Voucher and AssistanceCode have adjustment columns — no separate request table needed                                                       |            |
| 6   | Report table removed                                                    | Reports are generated on demand from existing data — no need to store them                                                                  |            |
| 7   | Authorization letter enforced in code                                   | is_mandatory = FALSE in REQUIREMENT — application code checks relationship field and enforces conditionally                                 |            |
| 8   | DOCUMENT references REQUIREMENT via requirement_id FK                   | Each document fulfills a specific requirement. Nullable for situational documents like authorization letter and allotment slip              |            |
| 9   | Security handled in application code                                    | Password hashing, session management, rate limiting, and PII encryption handled in code not database                                        |            |
| 10  | applicant_dob and beneficiary_dob instead of age                        | Age is derived data — date of birth stored, age computed in code                                                                            |            |
| 11  | MSWD additional document request loops back to MSWD                     | Additional documents go directly back to MSWD not AICS Staff                                                                                |            |
| 12  | Mayor's Office code adjustment loops back to AICS Staff                 | Mayor reviews assistance code — if adjustment needed, AICS Staff revises and resubmits                                                      |            |
| 13  | Accountant voucher adjustment loops back to MSWD Officer                | Accountant flags calculation issues — MSWD Officer revises voucher                                                                          |            |
| 14  | Canonical APPLICATION.status values are explicitly enumerated           | Prevents ambiguous status handling and enforces strict workflow/state machine consistency across backend, frontend, logs, and notifications | 2026-03-30 |
| 15  | Every APPLICATION.status transition maps to a defined SMS trigger_event | Ensures compliance with mandatory auto-SMS on every status change while allowing human-readable trigger naming                              | 2026-03-30 |

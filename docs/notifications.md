# SMS Notification Triggers

## Overview

All notifications are sent automatically via SMS API provider. Every notification creates a record in the NOTIFICATION table with delivery status tracked.

## Trigger Events

| Trigger Event             | Stage                | Recipient | Message Template                                                                                                                              |
| ------------------------- | -------------------- | --------- | --------------------------------------------------------------------------------------------------------------------------------------------- |
| application_submitted     | Submission           | Applicant | Your application has been successfully submitted. Your reference code is {reference_code}. You may use this to track your application status. |
| under_review              | AICS Review          | Applicant | Your application {reference_code} is now under review by AICS Staff.                                                                          |
| resubmission_requested    | AICS Review          | Applicant | Your application {reference_code} requires additional documents. Please visit the AICS office to resubmit. Remarks: {remarks}                 |
| forwarded_to_mswd         | AICS Review          | Applicant | Your application {reference_code} has been validated and forwarded for further review.                                                        |
| additional_docs_requested | MSWD Review          | Applicant | Your application {reference_code} requires additional documents as requested by the MSWD Officer. Please submit the required documents.       |
| approved_by_mswd          | MSWD Review          | Applicant | Your application {reference_code} has been approved by the MSWD Officer and is now being processed.                                           |
| coding                    | Coding               | Applicant | Your application {reference_code} is now in assistance code assignment.                                                                       |
| forwarded_to_mayor        | Coding               | Applicant | Your application {reference_code} has been coded and forwarded for final review.                                                              |
| approved_by_mayor         | Mayor Review         | Applicant | Your application {reference_code} has been approved. Financial processing is now underway.                                                    |
| voucher_preparation       | Financial Processing | Applicant | Your application {reference_code} is now in voucher preparation.                                                                              |
| forwarded_to_accounting   | Financial Processing | Applicant | Your application {reference_code} has been forwarded to Accounting for verification.                                                          |
| forwarded_to_treasury     | Financial Processing | Applicant | Your application {reference_code} has been approved by Accounting and forwarded to Treasury.                                                  |
| on_hold                   | Treasury             | Applicant | Your application {reference_code} is currently on hold due to fund availability. You will be notified once processing resumes.                |
| cheque_ready              | Treasury             | Applicant | Your financial assistance for application {reference_code} is ready. Please visit the AICS office to claim your cheque.                       |
| claimed                   | Claiming             | Applicant | Your financial assistance for application {reference_code} has been successfully claimed. Thank you.                                          |

## Status-to-Trigger Mapping

To ensure **every APPLICATION.status change** triggers an SMS:

| APPLICATION.status      | trigger_event             |
| ----------------------- | ------------------------- |
| submitted               | application_submitted     |
| under_review            | under_review              |
| resubmission_required   | resubmission_requested    |
| forwarded_to_mswd       | forwarded_to_mswd         |
| pending_additional_docs | additional_docs_requested |
| approved_by_mswd        | approved_by_mswd          |
| coding                  | coding                    |
| forwarded_to_mayor      | forwarded_to_mayor        |
| approved_by_mayor       | approved_by_mayor         |
| voucher_preparation     | voucher_preparation       |
| forwarded_to_accounting | forwarded_to_accounting   |
| forwarded_to_treasury   | forwarded_to_treasury     |
| on_hold                 | on_hold                   |
| cheque_ready            | cheque_ready              |
| claimed                 | claimed                   |

## Rules

- All triggers fire automatically on status change — never manually
- Message templates must use the exact trigger_event values above
- Templates are configurable by admin via system settings
- Delivery status is updated via SMS API callback
- Failed notifications must be retried or flagged for manual follow-up

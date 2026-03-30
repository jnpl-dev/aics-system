# Application Workflow

## Overview
The AICS application follows a strict linear workflow. Each stage must be completed before the next can begin. Status changes must always be logged in APPLICATION_LOG and trigger the appropriate SMS notification.

## Workflow Steps

### Step 1: Application Submission (Process 2.0)
- Actor: Applicant or AICS Staff (staff-assisted)
- Applicant selects assistance type
- System displays requirements list for selected category
- Applicant fills in personal information and beneficiary information
- Applicant uploads documents per requirement
- If applicant is not an immediate family member, authorization letter is required — enforced in code via applicant_relationship_to_beneficiary field
- System generates unique reference code on submission
- Status set to: submitted
- APPLICATION_LOG entry created
- SMS notification sent: application_submitted

### Step 2: Application Review and Validation (Process 3.0)
- Actor: AICS Staff
- Status set to: under_review (when AICS Staff starts evaluation)
- AICS Staff reviews submitted documents and conducts interview
- If documents are incomplete: status set to resubmission_required, resubmission request sent to applicant, SMS triggered
- If documents are complete: status set to forwarded_to_mswd
- APPLICATION_REVIEW record created
- APPLICATION_LOG entry created
- SMS notification sent: under_review, resubmission_requested, or forwarded_to_mswd

### Step 3: Social Case Study and Further Review (Process 4.0)
- Actor: MSWD Officer
- MSWD Officer conducts further review and interview
- MSWD Officer conducts social case study and uploads form
- If additional documents are required: status set to pending_additional_docs, request sent directly to applicant, applicant submits back to MSWD
- If everything is in order: status set to approved_by_mswd, returned to AICS Staff for coding
- APPLICATION_REVIEW record created
- APPLICATION_LOG entry created
- SMS notification sent: additional_docs_requested OR approved_by_mswd

### Step 4: Assistance Code Assignment (Process 3.0)
- Actor: AICS Staff
- Status set to: coding (when AICS Staff starts code assignment)
- AICS Staff reviews social case study and selects appropriate code from CodeReference (A-F)
- ASSISTANCE_CODE record created
- Status set to: forwarded_to_mayor
- APPLICATION_LOG entry created
- SMS notification sent: coding, forwarded_to_mayor

### Step 5: Mayor's Office Review (Process 5.0)
- Actor: Mayor's Office Staff
- Mayor's Office Staff reviews application and assistance code
- If code adjustment is needed: code adjustment request sent back to AICS Staff, AICS Staff revises code, resubmitted to Mayor's Office
- If approved: status set to approved_by_mayor, returned to MSWD for voucher preparation
- APPLICATION_REVIEW record created
- APPLICATION_LOG entry created
- SMS notification sent: approved_by_mayor

### Step 6: Voucher Preparation (Process 5.0)
- Actor: MSWD Officer
- Status set to: voucher_preparation (when MSWD starts voucher preparation)
- MSWD Officer prepares voucher based on approved assistance code amount
- VOUCHER record created
- Status set to: forwarded_to_accounting
- APPLICATION_LOG entry created
- SMS notification sent: voucher_preparation, forwarded_to_accounting

### Step 7: Accounting Verification (Process 5.0)
- Actor: Accountant
- Accountant verifies voucher calculations
- If adjustment needed: voucher adjustment request sent back to MSWD Officer, MSWD revises and resubmits
- If approved: status set to forwarded_to_treasury
- APPLICATION_REVIEW record created
- APPLICATION_LOG entry created
- SMS notification sent: forwarded_to_treasury

### Step 8: Treasury and Cheque Preparation (Process 5.0)
- Actor: Treasurer
- Treasurer checks fund availability
- If funds unavailable: status set to on_hold
- If funds available: voucher approved, cheque prepared, allotment slip scanned and uploaded
- CHEQUE record created
- Status set to: cheque_ready
- APPLICATION_LOG entry created
- SMS notification sent: on_hold OR cheque_ready

### Step 9: Cheque Claiming
- Applicant is notified to return to office
- Applicant claims cheque
- Status set to: claimed
- CHEQUE claimed_at timestamp updated
- APPLICATION_LOG entry created
- SMS notification sent: claimed

## Status Flow
submitted → under_review → (resubmission_required → under_review) OR forwarded_to_mswd → (pending_additional_docs → forwarded_to_mswd) OR approved_by_mswd → coding → forwarded_to_mayor → approved_by_mayor → voucher_preparation → forwarded_to_accounting → forwarded_to_treasury → (on_hold → forwarded_to_treasury) OR cheque_ready → claimed

## Rules
- Status must never skip steps
- Every status change must create an APPLICATION_LOG entry
- Every status change must trigger the appropriate SMS notification
- Adjustment request loops do not change the main status — they are handled internally within the current stage
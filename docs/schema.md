# Database Schema

## Database: MySQL

## Tables

### USER

| Column     | Type                                                                        | Constraints                         |
| ---------- | --------------------------------------------------------------------------- | ----------------------------------- |
| user_id    | INT                                                                         | PK, AUTO_INCREMENT                  |
| first_name | VARCHAR(100)                                                                | NOT NULL                            |
| last_name  | VARCHAR(100)                                                                | NOT NULL                            |
| email      | VARCHAR(255)                                                                | NOT NULL, UNIQUE                    |
| password   | VARCHAR(255)                                                                | NOT NULL                            |
| role       | ENUM(aics_staff, mswd_officer, mayor_office_staff, accountant, treasurer, admin, system_admin) | NOT NULL                            |
| status     | ENUM(active, inactive)                                                      | NOT NULL, DEFAULT active            |
| created_at | DATETIME                                                                    | NOT NULL, DEFAULT CURRENT_TIMESTAMP |

### ASSISTANCE_CATEGORY

| Column      | Type         | Constraints                         |
| ----------- | ------------ | ----------------------------------- |
| category_id | INT          | PK, AUTO_INCREMENT                  |
| name        | VARCHAR(255) | NOT NULL                            |
| description | TEXT         |                                     |
| is_active   | BOOLEAN      | NOT NULL, DEFAULT TRUE              |
| created_at  | DATETIME     | NOT NULL, DEFAULT CURRENT_TIMESTAMP |

### REQUIREMENT

| Column         | Type         | Constraints              |
| -------------- | ------------ | ------------------------ |
| requirement_id | INT          | PK, AUTO_INCREMENT       |
| category_id    | INT          | FK → ASSISTANCE_CATEGORY |
| name           | VARCHAR(255) | NOT NULL                 |
| description    | TEXT         |                          |
| is_mandatory   | BOOLEAN      | NOT NULL, DEFAULT TRUE   |
| is_active      | BOOLEAN      | NOT NULL, DEFAULT TRUE   |

### APPLICATION

| Column                                | Type                                                                                                                                                                                                                                                                   | Constraints                                   |
| ------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------- |
| application_id                        | INT                                                                                                                                                                                                                                                                    | PK, AUTO_INCREMENT                            |
| category_id                           | INT                                                                                                                                                                                                                                                                    | FK → ASSISTANCE_CATEGORY                      |
| submitted_by                          | INT                                                                                                                                                                                                                                                                    | FK → USER, NULLABLE                           |
| reference_code                        | VARCHAR(50)                                                                                                                                                                                                                                                            | NOT NULL, UNIQUE                              |
| status                                | ENUM(submitted, under_review, resubmission_required, forwarded_to_mswd, pending_additional_docs, approved_by_mswd, coding, forwarded_to_mayor, approved_by_mayor, voucher_preparation, forwarded_to_accounting, forwarded_to_treasury, on_hold, cheque_ready, claimed) | NOT NULL, DEFAULT submitted                   |
| applicant_last_name                   | VARCHAR(100)                                                                                                                                                                                                                                                           | NOT NULL                                      |
| applicant_first_name                  | VARCHAR(100)                                                                                                                                                                                                                                                           | NOT NULL                                      |
| applicant_middle_name                 | VARCHAR(100)                                                                                                                                                                                                                                                           | NULLABLE                                      |
| applicant_sex                         | ENUM(male, female)                                                                                                                                                                                                                                                     | NOT NULL                                      |
| applicant_dob                         | DATE                                                                                                                                                                                                                                                                   | NOT NULL                                      |
| applicant_address                     | VARCHAR(500)                                                                                                                                                                                                                                                           | NOT NULL                                      |
| applicant_phone                       | VARCHAR(20)                                                                                                                                                                                                                                                            | NOT NULL                                      |
| applicant_relationship_to_beneficiary | VARCHAR(100)                                                                                                                                                                                                                                                           | NOT NULL                                      |
| beneficiary_last_name                 | VARCHAR(100)                                                                                                                                                                                                                                                           | NOT NULL                                      |
| beneficiary_first_name                | VARCHAR(100)                                                                                                                                                                                                                                                           | NOT NULL                                      |
| beneficiary_middle_name               | VARCHAR(100)                                                                                                                                                                                                                                                           | NULLABLE                                      |
| beneficiary_sex                       | ENUM(male, female)                                                                                                                                                                                                                                                     | NOT NULL                                      |
| beneficiary_dob                       | DATE                                                                                                                                                                                                                                                                   | NOT NULL                                      |
| beneficiary_address                   | VARCHAR(500)                                                                                                                                                                                                                                                           | NOT NULL                                      |
| submitted_at                          | DATETIME                                                                                                                                                                                                                                                               | NOT NULL, DEFAULT CURRENT_TIMESTAMP           |
| updated_at                            | DATETIME                                                                                                                                                                                                                                                               | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE |

### DOCUMENT

| Column         | Type                                                                   | Constraints                         |
| -------------- | ---------------------------------------------------------------------- | ----------------------------------- |
| document_id    | INT                                                                    | PK, AUTO_INCREMENT                  |
| application_id | INT                                                                    | FK → APPLICATION                    |
| requirement_id | INT                                                                    | FK → REQUIREMENT, NULLABLE          |
| uploaded_by    | INT                                                                    | FK → USER, NULLABLE                 |
| document_type  | ENUM(supporting_document, authorization_letter, allotment_slip, other) | NOT NULL                            |
| file_name      | VARCHAR(255)                                                           | NOT NULL                            |
| file_path      | VARCHAR(500)                                                           | NOT NULL                            |
| file_size      | INT                                                                    | NOT NULL                            |
| mime_type      | VARCHAR(100)                                                           | NOT NULL                            |
| uploaded_at    | DATETIME                                                               | NOT NULL, DEFAULT CURRENT_TIMESTAMP |

### APPLICATION_LOG

| Column         | Type         | Constraints                         |
| -------------- | ------------ | ----------------------------------- |
| log_id         | INT          | PK, AUTO_INCREMENT                  |
| application_id | INT          | FK → APPLICATION                    |
| performed_by   | INT          | FK → USER, NULLABLE                 |
| action         | VARCHAR(255) | NOT NULL                            |
| from_status    | VARCHAR(100) | NULLABLE                            |
| to_status      | VARCHAR(100) | NULLABLE                            |
| remarks        | TEXT         | NULLABLE                            |
| timestamp      | DATETIME     | NOT NULL, DEFAULT CURRENT_TIMESTAMP |

### APPLICATION_REVIEW

| Column           | Type                                                                                              | Constraints                         |
| ---------------- | ------------------------------------------------------------------------------------------------- | ----------------------------------- |
| review_id        | INT                                                                                               | PK, AUTO_INCREMENT                  |
| application_id   | INT                                                                                               | FK → APPLICATION                    |
| reviewed_by      | INT                                                                                               | FK → USER                           |
| stage            | ENUM(aics_validation, mswd_review, mayors_review, accounting_verification, treasury_verification) | NOT NULL                            |
| decision         | ENUM(approved, rejected, resubmission_required, adjustment_requested, on_hold)                    | NOT NULL                            |
| feedback_remarks | TEXT                                                                                              | NULLABLE                            |
| reviewed_at      | DATETIME                                                                                          | NOT NULL, DEFAULT CURRENT_TIMESTAMP |

### SOCIAL_CASE_STUDY

| Column         | Type         | Constraints                         |
| -------------- | ------------ | ----------------------------------- |
| case_study_id  | INT          | PK, AUTO_INCREMENT                  |
| application_id | INT          | FK → APPLICATION, UNIQUE            |
| conducted_by   | INT          | FK → USER                           |
| file_name      | VARCHAR(255) | NOT NULL                            |
| file_path      | VARCHAR(500) | NOT NULL                            |
| file_size      | INT          | NOT NULL                            |
| mime_type      | VARCHAR(100) | NOT NULL                            |
| conducted_at   | DATETIME     | NOT NULL, DEFAULT CURRENT_TIMESTAMP |

### CODE_REFERENCE

| Column      | Type                   | Constraints            |
| ----------- | ---------------------- | ---------------------- |
| code_ref_id | INT                    | PK, AUTO_INCREMENT     |
| code        | ENUM(A, B, C, D, E, F) | NOT NULL, UNIQUE       |
| amount      | DECIMAL(10,2)          | NOT NULL               |
| description | TEXT                   | NULLABLE               |
| is_active   | BOOLEAN                | NOT NULL, DEFAULT TRUE |

### ASSISTANCE_CODE

| Column             | Type                           | Constraints                                   |
| ------------------ | ------------------------------ | --------------------------------------------- |
| code_id            | INT                            | PK, AUTO_INCREMENT                            |
| application_id     | INT                            | FK → APPLICATION, UNIQUE                      |
| code_ref_id        | INT                            | FK → CODE_REFERENCE                           |
| assigned_by        | INT                            | FK → USER                                     |
| adjustment_remarks | TEXT                           | NULLABLE                                      |
| adjustment_status  | ENUM(none, requested, revised) | NOT NULL, DEFAULT none                        |
| assigned_at        | DATETIME                       | NOT NULL, DEFAULT CURRENT_TIMESTAMP           |
| updated_at         | DATETIME                       | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE |

### VOUCHER

| Column             | Type                                                                                                         | Constraints                                   |
| ------------------ | ------------------------------------------------------------------------------------------------------------ | --------------------------------------------- |
| voucher_id         | INT                                                                                                          | PK, AUTO_INCREMENT                            |
| application_id     | INT                                                                                                          | FK → APPLICATION, UNIQUE                      |
| code_id            | INT                                                                                                          | FK → ASSISTANCE_CODE, UNIQUE                  |
| prepared_by        | INT                                                                                                          | FK → USER                                     |
| amount             | DECIMAL(10,2)                                                                                                | NOT NULL                                      |
| status             | ENUM(prepared, forwarded_to_accounting, adjustment_requested, approved_by_accounting, forwarded_to_treasury) | NOT NULL, DEFAULT prepared                    |
| adjustment_remarks | TEXT                                                                                                         | NULLABLE                                      |
| adjustment_status  | ENUM(none, requested, revised)                                                                               | NOT NULL, DEFAULT none                        |
| prepared_at        | DATETIME                                                                                                     | NOT NULL, DEFAULT CURRENT_TIMESTAMP           |
| updated_at         | DATETIME                                                                                                     | NOT NULL, DEFAULT CURRENT_TIMESTAMP ON UPDATE |

### CHEQUE

| Column              | Type                    | Constraints                         |
| ------------------- | ----------------------- | ----------------------------------- |
| cheque_id           | INT                     | PK, AUTO_INCREMENT                  |
| voucher_id          | INT                     | FK → VOUCHER, UNIQUE                |
| prepared_by         | INT                     | FK → USER                           |
| amount              | DECIMAL(10,2)           | NOT NULL                            |
| status              | ENUM(prepared, claimed) | NOT NULL, DEFAULT prepared          |
| allotment_slip_path | VARCHAR(500)            | NULLABLE                            |
| prepared_at         | DATETIME                | NOT NULL, DEFAULT CURRENT_TIMESTAMP |
| claimed_at          | DATETIME                | NULLABLE                            |

### NOTIFICATION

| Column          | Type                        | Constraints                         |
| --------------- | --------------------------- | ----------------------------------- |
| notification_id | INT                         | PK, AUTO_INCREMENT                  |
| application_id  | INT                         | FK → APPLICATION                    |
| recipient_phone | VARCHAR(20)                 | NOT NULL                            |
| message         | TEXT                        | NOT NULL                            |
| trigger_event   | VARCHAR(100)                | NOT NULL                            |
| delivery_status | ENUM(pending, sent, failed) | NOT NULL, DEFAULT pending           |
| sent_at         | DATETIME                    | NOT NULL, DEFAULT CURRENT_TIMESTAMP |

### AUDIT_LOG

| Column      | Type                                                   | Constraints                         |
| ----------- | ------------------------------------------------------ | ----------------------------------- |
| log_id      | INT                                                    | PK, AUTO_INCREMENT                  |
| user_id     | INT                                                    | FK → USER                           |
| module      | VARCHAR(100)                                           | NOT NULL                            |
| action      | ENUM(create, update, delete, login, logout, configure) | NOT NULL                            |
| description | TEXT                                                   | NULLABLE                            |
| ip_address  | VARCHAR(45)                                            | NULLABLE                            |
| timestamp   | DATETIME                                               | NOT NULL, DEFAULT CURRENT_TIMESTAMP |

## Seeded Data

### ASSISTANCE_CATEGORY

| category_id | name                |
| ----------- | ------------------- |
| 1           | Medical Assistance  |
| 2           | Hospital Assistance |
| 3           | Burial Assistance   |

### REQUIREMENT

| category_id | name                                | is_mandatory |
| ----------- | ----------------------------------- | ------------ |
| 1           | Medical Certificate                 | TRUE         |
| 1           | Prescription                        | TRUE         |
| 1           | Applicant's Government ID           | TRUE         |
| 1           | Beneficiary's Government ID         | TRUE         |
| 1           | Applicant's Cedula                  | TRUE         |
| 1           | Barangay Indigency                  | TRUE         |
| 1           | Authorization Letter                | FALSE        |
| 2           | Hospital Bill                       | TRUE         |
| 2           | Prescription                        | TRUE         |
| 2           | Medical Certificate/Abstract        | TRUE         |
| 2           | Applicant's Government ID           | TRUE         |
| 2           | Beneficiary's Government ID         | TRUE         |
| 2           | Applicant's Cedula                  | TRUE         |
| 2           | Barangay Indigency                  | TRUE         |
| 2           | Authorization Letter                | FALSE        |
| 3           | Certified Copy of Birth Certificate | TRUE         |
| 3           | Applicant's Government ID           | TRUE         |
| 3           | Applicant's Cedula                  | TRUE         |
| 3           | Beneficiary's Barangay Residency    | TRUE         |
| 3           | Barangay Indigency                  | TRUE         |
| 3           | Authorization Letter                | FALSE        |

## Change Log

| Date       | Change                                                                                   | Updated By |
| ---------- | ---------------------------------------------------------------------------------------- | ---------- |
|            | Initial schema created                                                                   |            |
| 2026-03-30 | Expanded APPLICATION.status enum with canonical workflow statuses aligned to workflow.md | Copilot    |

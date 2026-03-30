# Progress Tracker

## Current Phase
Phase 1: Project Setup

## Completed Tasks
- [ ] Initialize project repository
- [ ] Set up version control (Git)
- [ ] Choose and set up tech stack
- [ ] Set up development environment
- [ ] Set up database (MySQL)
- [x] Run CREATE TABLE queries to initialize schema
- [ ] Set up environment variables file (.env)
- [ ] Set up project folder structure
- [x] Connect Project to Database

## In Progress
- [ ] None currently

## Upcoming Phases

### Phase 1: Project Setup
- [ ] Initialize project repository
- [ ] Set up version control (Git)
- [ ] Choose and set up tech stack
- [ ] Set up development environment
- [ ] Set up database (MySQL)
- [x] Run CREATE TABLE queries to initialize schema
- [ ] Set up environment variables file (.env)
- [ ] Set up project folder structure
- [x] Connect Project to Database

### Phase 2: Authentication and Access Management
- [ ] Implement user registration (admin creates accounts only)
- [ ] Implement login with email and password
- [ ] Implement password hashing (bcrypt or Argon2)
- [ ] Implement JWT or session-based authentication
- [ ] Implement MFA / OTP verification
- [ ] Implement role-based access control (RBAC) middleware
- [ ] Implement password reset flow
- [ ] Implement login attempt tracking
- [ ] Implement logout
- [ ] Implement route guards per role

### Phase 3: System Administration
- [ ] Build user management page
- [ ] Build role and permission assignment
- [ ] Build password reset for users
- [ ] Build SMS configuration page
- [ ] Build audit log viewer
- [ ] Build system activity monitor

### Phase 4: Assistance Category and Requirements Management
- [ ] Build assistance category list page (CRUD)
- [ ] Build requirements list page per category (CRUD)
- [ ] Implement is_active toggle for categories and requirements
- [ ] Implement dynamic requirements checklist based on selected category

### Phase 5: Application Submission Management
- [ ] Build assistance type selection page
- [ ] Build dynamic requirements display
- [ ] Build application form with applicant and beneficiary fields
- [ ] Implement authorization letter logic in code
- [ ] Build document upload functionality per requirement
- [ ] Implement reference code generation on submission
- [ ] Build application tracking page
- [ ] Build application status page with ApplicationLog timeline
- [ ] Implement staff-assisted submission
- [ ] Build resubmission flow

### Phase 6: Application Review and Validation Management
- [ ] Build application queue for AICS Staff
- [ ] Build application detail view with all documents
- [ ] Implement document validation workflow
- [ ] Implement approval / rejection / resubmission request actions
- [ ] Build feedback remarks form
- [ ] Implement ApplicationReview record creation
- [ ] Implement ApplicationLog entry creation
- [ ] Build forward to MSWD Officer functionality
- [ ] Implement auto-SMS notification trigger

### Phase 7: Social Case Study and Assistance Code Management
- [ ] Build application queue for MSWD Officer
- [ ] Build application detail view for MSWD Officer
- [ ] Implement further review and interview workflow
- [ ] Build additional document request functionality
- [ ] Build social case study file upload
- [ ] Implement approval and return to AICS Staff workflow
- [ ] Build assistance code assignment page
- [ ] Implement CodeReference lookup
- [ ] Build code assignment form
- [ ] Build forward to Mayor's Office functionality
- [ ] Implement auto-SMS notification trigger

### Phase 8: Mayor's Office Review
- [ ] Build application queue for Mayor's Office Staff
- [ ] Build application detail view
- [ ] Implement approval workflow
- [ ] Implement code adjustment request workflow
- [ ] Build ApplicationReview record creation
- [ ] Implement return to MSWD on approval
- [ ] Implement auto-SMS notification trigger

### Phase 9: Financial Assistance Management
- [ ] Build voucher preparation page for MSWD Officer
- [ ] Implement voucher creation
- [ ] Build forward to Accounting Office functionality
- [ ] Build voucher review page for Accountant
- [ ] Implement financial decision workflow
- [ ] Implement voucher adjustment request loop
- [ ] Build forward to Treasurer's Office functionality
- [ ] Build voucher review page for Treasurer
- [ ] Implement fund availability check
- [ ] Implement on hold functionality
- [ ] Build cheque preparation page
- [ ] Build allotment slip upload functionality
- [ ] Implement auto-SMS notification to applicant
- [ ] Build cheque claim confirmation
- [ ] Implement ApplicationLog entries

### Phase 10: Notification Management
- [ ] Integrate SMS API provider
- [ ] Implement auto-SMS triggers at all key stages
- [ ] Implement delivery status tracking
- [ ] Build notification log viewer

### Phase 11: Reporting and Monitoring
- [ ] Build application summary report
- [ ] Build financial report
- [ ] Build SMS notification log report
- [ ] Build audit log report
- [ ] Implement print functionality
- [ ] Build main dashboard with key metrics per role

### Phase 12: Testing
- [ ] Unit test all business logic functions
- [ ] Test all role-based access controls
- [ ] Test application workflow end-to-end
- [ ] Test all SMS notification triggers
- [ ] Test document upload and retrieval
- [ ] Test reference code tracking
- [ ] Test all adjustment request loops
- [ ] Test resubmission flow
- [ ] Test on hold and fund availability flow
- [ ] Performance test
- [ ] Security test

### Phase 13: Deployment
- [ ] Set up production server
- [ ] Set up production database
- [ ] Configure environment variables
- [ ] Set up SSL certificate
- [ ] Set up database backups
- [ ] Deploy application
- [ ] Smoke test all features
- [ ] Train staff on system usage

## Change Log
| Date | Change | Updated By |
|---|---|---|
| | Initial setup | |
| 2026-03-30 | Documentation alignment completed: canonical application statuses, workflow transitions, and status-to-SMS trigger mapping standardized before development | Copilot |
| 2026-03-30 | Connected Laravel to MySQL database `aics_system` and added serve-time terminal DB connection status message | Copilot |
| 2026-03-30 | Ran Laravel migrations on MySQL `aics_system`; created missing `sessions` table and resolved SQLSTATE[42S02] sessions error | Copilot |
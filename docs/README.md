# AICS Digital Application and Notification System

## Overview
A web-based system for the Assistance to Individuals in Crisis Situation (AICS) program of a local government unit in the Philippines. The system digitizes the application, review, approval, and notification process for financial assistance.

## Purpose
- Allow applicants to submit assistance applications digitally or through staff-assisted walk-in submission
- Track applications through a multi-stage approval workflow
- Automate SMS notifications at every key stage
- Generate reports for management oversight

## Actors
| Actor | Role |
|---|---|
| Applicant | Submits and tracks their own application |
| AICS Staff | Reviews, validates, codes, and manages applications |
| MSWD Officer | Conducts social case study, prepares voucher |
| Mayor's Office Staff | Reviews and approves assistance code |
| Accountant | Verifies voucher calculations |
| Treasurer | Checks fund availability and prepares cheque |
| System Administrator | Manages user accounts, roles, and system settings |

## Tech Stack
1. Backend / Core
Framework: Laravel 
Database: MySQL 
👉 Laravel will now handle:
File management 
Automated SMS triggers (important update)

2. Authentication
Supabase Auth

2️. SMS System (Expanded Role)
Provider: PhilSMS 
🔐 Uses of SMS in the system:
1. OTP Verification
Login / registration verification 
2. Automated Notifications
Examples:
📩 “Your document has been approved” 
📩 “Your application is under review” 
📩 “Your request has been completed” 
📩 “Please upload missing documents”

3️. Frontend / Scanning
Templating: Blade
Dynamic components: Livewire 3
Client-side interactivity: Alpine.js
Styling: Tailwind CSS
Asset bundler: Vite (built into Laravel)
SPA-like navigation: wire:navigate (built into Livewire 3)
Camera input + auto-crop: 
opencv.js (auto detect edges) 
ocropper.js fallback

4️. Storage
Cloud Storage: Supabase Storage 
Laravel uploads files → Supabase 
MySQL stores file metadata

5️. Hosting
Render (Laravel app) 
MySQL (Render or external) 
Supabase (storage only)

## Quick Links
- [Progress Tracker](./progress.md)
- [Database Schema](./schema.md)
- [Workflow](./workflow.md)
- [RBAC](./rbac.md)
- [Entities](./entities.md)
- [Decisions](./decisions.md)
- [Notifications](./notifications.md)
- [API](./api.md)
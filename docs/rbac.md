# Role-Based Access Control

## Roles
| Role | Description |
|---|---|
| aics_staff | Reviews, validates, codes, and manages applications |
| mswd_officer | Conducts social case study, prepares voucher |
| mayors_office | Reviews and approves assistance code |
| accountant | Verifies voucher calculations |
| treasurer | Checks fund availability and prepares cheque |
| admin | Manages user accounts, roles, and system settings |

## Access Matrix

### Application
| Action | aics_staff | mswd_officer | mayors_office | accountant | treasurer | admin |
|---|---|---|---|---|---|---|
| Submit (staff-assisted) | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| View all applications | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Review and validate | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Assign assistance code | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Forward to MSWD | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Conduct case study | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Forward to Mayor | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Approve/request adjustment | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Prepare voucher | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Verify voucher | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ |
| Prepare cheque | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |

### User Management
| Action | aics_staff | mswd_officer | mayors_office | accountant | treasurer | admin |
|---|---|---|---|---|---|---|
| Create user | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Update user | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Deactivate user | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Reset password | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |

### Reports
| Action | aics_staff | mswd_officer | mayors_office | accountant | treasurer | admin |
|---|---|---|---|---|---|---|
| View application reports | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| View financial reports | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ |
| View audit logs | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| View SMS logs | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ |

### System Settings
| Action | aics_staff | mswd_officer | mayors_office | accountant | treasurer | admin |
|---|---|---|---|---|---|---|
| Configure SMS templates | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Manage assistance categories | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Manage requirements | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Manage code reference | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |

## Rules
- All routes must be protected by authentication middleware
- All routes must be protected by role middleware
- A user can only access pages and actions permitted by their role
- Admin cannot submit or process applications — admin is purely for system management
- Applicants access the system through a public-facing portal — they do not have a USER record
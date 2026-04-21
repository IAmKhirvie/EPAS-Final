# Product Requirements Document (PRD)
# EPAS-E Learning Management System

**Version:** 2.0
**Last Updated:** February 2026
**Document Owner:** EPAS Development Team
**Status:** Active Development

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Product Overview](#2-product-overview)
3. [User Roles & Permissions](#3-user-roles--permissions)
4. [Core Features](#4-core-features)
5. [Content Management System](#5-content-management-system)
6. [Assessment & Grading](#6-assessment--grading)
7. [Gamification System](#7-gamification-system)
8. [Communication & Collaboration](#8-communication--collaboration)
9. [Analytics & Reporting](#9-analytics--reporting)
10. [Security & Compliance](#10-security--compliance)
11. [Technical Architecture](#11-technical-architecture)
12. [Mobile & Accessibility](#12-mobile--accessibility)
13. [Integration Capabilities](#13-integration-capabilities)
14. [Future Roadmap](#14-future-roadmap)

---

## 1. Executive Summary

### 1.1 Purpose
EPAS-E (Electronic Products Assembly and Servicing - E-Learning) is a comprehensive Learning Management System designed specifically for Technical-Vocational Education and Training (TVET) programs. It facilitates competency-based learning for electronics and technical courses aligned with TESDA (Technical Education and Skills Development Authority) standards.

### 1.2 Vision
To provide an intuitive, engaging, and effective digital learning platform that bridges theoretical knowledge with practical skills assessment, enabling students to achieve industry-recognized certifications.

### 1.3 Target Users
- **Students:** TVET learners pursuing NC II certifications
- **Instructors:** Technical educators and trainers
- **Administrators:** School/institution management staff

### 1.4 Key Value Propositions
- Competency-based curriculum aligned with industry standards
- Interactive self-assessments with immediate feedback
- Hands-on task and job sheet workflows
- Progress tracking and certification management
- Gamified learning experience for increased engagement

---

## 2. Product Overview

### 2.1 Product Description
EPAS-E LMS is a web-based learning platform that delivers structured course content through a modular approach. Each module contains Information Sheets, Self-Checks, Task Sheets, Job Sheets, and Homework assignments that guide students through competency acquisition.

### 2.2 Learning Framework
```
Course
└── Modules
    └── Information Sheets (Learning Content)
        ├── Self-Checks (Knowledge Assessment)
        ├── Task Sheets (Guided Practice)
        ├── Job Sheets (Performance Tasks)
        ├── Checklists (Competency Verification)
        └── Homework (Extended Learning)
```

### 2.3 Key Metrics for Success
| Metric | Target |
|--------|--------|
| Student Completion Rate | > 85% |
| Assessment Pass Rate | > 70% |
| User Satisfaction Score | > 4.5/5 |
| Platform Uptime | 99.9% |
| Mobile Usage | > 40% |

---

## 3. User Roles & Permissions

### 3.1 Role Hierarchy

#### 3.1.1 Administrator
**Description:** Full system access for institutional management

**Permissions:**
- [ ] Manage all users (create, edit, delete, approve)
- [ ] Configure system settings
- [ ] Create and manage courses
- [ ] Assign instructors to courses
- [ ] View all analytics and reports
- [ ] Manage departments and sections
- [ ] Access audit logs
- [ ] Configure security settings
- [ ] Bulk operations on users
- [ ] Export data and generate reports
- [ ] Manage announcements (global)
- [ ] Configure gamification rules

#### 3.1.2 Instructor
**Description:** Course management and student evaluation

**Permissions:**
- [ ] Manage assigned courses and modules
- [ ] Create and edit learning content
- [ ] Create assessments (Self-Checks, Task Sheets, Job Sheets)
- [ ] Grade student submissions
- [ ] View student progress (assigned sections only)
- [ ] Post announcements (course-level)
- [ ] Manage students in advisory section
- [ ] Generate class reports
- [ ] Communicate with students
- [ ] Access course analytics

#### 3.1.3 Student
**Description:** Learning participant

**Permissions:**
- [ ] View enrolled courses and modules
- [ ] Access learning materials
- [ ] Take self-check assessments
- [ ] Submit task and job sheets
- [ ] View personal grades and progress
- [ ] Participate in forums
- [ ] Receive and view announcements
- [ ] Update personal profile
- [ ] Earn badges and points
- [ ] Download certificates

### 3.2 Permission Matrix

| Feature | Admin | Instructor | Student |
|---------|:-----:|:----------:|:-------:|
| User Management | Full | Section Only | Self Only |
| Course Creation | Yes | Yes | No |
| Content Creation | Yes | Yes | No |
| Grade Submissions | Yes | Yes | No |
| View All Analytics | Yes | No | No |
| View Course Analytics | Yes | Yes | No |
| Take Assessments | No | No | Yes |
| Submit Work | No | No | Yes |
| System Settings | Yes | No | No |
| Audit Logs | Yes | No | No |

---

## 4. Core Features

### 4.1 User Management

#### 4.1.1 Registration & Onboarding
- **Self-Registration:** Students can register with email verification
- **Admin Registration:** Admins can create user accounts directly
- **Bulk Import:** CSV/Excel import for batch user creation
- **Approval Workflow:** Admin approval required for account activation
- **Profile Completion:** Guided profile setup on first login

#### 4.1.2 User Profile Management
- Personal information (name, contact, bio)
- Profile picture upload
- Password management
- Email change with re-verification
- Notification preferences
- Two-factor authentication (optional)

#### 4.1.3 User Administration
- Search and filter users by role, status, section
- Activate/deactivate accounts
- Reset passwords
- Assign roles and sections
- View user activity history
- Bulk operations (activate, deactivate, delete)

### 4.2 Course Management

#### 4.2.1 Course Structure
```
Course
├── Course Code (unique identifier)
├── Course Name
├── Description
├── Sector (e.g., Electronics)
├── Qualification Title
├── Duration
├── Prerequisites
├── Learning Outcomes
├── Assigned Instructors
└── Enrollment Settings
```

#### 4.2.2 Course Operations
- Create, edit, duplicate, archive courses
- Assign/unassign instructors
- Set enrollment limits
- Configure access dates
- Order and organize modules
- Import/export course content

### 4.3 Module Management

#### 4.3.1 Module Structure
```
Module
├── Module Number
├── Module Name
├── Unit of Competency
├── How to Use CBLM
├── Introduction
├── Learning Outcomes
├── Table of Contents
├── Order/Sequence
├── Active/Inactive Status
└── Information Sheets[]
```

#### 4.3.2 Module Operations
- Create and edit modules
- Reorder modules within course
- Set prerequisites between modules
- Track completion requirements
- Clone modules across courses

### 4.4 Dashboard

#### 4.4.1 Student Dashboard
- Course progress overview
- Upcoming deadlines
- Recent activity feed
- Pending assignments
- Achievement badges
- Points and leaderboard position
- Quick access to enrolled courses
- Announcements

#### 4.4.2 Instructor Dashboard
- Class overview statistics
- Pending submissions to grade
- Student progress summary
- Recent student activity
- Course completion rates
- Quick actions (grade, announce)

#### 4.4.3 Admin Dashboard
- System-wide statistics
- User registration trends
- Active users count
- Course enrollment metrics
- Pending approvals
- System health indicators
- Quick links to common tasks

---

## 5. Content Management System

### 5.1 Information Sheets

#### 5.1.1 Structure
```
Information Sheet
├── Sheet Number (e.g., 1.1, 1.2)
├── Title
├── Content (Rich Text)
│   ├── Text formatting
│   ├── Images
│   ├── Tables
│   ├── Lists
│   ├── Code blocks
│   └── Embedded media
├── Order/Sequence
├── Attachments[]
└── Related Resources[]
```

#### 5.1.2 Content Editor Features
- WYSIWYG rich text editor
- Image upload and embedding
- Video embedding (YouTube, Vimeo)
- File attachments (PDF, DOC, etc.)
- Mathematical equations (LaTeX)
- Code syntax highlighting
- Table creation and editing
- Auto-save functionality

### 5.2 Self-Checks (Assessments)

#### 5.2.1 Question Types
| Type | Description | Auto-Graded |
|------|-------------|:-----------:|
| Multiple Choice | Single correct answer | Yes |
| Multiple Select | Multiple correct answers | Yes |
| True/False | Binary choice | Yes |
| Identification | Short text answer | Yes |
| Enumeration | List items | Partial |
| Matching | Pair items | Yes |
| Essay | Long-form response | No |
| Numeric | Number with tolerance | Yes |
| Image Identification | Label image parts | Partial |

#### 5.2.2 Self-Check Configuration
- Time limit (optional)
- Passing score threshold
- Number of attempts allowed
- Shuffle questions option
- Shuffle answer options
- Show correct answers after submission
- Immediate feedback per question
- Point values per question

#### 5.2.3 Self-Check Workflow
1. Student accesses self-check
2. Timer starts (if configured)
3. Student answers questions
4. Submit for grading
5. Automatic scoring for supported types
6. Display results and feedback
7. Record attempt in gradebook

### 5.3 Task Sheets

#### 5.3.1 Structure
```
Task Sheet
├── Task Number
├── Title
├── Description
├── Objectives[]
├── Materials Needed[]
├── Safety Precautions[]
├── Instructions (step-by-step)
├── Items to Check[]
│   ├── Part Name
│   ├── Description
│   ├── Expected Finding
│   └── Acceptable Range
├── Estimated Duration
├── Difficulty Level
└── Reference Images[]
```

#### 5.3.2 Task Sheet Submission
- Student observations
- Findings per item
- Time taken
- Challenges encountered
- Photo/file attachments
- Instructor evaluation

### 5.4 Job Sheets

#### 5.4.1 Structure
```
Job Sheet
├── Job Number
├── Title
├── Description
├── Objectives[]
├── Tools Required[]
├── Safety Requirements[]
├── Reference Materials[]
├── Procedures (Rich Text)
├── Performance Criteria
├── Steps[]
│   ├── Step Number
│   ├── Instruction
│   ├── Expected Outcome
│   ├── Warnings[]
│   ├── Tips[]
│   └── Image
├── Estimated Duration
└── Difficulty Level
```

#### 5.4.2 Job Sheet Submission
- Completed steps checklist
- Observations per step
- Overall observations
- Challenges faced
- Solutions applied
- Time taken
- Instructor evaluation with notes

### 5.5 Checklists

#### 5.5.1 Structure
```
Checklist
├── Checklist Number
├── Title
├── Description
├── Items[]
│   ├── Item Description
│   ├── Max Rating (1-5)
│   └── Order
├── Completed By
├── Completed At
├── Evaluated By
└── Evaluator Notes
```

#### 5.5.2 Rating System
- 1: Poor
- 2: Below Average
- 3: Average
- 4: Good
- 5: Excellent

### 5.6 Homework

#### 5.6.1 Structure
```
Homework
├── Homework Number
├── Title
├── Description
├── Instructions
├── Requirements[]
├── Submission Guidelines[]
├── Reference Images[]
├── Due Date
├── Max Points
├── Allow Late Submission
└── Late Penalty (% per day)
```

#### 5.6.2 Homework Submission
- File upload (required)
- Description/notes
- Work hours logged
- Late submission handling
- Instructor scoring and feedback

---

## 6. Assessment & Grading

### 6.1 Gradebook

#### 6.1.1 Grade Components
| Component | Weight | Type |
|-----------|:------:|------|
| Self-Checks | 30% | Auto-graded |
| Task Sheets | 20% | Instructor-graded |
| Job Sheets | 25% | Instructor-graded |
| Homework | 15% | Instructor-graded |
| Checklists | 10% | Instructor-rated |

#### 6.1.2 Grading Scale
| Grade | Percentage | Description |
|:-----:|:----------:|-------------|
| A | 90-100% | Excellent |
| B | 80-89% | Very Good |
| C | 70-79% | Good (Passing) |
| D | 60-69% | Needs Improvement |
| F | Below 60% | Failed |

#### 6.1.3 Gradebook Features
- View by student or by assignment
- Filter by date range, module, type
- Export to CSV/Excel
- Grade history tracking
- Bulk grading interface
- Grade override with reason
- Comments per grade

### 6.2 Progress Tracking

#### 6.2.1 Tracked Metrics
- Content viewed (Information Sheets)
- Self-checks completed and scores
- Task/Job sheets submitted
- Time spent per module
- Last access date
- Completion percentage

#### 6.2.2 Completion Criteria
- All Information Sheets viewed
- All Self-Checks passed (≥70%)
- All required submissions graded
- Minimum time requirement met (if set)

### 6.3 Certificates

#### 6.3.1 Certificate Types
- Module Completion Certificate
- Course Completion Certificate
- Achievement Certificates (badges)

#### 6.3.2 Certificate Features
- Auto-generation on completion
- Unique certificate ID
- QR code for verification
- PDF download
- Share to social media
- Verification portal

---

## 7. Gamification System

### 7.1 Points System

#### 7.1.1 Point Awards
| Activity | Points |
|----------|:------:|
| Topic Complete | 10 |
| Self-Check Pass | 25 |
| Homework Submit | 15 |
| Perfect Score | 50 |
| Daily Login | 5 |
| Module Complete | 100 |
| Course Complete | 500 |

#### 7.1.2 Point Features
- Running total display
- Points history
- Leaderboard (optional)
- Points decay prevention

### 7.2 Badges & Achievements

#### 7.2.1 Badge Categories
- **Milestone Badges:** First login, First course, etc.
- **Progress Badges:** 25%, 50%, 75%, 100% complete
- **Performance Badges:** Perfect scores, streaks
- **Engagement Badges:** Daily logins, participation
- **Special Badges:** Time-limited events

#### 7.2.2 Badge Display
- Profile showcase
- Achievement wall
- Share functionality
- Rarity indicators

### 7.3 Leaderboards

#### 7.3.1 Leaderboard Types
- Global (all students)
- Course-specific
- Section-specific
- Weekly/Monthly/All-time

#### 7.3.2 Privacy Settings
- Opt-in/opt-out participation
- Anonymous mode option
- Display name customization

---

## 8. Communication & Collaboration

### 8.1 Announcements

#### 8.1.1 Announcement Types
- Global (all users)
- Course-specific
- Section-specific
- Role-specific

#### 8.1.2 Announcement Features
- Rich text content
- File attachments
- Schedule publishing
- Priority levels (normal, important, urgent)
- Read receipts
- Pin to top option
- Expiration date

### 8.2 Forums (Discussion Boards)

#### 8.2.1 Forum Structure
```
Forum
├── Course Forums
│   └── Topic Threads
│       └── Posts/Replies
├── General Discussion
└── Q&A Section
```

#### 8.2.2 Forum Features
- Create threads
- Reply and nested replies
- Rich text formatting
- File attachments
- Like/upvote posts
- Mark as answer (Q&A)
- Subscribe to threads
- Moderation tools
- Search within forums

### 8.3 Notifications

#### 8.3.1 Notification Channels
- In-app notifications
- Email notifications
- Push notifications (PWA)

#### 8.3.2 Notification Types
- New announcements
- Grade posted
- Submission feedback
- Deadline reminders
- Forum replies
- Achievement unlocked
- Course updates

#### 8.3.3 Notification Preferences
- Per-channel enable/disable
- Per-type configuration
- Quiet hours setting
- Digest mode (daily/weekly)

### 8.4 Messaging (Future)

#### 8.4.1 Messaging Features
- Direct messages (student ↔ instructor)
- Group messages
- File sharing
- Message history
- Read receipts

---

## 9. Analytics & Reporting

### 9.1 Student Analytics

#### 9.1.1 Individual Metrics
- Overall progress percentage
- Time spent learning
- Assessment scores trend
- Strengths and weaknesses
- Completion predictions

#### 9.1.2 Comparative Analytics
- Performance vs. class average
- Rank within section
- Improvement over time

### 9.2 Instructor Analytics

#### 9.2.1 Class Metrics
- Class average scores
- Completion rates
- At-risk students identification
- Most challenging content
- Engagement levels

#### 9.2.2 Content Effectiveness
- Question difficulty analysis
- Time spent per content
- Drop-off points
- Content ratings

### 9.3 Admin Analytics

#### 9.3.1 System Metrics
- Active users (DAU, WAU, MAU)
- Registration trends
- Course popularity
- Platform usage patterns
- Storage utilization

#### 9.3.2 Institutional Reports
- Enrollment statistics
- Graduation rates
- Certification metrics
- Year-over-year comparisons

### 9.4 Report Generation

#### 9.4.1 Report Types
- Student Progress Report
- Class Performance Report
- Course Analytics Report
- Audit Report
- Custom Reports

#### 9.4.2 Export Formats
- PDF
- Excel/CSV
- Print-friendly HTML

---

## 10. Security & Compliance

### 10.1 Authentication

#### 10.1.1 Login Security
- Email/password authentication
- Strong password requirements
  - Minimum 8 characters
  - Uppercase and lowercase letters
  - Numbers and special characters
- Account lockout after failed attempts
- Session management
- Remember me functionality

#### 10.1.2 Two-Factor Authentication
- TOTP (Time-based One-Time Password)
- Backup codes
- Recovery options

#### 10.1.3 Password Management
- Secure password reset via email
- Password change with current password verification
- Password history (prevent reuse)
- Email change cooldown period

### 10.2 Authorization

#### 10.2.1 Access Control
- Role-based access control (RBAC)
- Section-based data isolation
- Resource-level permissions
- API authentication (future)

### 10.3 Data Protection

#### 10.3.1 Data Security
- HTTPS enforcement
- Password hashing (bcrypt)
- CSRF protection
- XSS prevention
- SQL injection prevention
- Input validation and sanitization

#### 10.3.2 Security Headers
- Content Security Policy (CSP)
- X-Frame-Options
- X-Content-Type-Options
- Strict-Transport-Security (HSTS)
- Referrer-Policy
- Permissions-Policy

### 10.4 Audit & Compliance

#### 10.4.1 Audit Logging
- User authentication events
- Data modifications (create, update, delete)
- Admin actions
- Failed access attempts
- Sensitive data access

#### 10.4.2 Audit Log Contents
- Timestamp
- User ID and role
- Action type
- Affected resource
- Old/new values
- IP address
- User agent

#### 10.4.3 Compliance Features
- Data export for users
- Account deletion capability
- Consent management
- Privacy policy enforcement

### 10.5 Rate Limiting

| Action | Limit |
|--------|-------|
| Login attempts | 5 per 15 minutes |
| Registration | 3 per minute |
| Password reset | 5 per minute |
| API requests | 60 per minute |

---

## 11. Technical Architecture

### 11.1 Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Blade Templates, Bootstrap 5.3, Alpine.js |
| Database | MySQL 8.0 / MariaDB 10.6 |
| Caching | File/Redis |
| Queue | Database/Redis |
| Search | Laravel Scout (optional) |
| Storage | Local / S3-compatible |

### 11.2 System Requirements

#### 11.2.1 Server Requirements
- PHP 8.2 or higher
- MySQL 8.0+ or MariaDB 10.6+
- Composer 2.x
- Node.js 18+ (for asset compilation)
- 2GB RAM minimum (4GB recommended)
- 20GB storage minimum

#### 11.2.2 Browser Support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

### 11.3 Database Schema Overview

#### 11.3.1 Core Tables
- users
- departments
- courses
- modules
- information_sheets
- self_checks, self_check_questions, self_check_submissions
- task_sheets, task_sheet_submissions
- job_sheets, job_sheet_submissions
- homeworks, homework_submissions
- checklists, checklist_items

#### 11.3.2 Supporting Tables
- user_progress
- announcements
- forum_threads, forum_posts
- audit_logs
- gamification_points
- certificates
- settings

### 11.4 Performance Considerations

- Database query optimization with eager loading
- Caching for expensive queries (5-10 min TTL)
- Image optimization and lazy loading
- Asset minification and bundling
- CDN for static assets (production)
- Database indexing on foreign keys and frequent queries

---

## 12. Mobile & Accessibility

### 12.1 Responsive Design

#### 12.1.1 Breakpoints
| Device | Width |
|--------|-------|
| Mobile | < 768px |
| Tablet | 768px - 1024px |
| Desktop | > 1024px |

#### 12.1.2 Mobile Optimizations
- Touch-friendly buttons (min 44px)
- Collapsible navigation
- Optimized tables (horizontal scroll or column hiding)
- Bottom navigation bar
- Swipe gestures
- Pull-to-refresh

### 12.2 Progressive Web App (PWA)

#### 12.2.1 PWA Features
- Installable on home screen
- Offline page support
- Service worker caching
- Push notifications
- App-like experience

#### 12.2.2 Manifest Configuration
- App name and icons
- Theme colors
- Display mode (standalone)
- Start URL
- Shortcuts

### 12.3 Accessibility (WCAG 2.1)

#### 12.3.1 Accessibility Features
- Keyboard navigation
- Screen reader compatibility
- ARIA labels and roles
- Color contrast compliance
- Focus indicators
- Skip navigation links
- Alt text for images
- Form labels and error messages

---

## 13. Integration Capabilities

### 13.1 Current Integrations

| Integration | Purpose | Status |
|-------------|---------|--------|
| SMTP (Gmail) | Email delivery | Active |
| UI Avatars | Default profile images | Active |
| CDN (jsDelivr) | Frontend libraries | Active |

### 13.2 Planned Integrations

#### 13.2.1 Authentication
- Google OAuth
- Microsoft OAuth
- LDAP/Active Directory

#### 13.2.2 Communication
- SMS notifications (Twilio)
- Slack notifications
- Microsoft Teams

#### 13.2.3 Storage
- AWS S3
- Google Cloud Storage
- Azure Blob Storage

#### 13.2.4 Analytics
- Google Analytics
- Custom analytics dashboard

#### 13.2.5 Standards
- LTI (Learning Tools Interoperability)
- xAPI (Experience API)
- SCORM (future consideration)

---

## 14. Future Roadmap

### 14.1 Phase 1: Foundation (Current)
- [x] User management with roles
- [x] Course and module structure
- [x] Content types (Info Sheets, Self-Checks, etc.)
- [x] Basic grading system
- [x] Gamification (points, badges)
- [x] Responsive design
- [x] Security hardening
- [x] Email verification

### 14.2 Phase 2: Enhancement (Q2 2026)
- [ ] Advanced analytics dashboard
- [ ] Forum/discussion boards
- [ ] Direct messaging
- [ ] Video content hosting
- [ ] Certificate generation with QR verification
- [ ] Bulk content import/export
- [ ] Advanced question types (drag-drop, hotspot)

### 14.3 Phase 3: Scale (Q3 2026)
- [ ] Multi-institution support
- [ ] API for third-party integrations
- [ ] LTI provider/consumer
- [ ] Advanced reporting with custom queries
- [ ] Automated proctoring integration
- [ ] Learning path recommendations (AI)

### 14.4 Phase 4: Innovation (Q4 2026)
- [ ] AI-powered content suggestions
- [ ] Adaptive learning paths
- [ ] Virtual lab simulations
- [ ] AR/VR content support
- [ ] Peer review workflows
- [ ] Competency mapping and tracking

---

## Appendix

### A. Glossary

| Term | Definition |
|------|------------|
| CBLM | Competency-Based Learning Material |
| NC II | National Certificate Level II |
| TESDA | Technical Education and Skills Development Authority |
| TVET | Technical and Vocational Education and Training |
| LMS | Learning Management System |
| PWA | Progressive Web App |

### B. Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | Jan 2026 | Dev Team | Initial draft |
| 2.0 | Feb 2026 | Dev Team | Security enhancements, gamification |

### C. References

- TESDA Training Regulations
- WCAG 2.1 Guidelines
- OWASP Security Guidelines
- Laravel Best Practices

---

*This document is confidential and intended for internal use only.*

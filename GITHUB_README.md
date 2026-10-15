# 📊 ISU IPCR Management System

A comprehensive Individual Performance Commitment and Review (IPCR) management system built for Isabela State University - Ilagan Campus.

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://www.php.org/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

## ✨ Features

### 👥 User Management
- Multi-role support (Admin, Dean, Faculty, Secretary)
- Department-based organization
- User activation/deactivation
- Bulk user import
- Personal email for password recovery

### 📝 IPCR Form Management
- Template-based IPCR creation
- MFO (Major Final Output) structure
- KRA (Key Result Area) tracking
- Success Indicators with Q/E/T ratings
- Evidence file uploads with AI verification
- Draft, Submit, and Review workflow

### 🤖 AI Evidence Verification
- **Dual Provider Support:**
  - Google Gemini (requires billing)
  - OpenRouter (100% FREE alternative)
- **Verification Modes:**
  - Advisory (recommended) - Shows feedback, never blocks
  - Strict - Blocks uploads below 50% confidence
  - Disabled - No AI checks
- Automated document type validation
- Confidence scoring
- Smart filename matching

### 📊 Rating & Evaluation
- Dean rating interface
- Q/E/T weighted scoring (configurable)
- 5-point rating scale (customizable)
- Average score calculation
- Performance level indicators

### 🔐 Security Features
- Password hashing (bcrypt)
- Failed login attempt tracking
- Account lockout mechanism
- Role-based access control
- Audit logging for all actions
- Session management

### 📧 Email Notifications
- SMTP email support (Gmail, etc.)
- Credential recovery
- Welcome emails for new users
- Notification system

### 📈 Reporting & Analytics
- Period-based reporting
- Submission tracking
- Department-wise statistics
- Export to Excel
- Print-friendly IPCR forms

### ⏰ Period Management
- Academic period configuration
- Submission deadlines
- Deadline extensions per user
- Period status workflow (Open → Rating → Closed → Archived)

## 🚀 Quick Start

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (for dependencies)

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/yourusername/ipcr-system.git
cd ipcr-system
```

2. **Install dependencies**
```bash
composer install
```

3. **Configure database**
```bash
cp config/database.example.php config/database.php
nano config/database.php
```

Update with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'ipcr_system');
```

4. **Configure email (optional)**
```bash
cp config/email.config.example.php config/email.php
nano config/email.php
```

5. **Import database**
```bash
mysql -u username -p ipcr_system < database/ipcr_system.sql
```

Or via phpMyAdmin:
- Import `database/ipcr_system.sql`

6. **Set file permissions**
```bash
chmod 755 uploads/ evidence/ exports/
chmod 644 config/*.php
```

7. **Access the system**
```
http://your-domain.com
```

**Default Admin Login:**
- Employee ID: `ADMIN001`
- Password: `admin123`
- ⚠️ **Change immediately after first login!**

## 🔧 Configuration

### AI Provider Setup

**Option 1: OpenRouter (FREE)**
1. Visit https://openrouter.ai/keys
2. Sign in with Google (free)
3. Create API key
4. Admin → Settings → AI Evidence Verification → Select OpenRouter
5. Paste API key → Save

**Option 2: Google Gemini**
1. Visit https://aistudio.google.com/app/apikey
2. Enable billing (required for full access)
3. Create API key
4. Admin → Settings → AI Evidence Verification → Select Gemini
5. Paste API key → Save

### Email Configuration

For Gmail:
1. Enable 2FA on your Google account
2. Generate App Password: https://myaccount.google.com/apppasswords
3. Update `config/email.php` with:
   - SMTP_USERNAME: your Gmail
   - SMTP_PASSWORD: 16-character app password

## 📖 User Guide

### For Faculty
1. Login with provided credentials
2. Navigate to "IPCR Form"
3. Fill out performance indicators
4. Upload evidence files
5. Submit for dean review

### For Deans
1. Review submitted IPCR forms
2. Rate performance indicators (Q/E/T)
3. Approve or return for revision
4. View department reports

### For Admins
1. Manage users and departments
2. Configure rating periods
3. Customize templates
4. Monitor submissions
5. Generate reports
6. Configure system settings

## 🛠️ Tech Stack

- **Backend:** PHP 8.0+
- **Database:** MySQL 5.7+
- **Frontend:** Bootstrap 5, Vanilla JavaScript
- **Email:** PHPMailer
- **AI:** Google Gemini API / OpenRouter API
- **Authentication:** Session-based
- **File Storage:** Local filesystem

## 📁 Project Structure

```
ipcr-system/
├── admin/              # Admin panel pages
├── dean/               # Dean interface
├── faculty/            # Faculty interface
├── api/                # API endpoints
├── config/             # Configuration files
├── includes/           # Shared includes (header, footer)
├── database/           # SQL files and migrations
├── uploads/            # User uploaded files
├── evidence/           # Evidence file storage
├── exports/            # Generated exports
├── assets/             # CSS, JS, images
│   ├── css/
│   ├── js/
│   └── images/
└── vendor/             # Composer dependencies
```

## 🔒 Security

**IMPORTANT:** Never commit sensitive files!

Protected files (in `.gitignore`):
- `config/database.php` - Database credentials
- `config/email.php` - Email passwords
- `uploads/` - User uploads
- `evidence/` - Evidence files

See [SECURITY.md](SECURITY.md) for detailed security guidelines.

## 🤝 Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Isabela State University - Ilagan Campus
- College of Engineering and Architecture Technology
- All contributors and testers

## 📞 Support

For issues, questions, or suggestions:
- **GitHub Issues:** https://github.com/yourusername/ipcr-system/issues
- **Email:** support@isu.edu.ph
- **Documentation:** See included `.md` files

## 📸 Screenshots

### Faculty Dashboard
![Faculty Dashboard](docs/screenshots/faculty-dashboard.png)

### Dean Review Interface
![Dean Review](docs/screenshots/dean-review.png)

### Admin Settings
![Admin Settings](docs/screenshots/admin-settings.png)

---

**Made with ❤️ for ISU-Ilagan Campus**

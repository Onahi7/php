# Education Summit Registration System

A comprehensive system for managing education summit registrations, payments, and meal validation.

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer for dependency management
- SSL certificate for secure transactions

## Installation

1. **Clone the Repository**
```bash
git clone https://github.com/yourusername/education-summit.git
cd education-summit
```

2. **Environment Setup**
Create a `.env` file in the root directory with the following content:
```env
# Application
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/summit
APP_NAME="Education Summit"

# Database
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASSWORD=your_database_password

# Payment Gateway - Paystack
PAYSTACK_SECRET_KEY=your_secret_key
PAYSTACK_PUBLIC_KEY=your_public_key

# Mail
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Education Summit"

# Admin
ADMIN_EMAIL=admin@yourdomain.com
ADMIN_NOTIFICATION_EMAIL=notifications@yourdomain.com
```

3. **Directory Permissions**
```bash
chmod -R 755 .
chmod -R 777 storage/logs
chmod -R 777 storage/uploads
chmod -R 777 storage/cache
```

4. **Database Setup**
- Create a new MySQL database
- Import the initial schema:
```bash
php install/setup.php
```

5. **Web Server Configuration**
Apache `.htaccess` is already configured. For Nginx, use:
```nginx
location /summit {
    try_files $uri $uri/ /summit/index.php?$query_string;
}
```

## Directory Structure

```
summit/
├── config/               # Configuration files
├── includes/
│   ├── core/            # Core system files
│   ├── auth/            # Authentication
│   └── payment/         # Payment gateways
├── public/              # Public assets
├── storage/
│   ├── logs/           # Application logs
│   ├── uploads/        # User uploads
│   └── cache/          # Cache files
├── views/               # View templates
└── vendor/              # Dependencies
```

## Features

1. **User Management**
   - Registration and authentication
   - Profile management
   - Role-based access control

2. **Payment System**
   - Multiple payment gateways
   - Payment verification
   - Invoice generation
   - Refund management

3. **Meal Management**
   - Barcode/phone validation
   - Meal tracking
   - Validation team dashboard
   - Real-time statistics

4. **Admin Dashboard**
   - User management
   - Payment tracking
   - Report generation
   - System settings

## Security Features

1. **Authentication**
   - Secure password hashing
   - Session management
   - CSRF protection
   - Rate limiting

2. **Data Protection**
   - Input validation
   - SQL injection prevention
   - XSS protection
   - HTTPS enforcement

3. **Access Control**
   - Role-based permissions
   - IP logging
   - Activity monitoring
   - Audit trails

## Usage

1. **Admin Access**
   - Default URL: `/admin`
   - Create initial admin: `php install/create-admin.php`

2. **User Registration**
   - Users register at: `/register`
   - Email verification required
   - Payment confirmation needed

3. **Meal Validation**
   - Validation team login: `/admin/validation-dashboard`
   - Scan barcode or enter phone
   - View real-time stats

## Maintenance

1. **Backups**
   - Database: Daily automated backups
   - Files: Weekly backups
   - Retention: 30 days

2. **Updates**
   - Check for updates: `php update.php check`
   - Apply updates: `php update.php apply`

3. **Logs**
   - Application logs: `storage/logs/app.log`
   - Error logs: `storage/logs/error.log`
   - Access logs: `storage/logs/access.log`

## Support

For support and issues:
1. Check documentation: `/docs`
2. Submit issue: GitHub Issues
3. Contact: support@yourdomain.com

## License

Copyright © 2025 Education Summit. All rights reserved.

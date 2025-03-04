# North Central Education Summit 2025

A modern web application for managing the North Central Education Summit registration, payments, and event management. Built with PHP, MySQL, and modern front-end technologies.

## Features

1. **User Management**
   - Secure registration and authentication
   - Profile management with photo upload
   - Role-based access control (Admin, Validation Team, Participants)

2. **Payment Integration**
   - Seamless Paystack integration
   - Automated payment verification
   - Digital receipt generation
   - Payment tracking dashboard

3. **Event Management**
   - Real-time participant tracking
   - Automated email notifications
   - Customizable event schedule
   - Resource management

4. **Meal Management**
   - QR code-based meal validation
   - Real-time validation statistics
   - Multiple validation points
   - Meal session tracking

5. **Admin Features**
   - Comprehensive dashboard
   - Real-time analytics
   - Report generation
   - System configuration
   - User management

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer for dependency management
- SSL certificate (required for Paystack)

## Installation

1. **Clone the Repository**
```bash
git clone https://github.com/Dickson-Hardy/Summit.git
cd summit
```

2. **Install Dependencies**
```bash
composer install
```

3. **Configure Environment**
Create a `.env` file in the root directory:
```env
# Application
BASE_PATH=/summit
ENVIRONMENT=production

# Database
DB_HOST=localhost
DB_NAME=u633250213_summit
DB_USER=u633250213_summit
DB_PASS=your_password

# Paystack (required for payments)
PAYSTACK_PUBLIC_KEY=your_public_key
PAYSTACK_SECRET_KEY=your_secret_key

# Email (required for notifications)
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=587
SMTP_USER=noreply@conference.nappsnasarawa.com
SMTP_PASS=your_smtp_password
SMTP_FROM=noreply@conference.nappsnasarawa.com
```

4. **Database Setup**
```bash
php install/setup.php
```

5. **Create Admin User**
```bash
php install/create-admin.php
```

6. **Set Directory Permissions**
```bash
chmod -R 755 .
chmod -R 777 uploads/
chmod -R 777 logs/
```

## Directory Structure

```
summit/
├── assets/             # Static assets (CSS, JS, images)
├── config/             # Configuration files
├── includes/
│   ├── core/          # Core system classes
│   ├── security/      # Security-related classes
│   ├── handlers/      # Request handlers
│   └── helpers/       # Helper functions
├── install/           # Installation scripts
├── uploads/          # User uploads
├── views/            # PHP view templates
└── vendor/           # Composer dependencies
```

## Security Features

1. **Authentication & Authorization**
   - Secure password hashing
   - Session-based authentication
   - CSRF protection
   - Rate limiting

2. **Data Protection**
   - Input validation and sanitization
   - Prepared statements for SQL
   - XSS protection
   - Secure file uploads

3. **Session Security**
   - Secure session handling
   - Session hijacking prevention
   - Automatic session regeneration
   - IP-based session validation

## Usage

1. **Admin Dashboard**
   - Access via: `/admin`
   - Manage participants
   - View reports
   - Configure settings

2. **Validation Team**
   - Access via: `/admin/validation-dashboard`
   - Scan participant QR codes
   - View validation statistics
   - Track meal sessions

3. **Participants**
   - Register: `/register`
   - View profile: `/profile`
   - Make payment
   - Download event materials

## Troubleshooting

1. **Common Issues**
   - Session errors: Check `session.php` configuration
   - Payment failures: Verify Paystack credentials
   - Upload errors: Check directory permissions

2. **Error Logs**
   - Application logs: `logs/app.log`
   - Error logs: `logs/error.log`
   - Access logs: `logs/access.log`

## Support

For technical support:
1. Email: support@nappsnasarawa.com
2. Visit: https://conference.nappsnasarawa.com/support
3. Call: +234 (your support number)

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

Copyright 2025 NAPPS Nasarawa. All rights reserved.

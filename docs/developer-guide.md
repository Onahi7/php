# North Central Education Summit - Developer Guide

## Development Environment

### Prerequisites
- PHP 8.1+
- MySQL 8.0+
- Composer
- Node.js 18+
- Git

### Local Setup
```bash
# Clone repository
git clone https://github.com/your-org/summit.git
cd summit

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate
php artisan db:seed

# Start development server
php artisan serve


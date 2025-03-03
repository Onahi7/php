# North Central Education Summit - API Documentation

## API Overview
The Summit API provides programmatic access to registration, payment, and participant management functionalities.

## Authentication
```http
POST /api/auth/token
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "your_password"
}


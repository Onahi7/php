# Deployment Guide for Education Summit Application

This guide will help you deploy the Education Summit application to Hostinger. Follow these steps carefully to ensure a successful deployment.

## Pre-Deployment Checklist

Before uploading your files to Hostinger, make sure you have:

1. Updated `config.php` with the correct settings
2. Updated `.htaccess` with the correct RewriteBase
3. Created all required directories
4. Set up your database on Hostinger
5. Run the `hostinger_check.php` script locally to verify your setup

## Step 1: Prepare Your Database

1. Log in to your Hostinger control panel
2. Navigate to the MySQL Databases section
3. Create a new database (e.g., `u633250213_summit`)
4. Create a new database user with a strong password
5. Assign all privileges to this user for the database
6. Note down the database name, username, and password

## Step 2: Configure Environment Variables

You have two options for configuring sensitive information:

### Option 1: Using .env File (Recommended)

1. Copy the `.env.example` file to `.env`
2. Update the values in the `.env` file with your actual credentials:
   ```
   DB_HOST=localhost
   DB_USER=u633250213_summit
   DB_PASS=your_actual_password
   DB_NAME=u633250213_summit
   PAYSTACK_PUBLIC_KEY=your_actual_public_key
   PAYSTACK_SECRET_KEY=your_actual_secret_key
   SMTP_PASS=your_actual_smtp_password
   ```
3. Upload the `.env` file to your Hostinger server

### Option 2: Update config.php Directly

If you prefer not to use environment variables, you can update the values directly in `config.php`.

## Step 3: Upload Files to Hostinger

1. Connect to your Hostinger account using FTP or the File Manager in the control panel
2. Navigate to the `public_html/summit` directory (create it if it doesn't exist)
3. Upload all files from your local `summit` directory to this location

## Step 4: Set File Permissions

After uploading, you need to set the correct permissions:

1. Navigate to `public_html/summit` in your Hostinger file manager
2. Run the `fix_permissions.php` script by accessing it in your browser:
   ```
   https://conference.nappsnasarawa.com/summit/fix_permissions.php
   ```
3. Verify that all directories have the correct permissions

## Step 5: Run Installation Script

1. Run the installation script to set up the database and required directories:
   ```
   https://conference.nappsnasarawa.com/summit/install.php
   ```
2. Follow the on-screen instructions to complete the installation

## Step 6: Verify Deployment

1. Run the Hostinger check script to verify your deployment:
   ```
   https://conference.nappsnasarawa.com/summit/hostinger_check.php
   ```
2. Check that all items are marked as successful
3. Address any issues that are reported

## Step 7: Test the Application

1. Visit your application's main URL:
   ```
   https://conference.nappsnasarawa.com/summit/
   ```
2. Test all major functionality:
   - User registration
   - Login
   - Payment processing
   - Admin functions

## Troubleshooting

### Common Issues

1. **404 Page Not Found**
   - Check that `.htaccess` has the correct RewriteBase setting
   - Verify that mod_rewrite is enabled on your Hostinger server

2. **Database Connection Errors**
   - Verify your database credentials in `.env` or `config.php`
   - Check that the database exists and the user has the correct permissions

3. **Permission Errors**
   - Run `fix_permissions.php` again
   - Check that all required directories are writable

4. **Blank Pages or PHP Errors**
   - Check the error logs in the `logs` directory
   - Temporarily enable error display by setting `ENVIRONMENT` to 'development'

### Getting Help

If you encounter issues that you cannot resolve, please:

1. Check the error logs in the `logs` directory
2. Run the `verify.php` script for a detailed system check
3. Contact Hostinger support with specific error messages

## Security Recommendations

1. **Protect Sensitive Files**
   - Restrict access to configuration files
   - Use `.htaccess` to deny direct access to sensitive directories

2. **Regular Backups**
   - Set up regular database backups
   - Back up your application files regularly

3. **Keep Software Updated**
   - Regularly update your application code
   - Keep PHP and other server software up to date

4. **Monitor Logs**
   - Regularly check your application logs for unusual activity
   - Set up alerts for critical errors

## Maintenance

1. **Regular Checks**
   - Periodically run `verify.php` to check system health
   - Monitor disk space usage

2. **Database Optimization**
   - Regularly optimize your database tables
   - Clean up old data that is no longer needed

---

By following this guide, you should have a successfully deployed Education Summit application on Hostinger. If you have any questions or need further assistance, please contact your system administrator.

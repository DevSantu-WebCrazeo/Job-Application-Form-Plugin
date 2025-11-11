# Job Application Form Plugin

A professional WordPress plugin for managing job applications with file upload, email notifications, virus checking, and admin management.

**Author:** DevSantu  
**Author URI:** https://devsantu.in  
**Version:** 1.0.0  
**License:** GPL v3 or later

## Features

- ✅ **Professional Form Design** - Beautiful, responsive form matching your design requirements
- ✅ **Shortcode Support** - Easy to use `[job_application_form]` shortcode
- ✅ **Customizable Application ID** - Configurable prefix and length for unique IDs (e.g., JAF-12345678)
- ✅ **Page Tracking** - Stores page title and URL where the form was submitted
- ✅ **Email Notifications** - Styled HTML emails sent to admin and applicant
- ✅ **Customizable Thank You Page** - Configure custom thank you page URL from admin settings
- ✅ **Application ID Shortcode** - Display application ID on thank you page using `[application_id]`
- ✅ **Secure File Upload** - Resume upload with multiple security layers and virus checking
- ✅ **Virus Protection** - Multiple security checks including ClamAV support
- ✅ **Admin Dashboard** - Complete admin interface to manage applications
- ✅ **Secure File Downloads** - Admin can download files with proper permission checks
- ✅ **Search & Filter** - Search by name, email, phone, or ID; filter by position
- ✅ **Export to CSV** - Export all applications to CSV format
- ✅ **View Details** - View complete application details in a modal
- ✅ **Delete Applications** - Remove applications with automatic file cleanup
- ✅ **Settings Page** - Configure notification email, file types, file size, thank you page, and application ID format

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The database table and upload directory will be created automatically upon activation
4. The plugin will automatically create a secure upload directory with `.htaccess` protection
5. Configure settings in **Job Applications > Settings**

## Usage

### Display the Form

Use the shortcode anywhere on your site:

```
[job_application_form]
```

### Display Application ID on Thank You Page

After form submission, users are redirected to the thank you page with the application ID in the URL. Use the shortcode to display it:

```
[application_id]
```

The shortcode will automatically display the application ID from the URL parameter (`?app_id=APP-123456`).

### Admin Panel

1. Go to **Job Applications** in the WordPress admin menu
2. View all applications in a table format with pagination
3. Use search and filter options to find specific applications
4. Click **"View"** to see complete details in a modal popup
5. Click **"Download"** to download resume files (admin only)
6. Click **"Delete"** to remove an application (files are automatically deleted)
7. Click **"Export CSV"** to download all applications as CSV file
8. **Admin Permissions** - Only users with `manage_options` capability can access

### Settings

1. Go to **Job Applications > Settings**
2. Configure:
   - **Notification Email** - Email address where admin notifications are sent
   - **Allowed File Types** - Comma-separated list (e.g., pdf,doc,docx,txt,rtf,png)
   - **Max File Size** - Maximum file size in megabytes (MB)
   - **Thank You Page URL** - Custom thank you page URL (optional, defaults to `/thank-you/`)
   - **Application ID Prefix** - Custom prefix for application IDs (e.g., JAF, APP, JOB)
   - **Application ID Length** - Length of the random part (4-12 characters, recommended: 6-8)

**Example:** With prefix "JAF" and length 8, application IDs will look like: `JAF-12345678`

### Thank You Page Setup

1. Create a thank you page in WordPress (Pages > Add New)
2. Add the shortcode `[application_id]` to display the application ID
3. Go to **Job Applications > Settings**
4. Enter the thank you page URL in the "Thank You Page URL" field
5. The application ID will be automatically passed as a query parameter (`?app_id=APP-123456`)
6. If no URL is specified, it defaults to `/thank-you/`

**Example Thank You Page Content:**
```
Thank you for your application!

Your Application ID: [application_id]

We will review your application and get back to you soon.
```

## Email Templates

The plugin includes two beautifully styled HTML email templates:

1. **Admin Notification** - Sent to admin when a new application is submitted
   - Blue gradient header
   - Complete application details
   - Direct link to admin panel

2. **Applicant Confirmation** - Sent to the applicant as confirmation
   - Green gradient header
   - Application ID for reference
   - Professional thank you message

## Security Features

### File Upload Protection

1. **File Type Validation** - Only allowed extensions (PDF, DOC, DOCX, TXT, RTF)
2. **MIME Type Checking** - Validates actual file type, not just extension (double verification)
3. **Content Scanning** - Checks for malicious code patterns (PHP tags, script tags, etc.)
4. **Size Validation** - Enforces maximum file size limit (configurable)
5. **Dangerous Extension Blocking** - Blocks executable and script files (exe, bat, php, js, etc.)
6. **ClamAV Integration** - Optional virus scanning if ClamAV is installed
7. **Secure Storage** - Files stored in protected directory with enhanced `.htaccess` protection
8. **Proper File Permissions** - Files stored with secure permissions (0640)

### File Storage Security

- **Protected Directory** - Files stored in `wp-content/uploads/job-applications/`
- **`.htaccess` Protection** - Multiple security layers:
  - Denies all direct HTTP access (Apache 2.2 and 2.4 compatible)
  - Prevents directory listing
  - Blocks PHP/script execution if malicious files are uploaded
  - Works with both Apache 2.2 and 2.4+
- **Secure File Permissions** - Files: 0640, Directories: 0755
- **Automatic Directory Creation** - Created on plugin activation with proper permissions

### File Download Security

- **Admin Access** - Administrators can download all files without restrictions
- **Nonce Verification** - All download requests require valid nonce tokens
- **Permission Checks** - Verifies user capabilities before allowing downloads
- **Secure File Path Resolution** - Handles URLs, absolute paths, and relative paths safely
- **Direct Access Blocked** - Files cannot be accessed directly via URL (blocked by `.htaccess`)

### Data Protection

- **Input Sanitization** - All user inputs are sanitized and validated
- **SQL Injection Protection** - All database queries use prepared statements
- **CSRF Protection** - Nonce verification for all form submissions and AJAX requests
- **XSS Protection** - All output is properly escaped (esc_html, esc_url, esc_attr)
- **Email Validation** - Proper email format validation using WordPress functions
- **File Name Sanitization** - Uploaded file names are sanitized to prevent directory traversal

## Database Structure

The plugin creates a table `wp_job_applications` with the following fields:

- `id` - Auto-increment primary key
- `unique_id` - Unique application identifier
- `name` - Applicant name
- `phone` - Phone number
- `email` - Email address
- `position` - Applied position
- `message` - Optional message
- `resume_path` - Path to uploaded resume
- `page_title` - Title of page where form was submitted
- `page_url` - URL of page where form was submitted
- `submitted_at` - Timestamp of submission

## File Structure

```
job-application-form/
├── job-application-form.php (Main plugin file)
├── includes/
│   ├── class-jaf-database.php (Database operations)
│   ├── class-jaf-form-handler.php (Form processing)
│   ├── class-jaf-file-uploader.php (File upload & security)
│   ├── class-jaf-file-downloader.php (Secure file download handler)
│   └── class-jaf-admin.php (Admin interface)
├── templates/
│   ├── form-template.php (Frontend form)
│   ├── admin-page.php (Admin listing page)
│   ├── admin-settings.php (Settings page)
│   ├── submission-details.php (View details template)
│   ├── email-admin-notification.php (Admin email template)
│   └── email-applicant-confirmation.php (Applicant email template)
├── assets/
│   ├── css/
│   │   ├── style.css (Frontend styles)
│   │   └── admin.css (Admin styles)
│   └── js/
│       ├── script.js (Frontend JavaScript)
│       └── admin.js (Admin JavaScript)
└── README.md (This file)
```

## Upload Directory Structure

When the plugin is activated, it creates the following structure:

```
wp-content/
└── uploads/
    └── job-applications/
        ├── .htaccess (Security protection file)
        └── [uploaded files]
```

The `.htaccess` file is automatically created to protect uploaded files from direct access.

## Requirements

- **WordPress:** 5.0 or higher
- **PHP:** 7.4 or higher (PHP 8.0+ recommended)
- **MySQL:** 5.6 or higher (MySQL 5.7+ or MariaDB 10.2+ recommended)
- **Apache:** 2.2 or higher (with mod_rewrite and mod_authz_host/mod_authz_core)
- **PHP Extensions:** 
  - `fileinfo` (for MIME type detection)
  - `mbstring` (recommended)
  - `gd` (for image processing, if needed)

## Optional: ClamAV for Enhanced Virus Scanning

For enhanced virus protection, you can install ClamAV on your server:

```bash
# Ubuntu/Debian
sudo apt-get install clamav clamav-daemon

# CentOS/RHEL
sudo yum install clamav clamav-update
```

The plugin will automatically detect and use ClamAV if available.

## Error Handling

The plugin includes comprehensive error handling:

- **File Upload Errors** - Specific error messages for each upload error type
- **Database Errors** - Error logging in debug mode
- **Permission Errors** - Clear messages for permission-related issues
- **Validation Errors** - User-friendly validation error messages
- **Security Errors** - Proper error handling for security violations

## Troubleshooting

### File Upload Issues

- **Permission Errors** - Ensure `wp-content/uploads/` directory is writable (755 or 775)
- **File Size Limits** - Check PHP `upload_max_filesize` and `post_max_size` settings
- **File Type Rejected** - Verify file type is in allowed list in Settings
- **.htaccess Not Created** - Check directory permissions and ensure WordPress can write files

### Admin Access Issues

- **Cannot Download Files** - Ensure you have administrator privileges
- **Files Not Found** - Check if files exist in `wp-content/uploads/job-applications/`
- **Permission Denied** - Verify nonce tokens are valid and user has proper capabilities

### Database Issues

- **Table Not Created** - Deactivate and reactivate the plugin
- **Data Not Saving** - Check database user permissions and table structure
- **Query Errors** - Enable WordPress debug mode to see detailed error messages

## Changelog

### Version 1.0.0
- Initial release
- File upload with security validation
- Admin dashboard with search and filter
- Email notifications
- CSV export functionality
- Secure file download system
- Enhanced .htaccess protection
- Customizable application ID format
- Customizable thank you page URL
- Application ID shortcode support
- Dynamic MIME type validation (supports PNG, JPG, and other image types)
- Comprehensive error handling
- File permission management
- Image file validation

## Support

For issues, questions, or feature requests, please contact:

- **Author:** DevSantu
- **Website:** https://devsantu.in
- **License:** GPL v2 or later

## Credits

Developed by DevSantu - Professional WordPress Plugin Development

## License

GPL v2 or later

Copyright (c) 2024 DevSantu

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.


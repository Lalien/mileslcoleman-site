# Contact Form SQLite Database Implementation

This implementation adds SQLite database storage for all contact form submissions before sending email notifications.

## Features Implemented

### 1. Database Storage
- All contact form submissions are saved to a SQLite database (`leads.db`)
- Stores: name, email, phone, message, timestamp, user agent, and IP address
- Uses PDO with prepared statements for security (SQL injection prevention)

### 2. Enhanced Contact Handler
- `contact-handler.php` now saves submissions to database first
- If database save fails, email still attempts to send (graceful degradation)
- Logs database errors for debugging
- Maintains all existing validation and sanitization

### 3. Admin Interface
- Password-protected admin panel at `/admin/view-leads.php`
- View all submitted leads in a table format
- Search/filter by name, email, or message
- Pagination for large datasets
- Export to CSV functionality
- Modern, responsive design

### 4. Security Measures
- `.htaccess` protects database file from direct web access
- `.gitignore` excludes database and credentials from version control
- Admin interface requires password authentication
- All data sanitized before storage
- PDO prepared statements prevent SQL injection

## Setup Instructions

### Initial Setup

1. **Initialize the database:**
   ```bash
   php init-database.php
   ```
   This creates the `leads.db` file and `leads` table. Safe to run multiple times.

2. **Change admin password:**
   Edit `admin/view-leads.php` and change line:
   ```php
   $ADMIN_PASSWORD = 'changeme123'; // Change this!
   ```

3. **Build the project:**
   ```bash
   npm run build
   ```

### Production Deployment

1. Upload all files to your server (except `test-implementation.php`)
2. Run `php init-database.php` on the server to create the database
3. **Change the recipient email** in `contact-handler.php` (line 113)
4. **Change the admin password** in `admin/view-leads.php` (line 13)
5. **(Recommended)** Move database outside web root for enhanced security
6. Ensure the database file has proper write permissions:
   ```bash
   chmod 664 leads.db
   chmod 775 .
   ```
7. Verify `.htaccess` is protecting the database file

## File Structure

```
project/
├── contact-handler.php      # Contact form handler (updated)
├── init-database.php        # Database initialization script
├── leads.db                 # SQLite database (created by init script)
├── .htaccess               # Updated with database protection
├── .gitignore              # Updated to exclude leads.db
├── admin/
│   └── view-leads.php      # Admin interface
└── test-implementation.php  # Test script
```

## Usage

### For Users
- No changes to the contact form interface
- Users submit forms as normal
- Form data is now saved to database before email is sent

### For Administrators

**Access Admin Panel:**
```
https://yourdomain.com/admin/view-leads.php
```

**Features:**
- View all leads in chronological order
- Search by name, email, or message content
- Click "Export CSV" to download all leads
- Pagination automatically handles large datasets

## Database Schema

```sql
CREATE TABLE leads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT,
    message TEXT NOT NULL,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_agent TEXT,
    ip_address TEXT
)
```

## Testing

Run the comprehensive test suite:
```bash
php test-implementation.php
```

This verifies:
- Database connection
- Table structure
- Data insertion and retrieval
- Contact handler integration
- Admin interface presence
- Security configurations

## Error Handling

The implementation includes graceful error handling:

1. **Database Connection Failure:**
   - Error is logged
   - Email still attempts to send
   - User receives normal response

2. **Database Insert Failure:**
   - Error is logged
   - Email still attempts to send
   - User receives normal response

3. **Email Send Failure:**
   - Returns error to user
   - Data is already saved in database

## Security Considerations

### Database Protection
- `.htaccess` denies direct access to `leads.db` (Apache 2.4+ syntax)
- Database file should ideally be outside web root (see Production Best Practices below)
- For enhanced security, move database outside web root and update paths in PHP files

### Production Best Practices

**Move Database Outside Web Root (Recommended):**
```bash
# Move database to a secure location
mv leads.db /var/secure/leads.db

# Update contact-handler.php:
$dbPath = '/var/secure/leads.db';

# Update admin/view-leads.php:
$dbPath = '/var/secure/leads.db';
```

### Admin Access
- **IMPORTANT:** Change default password immediately in `admin/view-leads.php`
- Default password: `changeme123` (line 13)
- Consider using environment variables: `$ADMIN_PASSWORD = getenv('ADMIN_PASSWORD');`
- Consider implementing stronger authentication (OAuth, 2FA, etc.)
- Use HTTPS for admin panel access

### Data Privacy
- Stores IP addresses for security tracking
- Consider GDPR/privacy implications
- Add data retention/deletion policies as needed

## Maintenance

### View Leads Directly
```bash
sqlite3 leads.db "SELECT * FROM leads ORDER BY submitted_at DESC LIMIT 10;"
```

### Export All Leads
```bash
sqlite3 -header -csv leads.db "SELECT * FROM leads;" > leads_export.csv
```

### Clear Old Leads (Example)
```bash
sqlite3 leads.db "DELETE FROM leads WHERE submitted_at < date('now', '-1 year');"
```

### Backup Database
```bash
cp leads.db leads_backup_$(date +%Y%m%d).db
```

## Troubleshooting

### Database file not writable
```bash
chmod 664 leads.db
chmod 775 .
```

### Admin login not working
- Check that PHP sessions are enabled
- Verify password in `admin/view-leads.php`
- Clear browser cookies

### Build process fails
```bash
npm install
npm run build
```

## Future Enhancements

Consider adding:
- Email notifications for new leads
- Lead status tracking (new, contacted, closed)
- More robust admin authentication (users, roles)
- Lead assignment to team members
- Analytics dashboard
- Automated lead scoring
- Integration with CRM systems

## Support

For issues or questions:
1. Run `php test-implementation.php` to diagnose problems
2. Check server error logs
3. Verify database file permissions
4. Ensure PHP PDO SQLite extension is enabled: `php -m | grep -i pdo`

## Changelog

### Version 1.0 (2026-01-12)
- Initial implementation
- SQLite database integration
- Admin interface with authentication
- CSV export functionality
- Search and pagination
- Security measures (.htaccess, .gitignore)
- Comprehensive test suite

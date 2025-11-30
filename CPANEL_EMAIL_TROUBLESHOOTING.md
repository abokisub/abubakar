# cPanel Email Troubleshooting Guide

## Issue: OTP emails not being sent to new users and on login

### Quick Diagnosis Steps

1. **Check Laravel Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Look for email-related errors

2. **Test Email Configuration**
   - Access: `https://yourdomain.com/test-email.php`
   - This will show your current email configuration
   - Try sending a test email

3. **Check .env File**
   Ensure these are set correctly:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=mail.yourdomain.com
   MAIL_PORT=465
   MAIL_USERNAME=support@yourdomain.com
   MAIL_PASSWORD=your_email_password
   MAIL_ENCRYPTION=ssl
   MAIL_FROM_ADDRESS=support@yourdomain.com
   MAIL_FROM_NAME="${APP_NAME}"
   ```

### Common Issues & Solutions

#### Issue 1: SMTP Authentication Failed
**Symptoms:**
- Error: "SMTP authentication failed"
- Emails not sending

**Solutions:**
1. Use full email address as `MAIL_USERNAME` (e.g., `support@yourdomain.com`)
2. Verify password is correct (no extra spaces)
3. Check if email account exists in cPanel
4. Try creating a new email account in cPanel

#### Issue 2: Connection Timeout
**Symptoms:**
- Error: "Connection timeout"
- Emails hang and fail

**Solutions:**
1. Try `MAIL_HOST=localhost` instead of `mail.yourdomain.com`
2. Try port 587 with TLS instead of 465 with SSL
3. Check if port 465/587 is blocked by firewall
4. Use `sendmail` driver instead (see below)

#### Issue 3: Wrong Port/Encryption
**Symptoms:**
- Connection errors
- SSL/TLS errors

**Solutions:**
- **Option A (SSL):**
  ```
  MAIL_PORT=465
  MAIL_ENCRYPTION=ssl
  ```

- **Option B (TLS):**
  ```
  MAIL_PORT=587
  MAIL_ENCRYPTION=tls
  ```

#### Issue 4: Using Sendmail (Recommended for cPanel)
**If SMTP doesn't work, use sendmail:**

```env
MAIL_MAILER=sendmail
MAIL_SENDMAIL_PATH=/usr/sbin/sendmail -t -i
MAIL_FROM_ADDRESS=support@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Note:** Remove SMTP settings when using sendmail

#### Issue 5: Emails Going to Spam
**Solutions:**
1. Set up SPF record in cPanel DNS
2. Set up DKIM record in cPanel
3. Use a proper `MAIL_FROM_ADDRESS` (must match your domain)
4. Avoid spam trigger words in subject/body

#### Issue 6: Email Account Not Created
**Solution:**
1. Go to cPanel → Email Accounts
2. Create email account: `support@yourdomain.com`
3. Use this email and password in `.env`

### Step-by-Step Fix

1. **Create Email Account in cPanel**
   - Login to cPanel
   - Go to "Email Accounts"
   - Create: `support@yourdomain.com`
   - Set a strong password
   - Note the password

2. **Update .env File**
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=mail.yourdomain.com
   MAIL_PORT=465
   MAIL_USERNAME=support@yourdomain.com
   MAIL_PASSWORD=your_actual_password_here
   MAIL_ENCRYPTION=ssl
   MAIL_FROM_ADDRESS=support@yourdomain.com
   MAIL_FROM_NAME="KoboPoint"
   ```

3. **Clear Config Cache**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

4. **Test Email**
   - Visit: `https://yourdomain.com/test-email.php`
   - Send test email
   - Check inbox and spam folder

5. **If SMTP Still Fails, Use Sendmail**
   ```env
   MAIL_MAILER=sendmail
   MAIL_SENDMAIL_PATH=/usr/sbin/sendmail -t -i
   MAIL_FROM_ADDRESS=support@yourdomain.com
   MAIL_FROM_NAME="KoboPoint"
   ```
   Then clear cache again:
   ```bash
   php artisan config:clear
   ```

### Verify Email Templates Exist

Check these files exist:
- `resources/views/email/verify.blade.php`
- `resources/views/email/otp_verification.blade.php`
- `resources/views/email/welcome.blade.php`

### Check Application Logs

```bash
# View recent email errors
tail -n 100 storage/logs/laravel.log | grep -i mail

# View all errors
tail -n 500 storage/logs/laravel.log
```

### Testing After Fix

1. Try registering a new user
2. Check if OTP email is received
3. Try login with OTP
4. Check Laravel logs for any errors

### Security Note

**IMPORTANT:** After testing, delete `public/test-email.php` as it exposes configuration information!

```bash
rm public/test-email.php
```

### Still Not Working?

1. Check cPanel email account quota (might be full)
2. Check cPanel email account is not suspended
3. Try sending email manually from cPanel webmail
4. Contact hosting support to check mail server status
5. Check if domain has proper MX records




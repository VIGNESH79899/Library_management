# ANTIGRAVITY LMS — Reminder Cron Scheduling Guide

## Overview

The script `cron/send_due_reminders.php` runs once daily and emails every
member whose borrowed book is due in **exactly 2 days**, provided they
haven't already received a reminder (`Reminder_Sent = 0`).

---

## Step 0 — Run the Migration First

Before scheduling, add the `Reminder_Sent` column to your `Issue` table:

```bash
mysql -u root -p LibraryManagementSystem < database/migrations/add_reminder_sent_to_issue.sql
```

Or let the cron script do it automatically on first run (it's idempotent).

---

## 🐧 Linux / macOS — cron

### 1. Open your crontab

```bash
crontab -e
```

### 2. Add this line (runs every day at **7:00 AM**)

```cron
0 7 * * * /usr/bin/php /var/www/html/Library-management/cron/send_due_reminders.php >> /var/log/lms_reminders.log 2>&1
```

> **Adjust the path** to match your server's PHP binary and project root.
> Find your PHP path with: `which php`

### 3. Verify it's registered

```bash
crontab -l
```

### 4. Test a dry-run manually

```bash
php /var/www/html/Library-management/cron/send_due_reminders.php
```

### Expected log output

```
[2026-02-23 07:00:01] [INFO] ════════════════════════════════════════
[2026-02-23 07:00:01] [INFO] Due-date reminder job started.
[2026-02-23 07:00:01] [INFO] Target date: 2026-02-25
[2026-02-23 07:00:01] [INFO] Found 3 issue(s) due for reminders.
[2026-02-23 07:00:01] [INFO] Processing Issue #12 — Vignesh S <v@aurora.edu.in> — "DBMS Concepts"
[2026-02-23 07:00:02] [INFO]   ✓ Sent & marked — Issue #12
...
[2026-02-23 07:00:03] [INFO] Job complete. Sent: 3 | Failed: 0 | Total: 3
[2026-02-23 07:00:03] [INFO] ════════════════════════════════════════
```

---

## 🪟 Windows — Task Scheduler (XAMPP)

### Step 1 — Open Task Scheduler

Press `Win + R` → type `taskschd.msc` → press Enter.

### Step 2 — Create Basic Task

1. In the right panel, click **"Create Basic Task…"**
2. **Name**: `LMS Due-Date Reminder`
3. **Description**: `Sends 2-day reminder emails for overdue books`
4. Click **Next**

### Step 3 — Set Trigger

1. Select **Daily**
2. Click **Next**
3. Set **Start time**: `07:00:00`
4. Set **Recur every**: `1` day
5. Click **Next**

### Step 4 — Set Action

1. Select **"Start a program"**
2. Click **Next**
3. Fill in:

| Field              | Value                                                            |
| ------------------ | ---------------------------------------------------------------- |
| **Program/script** | `C:\xampp\php\php.exe`                                           |
| **Add arguments**  | `C:\xampp\htdocs\Library-management\cron\send_due_reminders.php` |
| **Start in**       | `C:\xampp\htdocs\Library-management\cron`                        |

4. Click **Next → Finish**

### Step 5 — Test it immediately

Right-click your new task → **"Run"** → check the log file at:

```
C:\xampp\htdocs\Library-management\logs\reminders.log
```

### Step 6 — (Optional) Advanced: Run whether logged in or not

1. Right-click the task → **Properties**
2. Under **General**, select **"Run whether user is logged on or not"**
3. Check **"Run with highest privileges"**
4. Click **OK** and enter your Windows password when prompted

---

## 🔒 Security Notes

| Concern            | Protection                                              |
| ------------------ | ------------------------------------------------------- |
| Browser access     | `PHP_SAPI !== 'cli'` check returns HTTP 403             |
| SQL injection      | All queries use `prepare()` + `bind_param()`            |
| Duplicate emails   | `Reminder_Sent = 1` set atomically after send           |
| Failed email retry | `Reminder_Sent` stays `0` on failure — retried next run |
| SMTP credentials   | Move to `.env` or `config/mail.php` for production      |

---

## 📁 Files Created

```
cron/
  send_due_reminders.php          ← Main cron script
emails/
  reminder_template.php           ← Amber-themed HTML email
  EmailLogger.php                 ← Logs to Email_Log table
database/migrations/
  add_reminder_sent_to_issue.sql  ← Adds Reminder_Sent column
logs/
  reminders.log                   ← Auto-created on first run
```

---

## 🔗 Checking results in Admin panel

After the cron runs, visit:

```
http://localhost/Library-management/admin/email_logs.php
```

Filter by **Type = Reminder** to see all reminder email attempts.

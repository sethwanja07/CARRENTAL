# DriveEasy – Car Rental System (PHP + MySQL)

## Run locally (XAMPP / WAMP / Laragon)
1. Copy this `car_rental` folder into `C:\xampp\htdocs\` (or your server's web root).
2. Start **Apache** and **MySQL**.
3. Open http://localhost/phpmyadmin → **Import** → choose `database.sql` → Go.
   (It creates the `car_rental` database, tables, and sample data.)
4. If your MySQL has a password, edit it in `config.php`.
5. Open http://localhost/car_rental/

## Demo logins (password: admin123)
- Admin: admin@carrental.test
- Customer: customer@carrental.test

## Features
Customer: register/login, search by brand/category/dates (only free cars shown), book, view and cancel bookings.
Admin: dashboard stats, add/edit/delete cars with photo upload, approve/reject bookings, mark returned.
Security: PDO prepared statements, password_hash, CSRF tokens, output escaping, role checks.

## Structure
config.php · index.php · book.php · my_bookings.php · login/register/logout.php
admin/ (index, cars, bookings) · includes/ (header, footer) · assets/style.css · uploads/ · database.sql

## New in v2
- Logo (assets/logo.svg) in the header and as favicon.
- Pay before booking: bank transfer, MTN Mobile Money or Airtel Money. Booking is created only after payment.
- Notifications (bell icon): admin is notified of every paid booking and cancellation; customer is notified of payment, approval, rejection and return.
- SMS to the customer's phone (and the admin's) on payment and status changes. See admin > SMS for the log.
- Already have data? Import `upgrade.sql` instead of `database.sql` (adds the new tables only). Importing `database.sql` resets everything.

## Going live
- SMS: create an Africa's Talking account, then in config.php set SMS_ENABLED=true, SMS_USERNAME, SMS_API_KEY (and the live SMS_API_URL).
- Payments are simulated (no money moves). For real payments connect the MTN MoMo / Airtel Money APIs in book.php where the payment row is inserted.

# Luxury Motel - Hotel Booking System

A PHP-based hotel booking and management system with admin panel.

## Features

- **User Panel**: Browse rooms, make reservations, manage bookings
- **Admin Panel**: Manage rooms, room types, floors, amenities, special requests, users, and bookings
- **Booking Workflow**: Submit, approve, reject, check-in, check-out, and cancel reservations
- **Reports**: Daily, monthly, yearly, revenue, occupancy, and customer reports with CSV export
- **Special Requests**: Guests can add requests like extra pillows, baby cot, etc.

## Requirements

- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx
- XAMPP / WAMP / LAMP stack

## Installation

1. Clone the repo into your web server directory
2. Import `database/motel_booking.sql` into MySQL
3. Configure database credentials in `config/db.php`
4. Access the site at `http://localhost/motel-app`

### Default Admin Login

- **Email**: admin@luxurymotel.com
- **Password**: password123

## Tech Stack

- PHP (PDO)
- MySQL
- Tailwind CSS
- Font Awesome
- Chart.js

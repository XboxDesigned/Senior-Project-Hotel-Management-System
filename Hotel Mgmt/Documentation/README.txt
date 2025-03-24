# Hotel Management System

## Overview
The Hotel Management System is a senior capstone project developed by a team of students from Pennwest University to address inefficiencies in existing hotel management solutions.
Inspired by real-world challenges faced by our project manager, Nicholas Frischkorn, this system aims to streamline hotel operations with an intuitive, efficient, and user-friendly interface.
It manages critical hotel functions such as room bookings, guest check-ins/check-outs, billing, inventory, and housekeeping, enhancing both staff productivity and guest satisfaction.

The project was completed as part of the Senior Capstone I course, with the final design specification submitted on December 9, 2024.
Development and implementation continued through April 28, 2025, delivering a fully functional system designed to meet modern hotel operational needs.

## Features
- User Authentication: Secure login for administrators and staff with role-based access control.
- Room Management: Real-time dashboard for room availability, with options to add, edit, or remove rooms (e.g., single, double, suite).
- Reservation System: Booking, cancellation, and calendar-based reservation management for staff and admins.
- Check-in/Check-out: Tracks guest arrivals and departures, integrates with reservations, and generates bills/receipts.
- Billing & Payment: Invoice generation for room charges, services, and additional expenses.
- Employee Management: Admin ability to add, edit, or remove staff profiles.
- Housekeeping & Maintenance: Task assignment and status tracking (e.g., clean, in-progress, maintenance required).
- Night Audit: Daily reconciliation of reservations, billing updates, and end-of-day reporting.
- Usability: Intuitive UI with minimal training required; booking operations complete within 5 seconds.
- Reliability: 99.9% uptime with data backup for system recovery.

## Installation
To set up the Hotel Management System locally, follow these steps:

### Prerequisites
- XAMPP: For local server and database management (Apache, MySQL).
- SQL Plus: For database creation and testing.
- Notepad++: Recommended for code editing (or any preferred text editor).
- A modern web browser (e.g., Chrome, Firefox).

### Steps
1. Clone the Repository: git clone https://github.com/XboxDesigned/Senior-Project-Hotel-Management-System

2. Install XAMPP:
- Download and install XAMPP from https://www.apachefriends.org/.
- Start the Apache and MySQL modules via the XAMPP Control Panel.

3. Set Up the Database:
- Open phpMyAdmin (via http://localhost/phpmyadmin in your browser).
- Create a new database named "hotel_management".
- Import the SQL schema from "database/hotel_management.sql" (to be provided in the repository).

4. Configure the Application:
- Move the project files to the "htdocs" folder in your XAMPP directory (e.g., C:\xampp\htdocs\Senior-Project-Hotel-Management-System-main).
- Update the database connection settings in "config/db_config.php" with your MySQL credentials (default: "root", no password).

5. Run the Application:
- Open a browser and navigate to http://localhost/Senior-Project-Hotel-Management-System-main.
- Log in with default credentials and change them immediately for security reasons.
(Admin Username: "admin" / Password: "password")
(Front Desk Username: Front_Desk / Password: "password")
(Housekeeping Username: Housekeeping / Password: "password")
(Maintenance Username: Maintenance / Password: "password")

## Usage
Login: Access the system via the login page with your assigned credentials.
Receptionist/Front Desk Dashboard: Manage reservations, check room availability, handle check-ins/check-outs, and generate bills.
Night Audit: Run end-of-day processes to reconcile data and generate reports.
Admin Dashboard: Oversee employee profiles, room configurations, and system settings.
Maintenance Dashboard: View assigned tasks and complete them.
Housekeeping Dashboard: View assigned tasks and complete them.

## Technologies Used
- Frontend: HTML, CSS, JavaScript.
- Backend: PHP (via XAMPP’s Apache server).
- Database: MySQL (managed with phpMyAdmin).

##Development Tools
- Microsoft Visio: For topology, context, and data flow diagrams.
- Microsoft PowerPoint: for presenting.
- Notepad++: For coding and editing.
- Google Docs: For collaborative documentation.
- XAMPP: For local server and testing environment.

## Project Team
- Nicholas Frischkorn - Project Manager, Designer, Coder, Tester  
Email: fri5931@pennwest.edu | Phone: (814) 615-8014
- Skyler (Pio) DiPofi - Designer, Coder  
Email: dip56205@pennwest.edu
- Lyubomir Dimitrov - Designer, Coder  
Email: dim8751@pennwest.edu
- Mark Feick - Coder, Tester  
Email: fei2112@pennwest.edu

## Milestones
- December 9, 2024: Project documentation completed.
- February 18, 2025: Barebones prototype delivered.
- April 6, 2025: Fully functional system completed.
- April 28, 2025: Final programming, testing, and polishing finalized.
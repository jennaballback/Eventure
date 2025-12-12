# Eventure by team MumboJumbo
A College Event-Planning Web Application (Project URL: https://github.com/jennaballback/Eventure)

Eventure is a lightweight, PHP–MySQL powered event-planning platform designed to help college students create, manage, and RSVP to events. Users can build events, track attendance, and view upcoming, hosted, and past gatherings; all through an intuitive interface with clean visual design.

Features:
User Features: Create, edit, and delete events, View events by category: Upcoming, Hosted, and Past, RSVP to events with Yes, No, or Maybe, View attendance counts and attendee lists, and Receive event invitations via email  
System Features: Secure login/logout with session handling, Clean, responsive UI built with Bootstrap + custom CSS, Structured MySQL database with relational constraints, Email integration using PHPMailer + Gmail SMTP, and Integrated test suite for DB connectivity and PHP functionality

Installation & Run:
1. download XAMPP for Apache: https://www.apachefriends.org/  (if you don't already have)    
For setup: only need to select Apache, MySQL, PHP, phpMyAdmin    
YOU CAN UNSELECT THESE: FileZilla FTP Server, Mercury Mail Server, Tomcat, Perl, Webalizer, Fake Sendmail  
2. Clone using Git or Download ZIP
3. Move Project Files -> Make sure the entire project folder is under C:\xampp\htdocs\eventure (in your file explorer)
4. Open XAMPP Control Panel -> Start Apache & MySQL (Both should turn green)
5. Import the Database (Using the SQL File Already Included)
The database file here:/sql/event_planner.sql  
Open phpMyAdmin & Click New → create a new database named: event_planner then Select the new database on the left -> Click Import at the top & Select the file:sql/event_planner.sql and then Click Go
6. Confirm Database Settings
Open: includes/db.php & Make sure these match your XAMPP setup:  
$host = "localhost";  
$user = "root";  
$pass = "";    
$dbname = "event_planner";
7. Run the Application

This project was created for CSE 389 – Web System Architecture and is intended for academic use.

# Library Management System

A full-stack Library Management System built with **HTML, CSS, JavaScript, PHP**, and **MySQL**.

---

## Features

- User registration and login
- Admin dashboard to manage books and users
- Book issue and return system
- Fine calculation on late returns
- Student dashboard to view issued books
- AJAX-based issue request system
- Book recommendations

---

## Project Structure

librarydbms/
├── admin/ # Admin interface
├── css/ # Stylesheets
├── database/ # Contains database .sql file
│ └── librarydb.sql
├── dbconnect.php # MySQL connection script
├── index.php # Landing page
├── login.php # Login logic
├── signup.php # User registration
├── student_dashboard.php # Student panel
├── view_books.php # View available books
└── ... (other PHP and JS files)


##  How to Set It Up (Locally Using XAMPP)

1. Download and install [XAMPP](https://www.apachefriends.org/index.html)
2. Copy the `librarydbms` folder to: C:/xampp/htdocs/
3. Start **Apache** and **MySQL** from XAMPP Control Panel
4. Open a browser and go to:  http://localhost/phpmyadmin
5. Create a new database: librarydb
6. Import the SQL file:
- Go to the **Import** tab
- Choose: `librarydbms/database/librarydb.sql`
- Click **Go**
7. Run the project by visiting:
http://localhost/librarydbms

## Authors
- **Vinayak Mishra** 
- **Sagun Kumar Behera**



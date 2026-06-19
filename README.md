# Student Information Management System — Tanzania

## Project Title
**Student Information Management System for Primary and Secondary Schools in Tanzania**

## Degree Program
Bachelor of Science in Computer Science

## Group Number
**Group [YOUR GROUP NUMBER]**

---

## Project Overview

This is a web-based Student Information Management System (SIMS) developed using PHP and MySQL. It is designed for managing student records in primary and secondary schools across Tanzania. The system enables school administrators and teachers to efficiently register students, view their records, and search for specific students by registration number or name.

The system was developed as part of the **CP 222 – Open Source Technologies** assignment, covering PHP programming, Git version control, and GitHub collaboration.

---

## Key Features

- **User Authentication** – Secure login system with session management
- **User Management Module** – Role-based access control (Admin, Teacher, Viewer)
- **Student Registration** – Register primary and secondary school students with full details
- **Display Student Records** – View all student records with filtering and pagination
- **Search by Registration Number** – Quickly find any student using their registration number
- **Edit & Delete Records** – Update or remove student records (role-restricted)
- **Audit Trail** – System logs all major actions for accountability
- **Responsive Design** – Works on desktop and mobile devices

---

## Technologies Used

| Technology | Purpose |
|------------|---------|
| PHP 8.x | Server-side scripting and business logic |
| MySQL | Database management |
| HTML5 / CSS3 | Front-end structure and styling |
| Git | Version control |
| GitHub | Remote repository and collaboration |
| Apache (XAMPP/LAMP) | Local development server |

---

## Installation Steps

### Requirements
- XAMPP, LAMP, or WAMP stack (PHP 8.0+, MySQL 5.7+, Apache)
- Git

### Step 1 – Clone the Repository
```bash
git clone https://github.com/[YOUR-USERNAME]/OpenSource_Assignment_CS_Group[NUMBER].git
cd OpenSource_Assignment_CS_Group[NUMBER]
```

### Step 2 – Set Up the Database
1. Start Apache and MySQL from XAMPP/LAMP control panel
2. Open phpMyAdmin at `http://localhost/phpmyadmin`
3. Create a new database named `student_mgmt_db`
4. Import the provided `database.sql` file:
   - Click **Import** → Choose `database.sql` → Click **Go**

### Step 3 – Configure Database Connection
Open `includes/db.php` and update the credentials if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // your MySQL password
define('DB_NAME', 'student_mgmt_db');
```

### Step 4 – Run the Application
Place the project folder inside your web server root:
- XAMPP (Windows): `C:/xampp/htdocs/student_mgmt/`
- LAMP (Linux): `/var/www/html/student_mgmt/`

Open your browser and navigate to:
```
http://localhost/student_mgmt/
```

### Default Login Credentials
| Username | Password | Role |
|----------|----------|------|
| `admin` | `Admin@1234` | Admin |

---

## Project Structure

```
student_mgmt/
├── index.php              # Entry point (redirects to login)
├── login.php              # Authentication page
├── logout.php             # Session termination
├── dashboard.php          # System overview & stats
├── register_student.php   # Student registration form
├── students.php           # Display all student records
├── view_student.php       # View individual student profile
├── edit_student.php       # Edit student record
├── delete_student.php     # Delete student (Admin only)
├── search.php             # Search by registration number / name
├── users.php              # User management module
├── database.sql           # Database setup script
├── css/
│   └── style.css          # Main stylesheet
└── includes/
    ├── db.php             # Database connection
    └── functions.php      # Utility functions (sanitize, redirect, auth)
```

---

## Git Commands Used

```bash
# Initialize repository
git init

# Check status of working directory
git status

# Stage files for commit
git add .
git add <filename>

# Commit changes with message
git commit -m "Initial project setup and database schema"
git commit -m "Added student registration form"
git commit -m "Implemented student display and search functionality"
git commit -m "Added user management module"
git commit -m "Implemented edit and delete student features"
git commit -m "Added CSS styling and responsive design"
git commit -m "Merged development branch - added search by name feature"

# Create and switch to a new branch
git branch development
git checkout development
# OR combined:
git checkout -b development

# Merge branch into main
git checkout main
git merge development

# Connect to remote repository
git remote add origin https://github.com/[YOUR-USERNAME]/OpenSource_Assignment_CS_Group[NUMBER].git

# Push to GitHub
git push -u origin main
git push origin development

# View commit history
git log --oneline

# View all branches
git branch -a
```

---

## GitHub Repository

🔗 **[https://github.com/[YOUR-USERNAME]/OpenSource_Assignment_CS_Group[NUMBER]](https://github.com)**

---

## Group Members

| # | Full Name | Registration Number | Role |
|---|-----------|---------------------|------|
| 1 | [Member 1] | [Reg. No.] | Team Leader / Backend Dev |
| 2 | [Member 2] | [Reg. No.] | Database Design |
| 3 | [Member 3] | [Reg. No.] | Frontend / CSS |
| 4 | [Member 4] | [Reg. No.] | Documentation |

---

## Course Information
- **Course:** CP 222 – Open Source Technologies
- **Deadline:** 18th June 2026

---

*Developed with ❤️ by Group [02] | University of Dodoma | 2026*

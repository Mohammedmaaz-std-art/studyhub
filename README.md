# StudyHub

A role-based study management system built using PHP, MySQL, JavaScript, HTML, and CSS.

---

## Features

- Subject management
- Task tracking
- Quiz system
- Progress tracking
- Focus timer

---

## Technologies Used

- PHP
- MySQL
- JavaScript
- HTML/CSS
- Laragon

---

## Project Setup

### 1. Clone Repository

```bash
git clone https://github.com/Mohammedmaaz-std-art/studyhub.git
```

---

### 2. Move Project to Laragon www Folder

Example:

```text
C:\laragon\www\(folder
```

---

### 3. Start Laragon

Start:

- Apache
- MySQL

---

### 4. Create Database

Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Create database:

```text
studyhub
```

---

### 5. Import SQL File

Import the provided SQL file into the database.

Example:

```text
studyhub.sql
```

---

### 6. Configure Database Connection

Open:

```text
config/db.php
```

Update credentials if needed:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "studyhub";
```

---

### 7. Run Project

Open browser:

```text
http://localhost/studyhub
```

---

## Folder Structure

```text
studyhub/
│
├── auth/
├── subjects/
├── tasks/
├── quiz/
├── progress/
├── assets/
└── config/
```

---

## Future Improvements

- User authentication
- Auto deduction Role-based
- Notifications and messages And email reminders 
- Analytics
- Apps & social media blocking and usage control over Smartphone
- AI study assistant
- Behavioral tracking and improvement solution Deduction

---

## Author

Mohammed Maaz

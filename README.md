# CybAware

<img height="75" align="right" alt="CybAware QR Code" src="https://github.com/user-attachments/assets/b07f28ac-f31d-43f3-8159-8dda8aa1c543" />

<p align="justify">Final project for the Project Design & Management course at T.A. Marryshow Community College. An interactive, web-based educational game developed to raise cybersecurity awareness, featuring gamified lessons on password safety and phishing detection, built with HTML5, CSS3, JavaScript, and PHP.</p>

<img width="1200" height="947" alt="image" src="https://github.com/user-attachments/assets/5f9c5cd6-c9b1-445e-95a5-c71d3b5459ac" />

<img width="960" height="947" alt="image" src="https://github.com/user-attachments/assets/c8ccf39b-6745-42b6-8de9-a3b3bffe3712" />

<img width="1440" height="1015" alt="image" src="https://github.com/user-attachments/assets/d496e591-106b-4e79-bbfe-d49cc8a60df3" />

## About

CybAware is a browser-based educational game that teaches students and young adults in Grenada how to stay safe online. Players create an account, complete four game levels across two missions, and earn a personalized Certificate of Achievement.

The project was developed by Ahndre Walters and Joshua Evelyn as a capstone project for PMT226 at T.A. Marryshow Community College, under the guidance of lecturer Mrs. Chrislyn Charles-Williams.

## Missions

**Password Fortress**
- Level 1 - Learn Security: Ten multiple-choice questions covering password security fundamentals including strength, hashing, two-factor authentication, and brute force attacks.
- Level 2 - Deeper Security: Create a unique, strong password for each of five company departments. Each password is scored by a strength algorithm and worth up to two points.

**Phishing Detective**
- Level 1 - Read Emails: Analyse ten realistic spoofed emails and classify each one as Legitimate or Phishing.
- Level 2 - Hunt Errors: Read a single phishing email and click on ten hidden errors with no visual hints. Errors include spelling mistakes, character substitutions, a fake date, a suspicious link, and a contradictory security notice.

Completing all four levels unlocks a personalized Certificate of Achievement that can be downloaded as a PDF.

## Features

- User registration, login, and password reset system
- Scores saved per user to a MySQL database and persisted across sessions
- Dynamic certificate showing Achievement or Participation status based on completion
- PDF certificate export using html2canvas and jsPDF
- Interactive avatar picker with ten characters featuring real-time cursor-tracking eyes
- Background music with seamless cross-page playback continuity
- Fully responsive design for desktop and mobile browsers
- Keyboard shortcuts on quiz levels for faster navigation

## Tech Stack

- Frontend: HTML5, CSS3, JavaScript (vanilla)
- Backend: PHP
- Database: MySQL hosted on Aiven Cloud
- Libraries: html2canvas, jsPDF, Font Awesome, Google Fonts
- Version Control: GitHub

## Project Structure
```
CybAware/
├── config/
│   └── database.php
├── css/
│   └── styles.css
├── images/
├── includes/
│   ├── navigation.php
│   └── footer.php
├── music/
├── sql/
│   └── CybAwareDB.sql
├── .env
├── .gitignore
├── about.php
├── certificate.php
├── contact.php
├── Dockerfile
├── game.php
├── index.php
├── login.php
├── logout.php
├── password-game-1.php
├── password-game-2.php
├── passwordreset.php
├── phishing-game-1.php
├── phishing-game-2.php
├── register.php
└── terms.php
```

## Database Setup

The database schema is located in `sql/CybAwareDB.sql`. It creates three tables.

- `users` stores account details with bcrypt-hashed passwords
- `game_scores` stores one score record per user per game level
- `game_levels` stores additional level metadata including clues found and completion status

To set up the database, import `CybAwareDB.sql` into your MySQL server, then update the connection constants in `config/database.php` to match your environment.
```php
define('DB_SERVER', '');
define('DB_NAME', '');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_PORT', '');
```

## How to Run Locally

1. Clone the repository
2. Place the project folder in your local server directory (e.g. htdocs for XAMPP)
3. Import `sql/CybAwareDB.sql` into your MySQL server
4. Update the database constants in `config/database.php`
5. Start your local server and open the project in a browser

The application requires a PHP-enabled server and a MySQL database. No additional dependencies need to be installed.

## How to Play

1. Register for a free account on the register page
2. Sign in using your email address or your name
3. Go to the Game page and select any level to begin
4. Complete all four levels to unlock your Certificate of Achievement
5. Visit the Certificate page and click Save as PDF to download it

## Contact

For questions or feedback, reach out at cybaware@proton.me

## Course Information

- Institution: T.A. Marryshow Community College
- Department: Information Technology
- Course: Project Design & Management PMT226
- Developers: Ahndre Walters & Joshua Evelyn
- Lecturer: Mrs. Chrislyn Charles-Williams
- Year: 2025 - 2026

## License

<strong>[&copy; 2026 Ahndre Walters and Joshua Evelyn](https://github.com/AhndreWalters/CybAware/blob/main/LICENSE) · CybAware · TAMCC Project Design & Management Course · College Course Capstone Project</strong>

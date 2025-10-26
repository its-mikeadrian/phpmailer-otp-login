# phpmailer-otp-login

Login with Email OTP using PHPMailer it is a PHP template for email-based OTP authentication. It demonstrates core security practices like encrypted sessions, CSRF protection, and rate limiting but is not production-ready. Use it as a learning or prototype template, and adjust the security and UI to fit your own requirements.

## 📬 Contact
For business inquiries, please reach out via email: mikeadrian123456@gmail.com

## 📚 Usage

- Register a user → login → check your email for OTP → verify → welcome.
- “Resend OTP” is rate-limited and attempts are tracked.
- Tick “Remember Me” to stay logged in (rotating tokens).

## 🔎 Example Output
Sample OTP email body:

Subject: Your OTP Login Code
Hi <username>, your OTP is: 123456 (expires in 5 minutes)

## 🧠 Why / Motivation
This project helps developers especially beginners implement email OTP login with Gmail. It provides a clean, readable template with minimal boilerplate, strong security defaults, and clear folder structure (public/, src/, config/, database/) to make adoption easy.

## 🤝 Contributing
Contributions are welcome! Feel free to submit pull requests or open issues to help improve security, user experience, documentation, and examples. If you find this project useful, please consider giving it a ⭐️ on GitHub!

## ✨ Features
- 🔐 Encrypted sessions (AES-256-GCM) stored in DB
- 📧 Email OTP login with resend throttle & attempt limits
- 🛡️ Hardened sessions, CSRF tokens, safe cookie defaults
- 🧠 “Remember Me” with token rotation & hijack protection
- ⚙️ Simple `.env` config with optional encrypted secrets

## 🚀 Installation
1. **Download and Install XAMPP and ensure PHP 8.2+**:
   - [XAMPP Download](https://www.apachefriends.org/index.html)
   - Follow the installation instructions for your OS.

2. **Clone or Download the Repository**:
   - Use Git:
     ```bash
     git clone <repository-url>
     ```
   - Or download and extract the ZIP file.

3. **Copy Project Files to XAMPP**:
   - Copy the files to the `htdocs` directory of XAMPP (default location: `C:\xampp\htdocs\`).

4. **Open any code editor (e.g, Visual Studio Code)**:
   - create new file '.env' it should be placed on the project root "C:\xampp\htdocs\phpmailer-otp-login" .
   - Check the .env configuration below for the instruction

4. ** Create Gmail SMTP for SMTP_PASSWORD (App Password)**:
   - Enable 2-Step Verification in your Google Account: `https://myaccount.google.com/security` → 2-Step Verification → Turn On
   - Then go to [Gmail Security app](https://myaccount.google.com/apppasswords?pli=1&rapt=AEjHL4P5hvEUyT81E6ElyOR9oYJ5dgxOqsDeQEo7xY2OvKAyBqWZzGjU_8gkU7C823w7v98q6Hube3TJxBOB1VCbe-dHIeA20uGyDDj4ngdWTqjkgi8TWeU) enter an app name (e.g, login) then click create after that a 16-character password wil be shown just copy it and put it on `.env` settings insert it on SMTP_PASSWORD='insert here"
   
4. **Composer and Dependencies**:
   - Install Composer for Windows: download `Composer-Setup.exe` from `https://getcomposer.org/download/` and let it detect `php.exe` (usually `C:\xampp\php\php.exe`). Verify just type `composer --version` in PowerShell.
   - In the project root, install dependencies: on terminal or powershell : first type `cd C:\xampp\htdocs\phpmailer-otp-login` to go to the project root and hit enter then type `composer install` then enter
   - Then check if the dependencies installation is success by checking if the vendor folder is already placed on the project root

6. **Start the XAMPP Server**:
   - Open the XAMPP and start `Apache` and `MySQL`
   
4. **Set Up Database**:
   - Open your browser and go to `http://localhost/phpmyadmin`.
   - Create a database and name it "otp".
   - Import the provided SQL file its placed on `phpmailer-otp-login\database ` login.sql.

7. **Access the Application**:
   - Open your browser and go to `http://localhost/<project-folder-name>`.

## 🔧 .env Configuration
Create `.env` at project root:
```
# Insert these on .env

# SMTP server settings
SMTP_HOST=smtp.gmail.com
SMTP_AUTH=true
SMTP_USERNAME='youremail@gmail.com'

#encrypted password. (leave empty when using plaintext variant)
SMTP_PASSWORD_ENC=
SMTP_IV=

# Plaintext fallback (leave empty when using encrypted variant)
SMTP_PASSWORD='your-smtp-password'

# Encryption mode:
SMTP_ENCRYPTION=STARTTLS
SMTP_PORT=587

# TLS certificate verification options
SMTP_VERIFY_PEER=false
SMTP_VERIFY_PEER_NAME=false
SMTP_ALLOW_SELF_SIGNED=true

# From address and name
SMTP_FROM_ADDRESS='youremail@gmail.com' 
SMTP_FROM_NAME=otp


# Optional: SMTP test configuration
SMTP_TEST_DEBUG=2
SMTP_TEST_TO=youremail@gmail.com

# Database configuration
DB_HOST=localhost
DB_USERNAME=root
DB_PASSWORD=
DB_NAME=otp
```
# CrowdFunding Platform

A PHP-based crowdfunding platform for social projects, similar to GoFundMe. This platform allows users to create fundraising campaigns and make donations to support various social initiatives.

## Features

- User registration and authentication
- Project creation with image upload
- Project browsing with filtering and search
- Donation system
- User profiles with project and donation history
- Responsive design using Bootstrap
- Secure password hashing
- SQL injection prevention using prepared statements
- XSS prevention using htmlspecialchars

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

## Installation

1. Clone the repository to your web server directory:
```bash
git clone https://github.com/yourusername/crowdfunding.git
```

2. Create a MySQL database and import the schema:
```bash
mysql -u root -p
CREATE DATABASE crowdfunding;
exit;
mysql -u root -p crowdfunding < database/schema.sql
```

3. Configure the database connection:
   - Open `config/database.php`
   - Update the database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'crowdfunding');
     ```

4. Create an uploads directory and set permissions:
```bash
mkdir uploads
chmod 777 uploads
```

5. Access the website through your web browser:
```
http://localhost/crowdfunding
```

## Directory Structure

```
crowdfunding/
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── includes/
│   ├── header.php
│   └── footer.php
├── uploads/
├── index.php
├── login.php
├── register.php
├── logout.php
├── create-project.php
├── project.php
├── projects.php
└── profile.php
```

## Security Features

- Password hashing using PHP's password_hash()
- Prepared statements for all database queries
- Input validation and sanitization
- XSS prevention
- Session management
- File upload validation

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Bootstrap for the frontend framework
- Font Awesome for icons
- GoFundMe for inspiration 
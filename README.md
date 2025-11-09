# TechWave Blog Application

A modern, feature-rich blog platform built with PHP, MySQL, and vanilla JavaScript. TechWave offers a beautiful user interface with markdown support, category management, and social features.

## Features

-  **Modern Dark UI** - Beautiful, responsive design with smooth animations
-  **Markdown Editor** - Write posts using Markdown with live preview
-  **Category System** - Organize content by topics
-  **Comments** - Engage with readers
-  **Like System** - Show appreciation for posts
-  **Image Uploads** - Featured images for blog posts
-  **User Authentication** - Secure login and registration
-  **Fully Responsive** - Works on all devices
-  **Personal Dashboard** - Manage your posts easily

##  Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Editor**: SimpleMDE (Markdown)
- **Server**: Apache with mod_rewrite

##  Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Web browser

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/techwave-blog.git
   cd techwave-blog
   ```

2. **Create database**
   ```sql
   CREATE DATABASE techwave_blog;
   ```

3. **Import database schema**
   ```bash
   mysql -u root -p techwave_blog < database/schema.sql
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   nano .env
   ```
   
   Update with your credentials:
   ```env
   DB_HOST=localhost
   DB_NAME=techwave_blog
   DB_USER=your_username
   DB_PASS=your_password
   ```

5. **Set permissions**
   ```bash
   chmod 755 uploads/
   chmod 644 .env
   ```

6. **Configure web server**
   
   Point your web server to the project directory. For Apache, ensure `mod_rewrite` is enabled:
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

7. **Access the application**
   ```
   http://localhost/blog-application
   ```

##  Project Structure

```
blog-application/
├── api/                    # API endpoints
├── assets/                # Static assets (CSS, JS, images)
├── auth/                  # Authentication
├── blog/                  # Blog functionality
├── config/                # Configuration files
├── database/              # Database schema
├── includes/              # Reusable components
├── pages/                 # Application pages
├── uploads/               # User-uploaded images
├── .env.example          # Environment template
├── .gitignore            # Git ignore rules
├── .htaccess             # Apache configuration
└── index.php             # Entry point
```

## Usage

### Creating a Blog Post

1. Register/Login to your account
2. Navigate to Dashboard
3. Click "Create New Blog"
4. Choose a category
5. Write your content in Markdown
6. Upload a featured image (optional)
7. Click "Publish"

### Markdown Support

The editor supports full Markdown syntax:
- **Bold** and *italic* text
- Headers (H1-H6)
- Lists (ordered and unordered)
- Links and images
- Code blocks
- Blockquotes

## Security Features

- Password hashing with bcrypt
- SQL injection protection (prepared statements)
- XSS prevention (input sanitization)
- CSRF protection
- File upload validation
- Session security

## Demo Account

After setup, you can create an account or use demo credentials if provided in your schema.

## Screenshots

*Add screenshots of your application here*

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is open source and available under the [MIT License](LICENSE).

## Author

**Your Name**
- GitHub: [@minimuthu02](https://github.com/minimuthu02)

## Acknowledgments

- SimpleMDE for the Markdown editor
- Marked.js for Markdown parsing
- Font Awesome for icons (if used)

## Support

For support, email tmmst2@gmail.com or open an issue in the repository.

---

Made by [Minimuthu Thennakoon]

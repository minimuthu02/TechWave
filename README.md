TechWave Blog Application

TechWave is a modern, dynamic blogging platform built with PHP, MySQL, and Vanilla JavaScript.
It offers a smooth, dark-themed interface, a Markdown-powered editor, and essential social features — all designed to give users a seamless writing and reading experience.

Features

Modern Dark UI – Sleek, responsive, and mobile-friendly design

Markdown Editor – Write blog posts using Markdown with live preview

Category Management – Organize posts into different topics

Comment System – Readers can engage and interact

Like Functionality – Show appreciation for great content

Image Uploads – Add featured images to posts

User Authentication – Secure login and registration system

Fully Responsive – Works on desktops, tablets, and phones

Personal Dashboard – Manage and edit your posts easily

Tech Stack
Layer	Technology
Backend	PHP 7.4+
Database	MySQL 5.7+
Frontend	HTML5, CSS3, Vanilla JavaScript
Editor	SimpleMDE (Markdown Editor)
Server	Apache (with mod_rewrite enabled)
Installation Guide
1. Requirements

PHP 7.4 or higher

MySQL 5.7 or higher

Apache or Nginx Web Server

Any modern browser

2. Setup Steps
Clone the Repository
git clone https://github.com/YOUR_USERNAME/techwave-blog.git
cd techwave-blog

Create the Database

Login to phpMyAdmin (or use MySQL CLI) and run:

CREATE DATABASE techwave_blog;

Import the Schema
mysql -u root -p techwave_blog < database/schema.sql

Configure the Environment

Copy and edit the environment file:

cp .env.example .env


Update with your database credentials:

DB_HOST=host_name
DB_NAME=techwave_blog
DB_USER=your_username
DB_PASS=your_password
APP_ENV=production

Set Folder Permissions
chmod 755 uploads/
chmod 644 .env

Configure Apache (for local setup)

Enable mod_rewrite and restart Apache:

sudo a2enmod rewrite
sudo systemctl restart apache2


Add .htaccess to enable clean URLs:

RewriteEngine On
RewriteBase /blog-application/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]

Project Structure
blog-application/
│
├── api/             # API endpoints
├── assets/          # Static files (CSS, JS, images)
├── auth/            # Login, Register, Logout
├── blog/            # Blog CRUD and display
├── config/          # Configuration files
├── database/        # SQL schema and migrations
├── includes/        # Reusable PHP components
├── pages/           # Frontend pages
├── uploads/         # User-uploaded images
│
├── .htaccess        # URL rewriting rules
└── index.php        # Entry point

Usage
Create Your First Blog Post

Register or log in to your account

Open the Dashboard

Click Create New Blog

Write your post using Markdown

(Optional) Upload a featured image

Click Publish

Markdown Features

The built-in Markdown editor supports:

Bold and italic text

Inline code and code blocks

Headings (H1–H6)

Ordered and unordered lists

Links and embedded images

Blockquotes and horizontal rules

Security Highlights

Password hashing using bcrypt

SQL Injection protection with prepared statements

XSS filtering and sanitization

CSRF protection for form submissions

Secure session handling

File upload validation

Deployment (InfinityFree)

If you are hosting on InfinityFree, follow these steps:

Upload all project files to the /htdocs/blog-application/ directory

Update the .env file with your InfinityFree database credentials:

DB_HOST=sqlXXX.infinityfree.com
DB_NAME=epiz_XXXXXXX_techwave
DB_USER=epiz_XXXXXXX
DB_PASS=yourpassword


Set write permissions for the uploads/ folder (755)

Access your website at:

https://yourusername.infinityfreeapp.com/blog-application/

Author

Minimuthu Thennakoon
Email: tmmst2@gmail.com

GitHub: @minimuthu02

License

This project is licensed under the MIT License.
You are free to use, modify, and distribute it with proper attribution.

Acknowledgments

SimpleMDE – Markdown Editor

Marked.js – Markdown Parser

Font Awesome – Icons

Support

If you encounter issues or need help:

Email: tmmst2@gmail.com

Or open an issue on GitHub

Made by Minimuthu Thennakoon

-- ============================================
-- Blog Application Database Schema (Fixed & Final)
-- ============================================

-- Create the database
CREATE DATABASE IF NOT EXISTS blog_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE blog_db;

-- ============================================
-- Users Table
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Categories Table
-- ============================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default categories
INSERT INTO categories (name) VALUES
('Technology'), 
('Science'),
('Engineering & Innovation'),
('Research & Discoveries'),
('Tech Trends'),
('Industry'),
('DIY & Tutorials')
ON DUPLICATE KEY UPDATE id=id;

-- ============================================
-- Blog Posts Table (✅ Fixed category_id conflict)
-- ============================================
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NULL, -- ✅ FIXED: must allow NULL for ON DELETE SET NULL
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255) NULL COMMENT 'Path to featured blog image',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_created_at (created_at),
    INDEX idx_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Comments Table
-- ============================================
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table to store user likes on blog posts
CREATE TABLE IF NOT EXISTS blog_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_id INT NOT NULL,
    user_id INT NOT NULL,
    liked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);



-- ============================================
-- Seed Users
-- ============================================

-- Seed admin user (password: 'password')
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@blog.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- Seed sample user (password: 'password')
INSERT INTO users (username, email, password, role) VALUES 
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user')
ON DUPLICATE KEY UPDATE id=id;

-- ============================================
-- Seed Blog Posts
-- ============================================

-- Welcome blog post
INSERT INTO blog_posts (user_id, category_id, title, content, image) VALUES 
(
    (SELECT id FROM users WHERE username='admin'),
    (SELECT id FROM categories WHERE name='Technology'),
    'Welcome to TechWave Blog!',
    '# Welcome to TechWave! 🚀

This is your starting point for exploring the latest in **technology** and **science**.

## What You Can Do Here

- 📝 **Create Blog Posts**: Share your insights and ideas with markdown support
- 💬 **Engage with Comments**: Discuss and interact with other members
- 🖼️ **Add Featured Images**: Make your posts visually appealing
- 🎨 **Use Markdown**: Format your content beautifully

## Features

### Rich Markdown Support
You can use various markdown features:
- **Bold text**
- *Italic text*
- Lists and nested lists
- Code blocks
- Links and images

### User-Friendly Interface
Our modern, responsive design ensures a great experience on all devices.

### Secure & Fast
Built with security and performance in mind.

---

**Get Started Today!** Create your first blog post and join our community of tech enthusiasts.

Happy blogging! 💻✨',
    NULL
)
ON DUPLICATE KEY UPDATE id=id;

-- Markdown guide blog post
INSERT INTO blog_posts (user_id, category_id, title, content, image) VALUES 
(
    (SELECT id FROM users WHERE username='john_doe'),
    (SELECT id FROM categories WHERE name='Technology'),
    'Getting Started with Markdown',
    '# Markdown Guide

Markdown is a lightweight markup language that you can use to add formatting elements to plaintext text documents.

## Basic Syntax

### Headers
Use `#` for headers. More `#` symbols create smaller headers.

### Emphasis
- *Italic* with single asterisks
- **Bold** with double asterisks
- ***Bold and italic*** with triple asterisks

### Lists
Create unordered lists with `-` or `*`:
- Item 1
- Item 2
- Item 3

Ordered lists with numbers:
1. First item
2. Second item
3. Third item

### Links and Images
- Link: `[text](url)`
- Image: `![alt text](image url)`

### Code
Inline `code` with backticks.

```javascript
// Code block with triple backticks
function hello() {
    console.log("Hello World!");
}
```',
    NULL
)
ON DUPLICATE KEY UPDATE id=id;

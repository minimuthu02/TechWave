document.addEventListener('DOMContentLoaded', async () => { 
    // =========================
    // Mobile Navigation Toggle
    // =========================
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');

    if (navToggle) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }

    document.addEventListener('click', (event) => {
        if (navMenu && navMenu.classList.contains('active')) {
            if (!navToggle.contains(event.target) && !navMenu.contains(event.target)) {
                navMenu.classList.remove('active');
            }
        }
    });

    // =========================
    // Page Transition Animation
    // =========================
    const page = document.querySelector(".page-transition");
    if (page) {
        setTimeout(() => {
            page.classList.add("visible");
        }, 50);
    }

    // =========================
    // Category Dropdown
    // =========================
    const categoriesBtn = document.getElementById('categoriesBtn');
    const categoryDropdown = document.getElementById('categoryDropdown');
    const categoryList = document.getElementById('categoryList');

    if (categoriesBtn && categoryDropdown && categoryList) {
        try {
            const response = await fetch('/api/blog/categories.php');
            const data = await response.json();
            if (data.success && Array.isArray(data.categories)) {
                renderCategories(data.categories);
            } else {
                categoryList.innerHTML = '<div style="padding: 1rem; text-align: center; color: var(--text-secondary);">No categories available</div>';
            }
        } catch (error) {
            console.error('Error loading categories:', error);
            categoryList.innerHTML = '<div style="padding: 1rem; text-align: center; color: red;">Failed to load categories</div>';
        }

        function renderCategories(cats) {
            const header = document.querySelector('.category-dropdown-header');
            if (header) {
                header.textContent = cats.length > 0 
                    ? `Categories (${cats.length})` 
                    : 'No categories available';
            }

            categoryList.innerHTML = cats.map(cat => `
                <a href="/pages/category.php?id=${cat.id}" class="category-dropdown-item">
                    <span class="category-info">
                        <span class="category-name">${escapeHtml(cat.name)}</span>
                        <span class="category-count">${cat.blog_count} post${cat.blog_count !== '1' ? 's' : ''}</span>
                    </span>
                </a>
            `).join('');
        }

        categoriesBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            categoryDropdown.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (!categoriesBtn.contains(e.target) && !categoryDropdown.contains(e.target)) {
                categoryDropdown.classList.remove('show');
            }
        });

        categoryList.addEventListener('click', () => {
            categoryDropdown.classList.remove('show');
        });
    }
});

// =========================
// Utility Functions
// =========================
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    if (!notification) {
        console.error('Notification element not found');
        return;
    }
    notification.textContent = message;
    notification.className = `notification ${type} show`;
    setTimeout(() => {
        notification.classList.remove('show');
    }, 4000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('en-US', options);
}

function truncateText(text, maxLength = 150) {
    return text.length <= maxLength ? text : text.substring(0, maxLength) + '...';
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
}

function confirmAction(message) {
    return confirm(message);
}



# MINALOC eLearning Platform

A customized Moodle 4.3.3+ based Learning Management System (LMS) for Rwanda's Ministry of Local Government (MINALOC). This platform provides comprehensive e-learning capabilities with custom user registration, demographic data collection, and analytics features.

## 📋 Table of Contents

- [Overview](#overview)
- [System Requirements](#system-requirements)
- [Quick Start](#quick-start)
- [Architecture](#architecture)
- [Custom Features](#custom-features)
- [Development Guide](#development-guide)
- [Docker Environment](#docker-environment)
- [Database Management](#database-management)
- [Frontend Development](#frontend-development)
- [Testing](#testing)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)

## 🎯 Overview

**Project:** MINALOC eLearning Platform
**Based on:** Moodle 4.3.3+ (Build: 20240215, Branch: 403)
**PHP Version:** 8.1
**Database:** MariaDB
**Web Server:** Apache
**Containerization:** Docker & Docker Compose

This platform extends Moodle's core functionality with Rwanda-specific features including:
- Custom user registration with demographic data (province, district, sector, cell, village)
- Enhanced user profile management
- Custom themes (Adaptable, Space)
- Real-time analytics dashboard
- Integration with Rwanda's administrative divisions

## 🖥️ System Requirements

### For Docker Deployment (Recommended)
- **Docker:** Version 20.10 or higher
- **Docker Compose:** Version 2.0 or higher
- **RAM:** Minimum 4GB, recommended 8GB
- **Disk Space:** Minimum 10GB free space
- **Operating System:** Linux, macOS, or Windows 10/11 with WSL2

### For Local Development
- **PHP:** 8.1 with extensions: mysqli, gd, intl, mbstring, xml, zip, curl, openssl
- **Node.js:** 16.x or 18.x
- **npm:** 8.x or higher
- **MariaDB:** 10.6 or higher
- **Apache:** 2.4 or higher

## 🚀 Quick Start

### 1. Clone the Repository

```bash
git clone <repository-url>
cd minaloc-elearning
```

### 2. Start the Docker Environment

```bash
docker compose up -d
```

### 3. Access the Platform

Open your browser and navigate to:
```
http://localhost:8086
```

### 4. Default Credentials

- **Database:**
  - Host: `db` (container name)
  - Database: `moodle`
  - Username: `root`
  - Password: `gov@123`

- **Moodle Admin:** (Set during initial installation)

## 🏗️ Architecture

### Directory Structure

```
minaloc-elearning/
│
├── elearning/                    # Moodle application root
│   ├── admin/                    # Administration tools
│   ├── auth/                     # Authentication plugins
│   ├── blocks/                   # Sidebar blocks (~49 blocks)
│   │   └── realdashboard/        # Custom analytics dashboard
│   ├── local/                    # Custom local plugins
│   │   ├── additionaluserdetails/  # Custom registration fields
│   │   └── userdetails/          # User details management
│   ├── theme/                    # Themes
│   │   ├── adaptable/            # Custom theme
│   │   ├── space/                # Custom theme
│   │   ├── boost/                # Default Moodle theme
│   │   └── classic/              # Alternative theme
│   ├── mod/                      # Activity modules (assignments, quizzes, etc.)
│   ├── lib/                      # Core libraries and APIs
│   ├── course/                   # Course management
│   ├── user/                     # User management
│   ├── lang/                     # Language packs
│   └── config.php                # Main configuration (not in Git)
│
├── moodledata/                   # Data directory (excluded from Git)
│   ├── cache/                    # Cache files
│   ├── filedir/                  # Uploaded files
│   ├── sessions/                 # Session data
│   └── temp/                     # Temporary files
│
├── db/                           # MariaDB data volume (excluded from Git)
│
├── init-scripts/                 # Database initialization
│   └── init.sql                  # Initial database schema
│
├── compose.yaml                  # Docker Compose configuration
├── Dockerfile                    # Moodle container image
├── php.ini-development           # PHP configuration
├── .gitignore                    # Git ignore rules
└── README.md                     # This file
```

### Core Technologies

- **Backend:** PHP 8.1 (Moodle framework)
- **Database:** MariaDB 10.6+
- **Frontend:** JavaScript (AMD/RequireJS), HTML5, CSS3/SCSS
- **Build Tools:** Grunt, npm
- **Containerization:** Docker, Docker Compose
- **Version Control:** Git

## ✨ Custom Features

### 1. Additional User Details Plugin (`local/additionaluserdetails`)

**Location:** `elearning/local/additionaluserdetails/`
**Version:** 2025072200
**Entry Point:** `register.php`

Custom user registration form that collects Rwanda-specific demographic data:
- Province
- District
- Sector
- Cell
- Village
- Gender
- Date of Birth
- Education Level
- Employment Status

**Frontend:** React-based with Tailwind CSS
- Source: `frontend/src/`
- Build: `frontend/dist/`
- Data: `frontend/src/scripts/demographic/rwanda-districts-data.json`

### 2. User Details Management (`local/userdetails`)

**Location:** `elearning/local/userdetails/`
**Version:** 2025072200

Manages and displays additional user profile information integrated with Moodle's user system.

### 3. Real Dashboard Block (`blocks/realdashboard`)

**Location:** `elearning/blocks/realdashboard/`

Custom analytics dashboard providing:
- User enrollment statistics
- Learning progress metrics
- Course completion rates
- Real-time data visualization

**Frontend:** React-based
- Source: `frontend/`
- Build artifacts excluded from Git

### 4. Custom Themes

**Adaptable Theme:** Modern, responsive theme with customization options
**Space Theme:** Alternative theme with unique styling

## 💻 Development Guide

### Docker Environment

#### Starting the Environment

```bash
# Start all services in detached mode
docker compose up -d

# View logs
docker compose logs -f

# View specific service logs
docker compose logs -f moodle
docker compose logs -f db
```

#### Stopping the Environment

```bash
# Stop services
docker compose stop

# Stop and remove containers
docker compose down

# Stop and remove containers, volumes, and networks
docker compose down -v
```

#### Accessing Containers

```bash
# Access Moodle container shell
docker compose exec moodle bash

# Access database container
docker compose exec db bash

# Access database CLI
docker compose exec db mariadb -uroot -p'gov@123' moodle
```

### Moodle Configuration

**Configuration File:** `elearning/config.php` (not in Git - create from `config-dist.php`)

**Key Settings:**
- **Database Host:** `db` (Docker container name)
- **WWW Root:** `http://localhost:8086`
- **Data Directory:** `/var/www/moodledata` (inside container)
- **Table Prefix:** `mdl_`

**Enable Debugging (Development):**

Edit `elearning/config.php`:
```php
$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;
```

**Disable Caching (Development):**

```php
$CFG->cachejs = false;
$CFG->langstringcache = false;
```

### Moodle CLI Commands

All CLI commands run inside the Moodle container:

```bash
docker compose exec moodle bash
cd /var/www/html
```

#### Essential Commands

**Install/Upgrade Database:**
```bash
php admin/cli/install_database.php
php admin/cli/upgrade.php --non-interactive
```

**Clear Caches:**
```bash
php admin/cli/purge_caches.php
```

**Run Cron (Scheduled Tasks):**
```bash
php admin/cli/cron.php
```

**Reset Admin Password:**
```bash
php admin/cli/reset_password.php
```

**Plugin Management:**
```bash
# Uninstall plugin
php admin/cli/uninstall_plugins.php --plugins=<plugin_name> --run

# List plugins
php admin/cli/uninstall_plugins.php --show-all
```

**Maintenance Mode:**
```bash
# Enable maintenance mode
php admin/cli/maintenance.php --enable

# Disable maintenance mode
php admin/cli/maintenance.php --disable
```

## 🎨 Frontend Development

Moodle uses **Grunt** for frontend asset compilation.

### Setup

```bash
# Install dependencies (inside Moodle container or on host)
npm install
```

### Build Commands

**Compile JavaScript (AMD modules):**
```bash
npx grunt amd
```

**Compile SCSS to CSS:**
```bash
npx grunt css
```

**Watch for Changes:**
```bash
npx grunt watch
```

**Run Linters:**
```bash
npx grunt eslint      # JavaScript
npx grunt stylelint   # CSS/SCSS
```

**Build Everything:**
```bash
npx grunt
```

### JavaScript Architecture

- **AMD Modules:** `*/amd/src/*.js` → compiled to `*/amd/build/*.js`
- **Module Loader:** RequireJS/AMD
- **Transpilation:** Babel for ES6+ support
- **YUI Modules:** Legacy format in `*/yui/src/**/*.js`

### CSS/SCSS Architecture

- **Theme SCSS:** `theme/*/scss/**/*.scss`
- **Component Styles:** `*/styles.css` or within theme directories
- **Compiled CSS:** Cached in `moodledata/localcache`

### Custom Plugin Frontend Development

**Additional User Details Plugin:**

```bash
# Navigate to plugin frontend
cd elearning/local/additionaluserdetails/frontend

# Install dependencies
npm install

# Development mode with hot reload
npm run dev

# Build for production
npm run build
```

**Real Dashboard Block:**

```bash
# Navigate to block frontend
cd elearning/blocks/realdashboard/frontend

# Install dependencies
npm install

# Build
npm run build
```

## 🗄️ Database Management

### Direct Database Access

```bash
# Connect to database
docker compose exec db mariadb -uroot -p'gov@123' moodle
```

### Common SQL Queries

```sql
-- View all users
SELECT id, username, firstname, lastname, email FROM mdl_user WHERE deleted = 0;

-- View all courses
SELECT id, fullname, shortname, visible FROM mdl_course;

-- View user enrollments
SELECT u.username, c.fullname, r.shortname as role
FROM mdl_user_enrolments ue
JOIN mdl_user u ON u.id = ue.userid
JOIN mdl_enrol e ON e.id = ue.enrolid
JOIN mdl_course c ON c.id = e.courseid
JOIN mdl_role_assignments ra ON ra.userid = u.id
JOIN mdl_role r ON r.id = ra.roleid;

-- View additional user details
SELECT * FROM mdl_local_additionaluserdetails;
```

### Moodle Database API (PHP)

```php
global $DB;

// Get records
$users = $DB->get_records('user', ['deleted' => 0]);

// Get single record
$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

// Insert record
$id = $DB->insert_record('tablename', $dataobject);

// Update record
$DB->update_record('tablename', $dataobject);

// Execute SQL (use sparingly)
$DB->execute('UPDATE {user} SET deleted = 1 WHERE id = ?', [$userid]);
```

**Note:** All table names use `{tablename}` placeholders which resolve to `mdl_tablename`.

### Backup and Restore

**Backup Database:**
```bash
docker compose exec db mariadb-dump -uroot -p'gov@123' moodle > backup.sql
```

**Restore Database:**
```bash
docker compose exec -T db mariadb -uroot -p'gov@123' moodle < backup.sql
```

## 🧪 Testing

### PHPUnit Tests

**Initialize PHPUnit:**
```bash
docker compose exec moodle bash
php admin/tool/phpunit/cli/init.php
```

**Run Tests:**
```bash
# All tests
vendor/bin/phpunit

# Specific test suite
vendor/bin/phpunit --testsuite core_dml_testsuite

# Component tests
vendor/bin/phpunit local/userdetails/tests/

# Single test file
vendor/bin/phpunit local/userdetails/tests/userdetails_test.php
```

### Behat (Acceptance) Tests

**Initialize Behat:**
```bash
php admin/tool/behat/cli/init.php
```

**Run Behat Tests:**
```bash
vendor/bin/behat --config /var/www/moodledata/behatrun/behat/behat.yml
```

### Manual Testing Checklist

- [ ] User registration with additional details
- [ ] User login and profile access
- [ ] Course enrollment and access
- [ ] Dashboard analytics display
- [ ] Theme switching
- [ ] Mobile responsiveness
- [ ] File uploads
- [ ] Quiz and assignment submission

## 📦 Deployment

### Production Checklist

1. **Security:**
   - [ ] Change database password from `gov@123`
   - [ ] Update `$CFG->wwwroot` to production domain
   - [ ] Enable HTTPS: Set `$CFG->sslproxy = true`
   - [ ] Disable debugging: `$CFG->debug = 0`
   - [ ] Review file permissions

2. **Performance:**
   - [ ] Enable caching: `$CFG->cachejs = true`
   - [ ] Set up Redis/Memcached
   - [ ] Enable OPcache in PHP
   - [ ] Configure `$CFG->localcachedir`
   - [ ] Use CDN for static assets

3. **Backup:**
   - [ ] Set up automated database backups
   - [ ] Back up `moodledata/` directory
   - [ ] Back up configuration files
   - [ ] Test restore procedure

4. **Monitoring:**
   - [ ] Set up error logging
   - [ ] Configure cron jobs
   - [ ] Set up uptime monitoring
   - [ ] Configure email notifications

### Environment Variables

Create `.env` file for production:

```env
MYSQL_ROOT_PASSWORD=<strong_password>
MYSQL_DATABASE=moodle
MOODLE_WWWROOT=https://yourdomain.com
MOODLE_DATAROOT=/var/www/moodledata
```

### SSL/HTTPS Configuration

Update `elearning/config.php`:
```php
$CFG->wwwroot = 'https://yourdomain.com';
$CFG->sslproxy = true;
```

Configure reverse proxy (nginx/Apache) for SSL termination.

## 🔧 Troubleshooting

### Common Issues

**1. Permission Errors**

```bash
# Fix moodledata permissions
docker compose exec moodle chown -R www-data:www-data /var/www/moodledata
docker compose exec moodle chmod -R 02775 /var/www/moodledata
```

**2. Database Connection Failed**

- Check database container is running: `docker compose ps`
- Verify credentials in `elearning/config.php`
- Check database logs: `docker compose logs db`

**3. White Screen / 500 Error**

- Enable debugging in `config.php`
- Check PHP error logs: `docker compose logs moodle`
- Clear caches: `php admin/cli/purge_caches.php`

**4. Plugin Not Showing Up**

- Visit admin notifications: `http://localhost:8086/admin/index.php`
- Clear caches: `php admin/cli/purge_caches.php`
- Check plugin version.php file

**5. Frontend Assets Not Loading**

```bash
# Rebuild frontend assets
npx grunt amd
npx grunt css
php admin/cli/purge_caches.php
```

**6. Git Push Fails (Large Repository)**

The repository includes large files. To clean up:

```bash
# Remove large files from Git history
git filter-branch --tree-filter 'rm -rf moodledata' HEAD
git push --force
```

Or use Git LFS for large files.

### Logs Location

- **Moodle Logs:** `moodledata/` (visible in admin interface)
- **Apache Logs:** `docker compose logs moodle`
- **Database Logs:** `docker compose logs db`
- **PHP Error Log:** Check Docker logs

## 🤝 Contributing

### Development Workflow

1. Create a feature branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make changes and test thoroughly

3. Follow Moodle coding standards:
   - PSR-2 for PHP
   - ESLint for JavaScript
   - StyleLint for CSS/SCSS

4. Commit changes:
   ```bash
   git add .
   git commit -m "Description of changes"
   ```

5. Push and create pull request

### Coding Standards

**PHP:**
- Follow Moodle coding style
- Use type hints
- Document functions with PHPDoc
- Run: `php admin/cli/check_database_schema.php`

**JavaScript:**
- Use AMD module pattern
- Follow ESLint rules: `npx grunt eslint`
- Document with JSDoc comments

**CSS/SCSS:**
- Use BEM naming convention
- Follow StyleLint rules: `npx grunt stylelint`
- Keep specificity low

### Plugin Development

Standard plugin structure:
```
version.php           # Required: version and metadata
settings.php          # Admin settings page
lang/en/<plugin>.php  # Language strings
db/
  ├── install.xml     # Database schema
  ├── upgrade.php     # Database upgrades
  ├── events.php      # Event observers
  └── access.php      # Capability definitions
classes/              # Autoloaded classes (PSR-4)
tests/                # PHPUnit tests
amd/src/              # JavaScript (AMD modules)
scss/                 # SCSS stylesheets
```

## 📚 Additional Resources

- **Moodle Documentation:** https://docs.moodle.org
- **Moodle Developer Docs:** https://moodledev.io
- **Moodle Tracker:** https://tracker.moodle.org
- **Docker Documentation:** https://docs.docker.com
- **Grunt Documentation:** https://gruntjs.com

## 📄 License

This project is based on Moodle, which is licensed under the GNU General Public License v3.0.

## 👥 Support

For issues, questions, or contributions, please contact the MINALOC eLearning development team.

---

**Built with ❤️ for Rwanda's Ministry of Local Government (MINALOC)**

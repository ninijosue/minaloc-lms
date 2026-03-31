# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is the **Minaloc eLearning Platform**, a Moodle 4.3.3+ based learning management system (LMS) customized for Rwanda's Ministry of Local Government (MINALOC). The project runs in a Dockerized environment with MariaDB and PHP 8.1-Apache.

**Moodle Version:** 4.3.3+ (Build: 20240215)
**Branch:** 403
**PHP Version:** 8.1
**Database:** MariaDB

## Docker Environment

### Starting the Environment
```bash
docker compose up -d
```

The application will be available at: `http://localhost:8086`

### Services
- **moodle**: PHP 8.1-Apache container serving the Moodle application
  - Port: 8086 (maps to container's port 80)
  - Mounts: `./elearning`, `./moodledata`, `./php.ini-development`

- **db**: MariaDB database
  - Port: 3306
  - Root password: `gov@123` (configured in compose.yaml)
  - Database name: `moodle`
  - Initial data loaded from: `./init-scripts/init.sql`

### Stopping the Environment
```bash
docker compose down
```

### Accessing Containers
```bash
# Access Moodle container
docker compose exec moodle bash

# Access database container
docker compose exec db bash

# View logs
docker compose logs -f moodle
docker compose logs -f db
```

## Moodle Configuration

The main configuration file is `elearning/config.php` with these key settings:

- **Database:** MariaDB on host `db` (container name)
- **WWW Root:** `http://localhost:8086`
- **Data Directory:** `/var/www/moodledata` (inside container)
- **Table Prefix:** `mdl_`
- **Debugging:** Disabled (production mode)

To enable debugging during development, edit `elearning/config.php`:
```php
$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;
```

## Custom Plugins and Themes

### Local Plugins
Located in `elearning/local/`:

1. **local_additionaluserdetails** - Custom user registration fields
   - Version: 2025072200
   - Status: MATURITY_ALPHA
   - Entry point: `elearning/local/additionaluserdetails/register.php`

2. **local_userdetails** - User details management
   - Version: 2025072200
   - Status: MATURITY_ALPHA

### Custom Themes
Located in `elearning/theme/`:

- **adaptable** - Custom theme variant
- **space** - Custom theme variant
- **boost** - Default Moodle theme (core)
- **classic** - Alternative core theme

## Moodle CLI Commands

All CLI commands must be run inside the Moodle container:

```bash
docker compose exec moodle bash
cd /var/www/html
```

### Essential Commands

**Install/Upgrade Database:**
```bash
php admin/cli/install_database.php
```

**Upgrade Moodle:**
```bash
php admin/cli/upgrade.php --non-interactive
```

**Clear Caches:**
```bash
php admin/cli/purge_caches.php
```

**Fix Permissions (if needed):**
```bash
php admin/cli/fix_course_sortorder.php
```

**Cron (scheduled tasks):**
```bash
php admin/cli/cron.php
```

**Reset Admin Password:**
```bash
php admin/cli/reset_password.php
```

**Install/Uninstall Plugins:**
```bash
php admin/cli/uninstall_plugins.php --plugins=<plugin_name> --run
```

## Frontend Development

Moodle uses **Grunt** for frontend asset compilation.

### Prerequisites
Inside the Moodle container or on host with Node.js 16.x:

```bash
npm install
```

### Build Commands

**Compile All JavaScript (AMD modules):**
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

**Run ESLint:**
```bash
npx grunt eslint
```

**Run StyleLint:**
```bash
npx grunt stylelint
```

**Compile Everything:**
```bash
npx grunt
```

### JavaScript Architecture
- **AMD Modules:** Located in `*/amd/src/*.js`, compiled to `*/amd/build/*.js`
- **YUI Modules:** Legacy format in `*/yui/src/**/*.js`
- Uses RequireJS/AMD for module loading
- Babel transpilation for ES6+ support

### SCSS/CSS Architecture
- Theme SCSS: `theme/*/scss/**/*.scss`
- Component styles: `*/styles.css` or within theme directories
- Compiled CSS cached in `moodledata/localcache`

## Testing

### PHPUnit Tests

**Initialize PHPUnit:**
```bash
php admin/tool/phpunit/cli/init.php
```

**Run All Tests:**
```bash
vendor/bin/phpunit
```

**Run Specific Test Suite:**
```bash
vendor/bin/phpunit --testsuite core_dml_testsuite
```

**Run Tests for Specific Component:**
```bash
vendor/bin/phpunit path/to/component/tests/
```

**Run Single Test File:**
```bash
vendor/bin/phpunit local/userdetails/tests/userdetails_test.php
```

### Behat (Acceptance) Tests

Configuration: `behat.yml.dist`

**Initialize Behat:**
```bash
php admin/tool/behat/cli/init.php
```

**Run Behat Tests:**
```bash
vendor/bin/behat --config /var/www/moodledata/behatrun/behat/behat.yml
```

## Architecture Overview

### Directory Structure

```
elearning/
├── admin/          # Administration interfaces and tools
├── auth/           # Authentication plugins (LDAP, OAuth, etc.)
├── blocks/         # Sidebar blocks (~49 blocks)
├── cache/          # Cache subsystem
├── lib/            # Core libraries and APIs
│   ├── dml/        # Database abstraction layer (currently viewing)
│   └── classes/    # Core classes (autoloaded)
├── local/          # Custom local plugins (MINALOC customizations)
├── mod/            # Activity modules (assignments, quizzes, etc.)
├── theme/          # Themes (UI/UX customization)
├── course/         # Course management
├── user/           # User management
├── lang/           # Language packs
└── config.php      # Main configuration

moodledata/         # Data directory (file uploads, cache, sessions)
db/                 # MariaDB data volume
init-scripts/       # Database initialization SQL
```

### Core Subsystems

**Database Layer (`lib/dml/`):**
- Abstraction layer supporting multiple databases
- Currently using MySQLi driver: `mysqli_native_moodle_database.php`
- Uses prepared statements and parameterized queries
- Table prefix system (`mdl_`) for multi-tenancy

**Plugin System:**
- Modular architecture with plugin types: `mod`, `block`, `theme`, `local`, `auth`, `enrol`, etc.
- Each plugin has `version.php` with metadata
- Database schema in `db/install.xml`
- Language strings in `lang/en/<component>.php`

**Cache System:**
- Multiple cache stores (file, memcached, redis)
- Three-tier: application, session, request
- Configuration in `cache/` directory

**Event System:**
- Observer pattern for plugin hooks
- Events defined in `db/events.php`
- Observers in `classes/event/` and `db/events.php`

**Authentication Flow:**
- Multi-plugin authentication support (`auth/` directory)
- Custom registration forms in `local/additionaluserdetails/register.php`
- User profile extensions via local plugins

## Development Workflow

### Adding Custom Functionality

1. **Create Local Plugin** (preferred for custom features):
   ```bash
   mkdir -p elearning/local/myplugin/{classes,lang/en,db}
   # Create version.php, settings.php, and other required files
   ```

2. **Visit Admin Notifications:**
   Navigate to `http://localhost:8086/admin/index.php` to trigger installation

3. **Clear Caches:**
   ```bash
   docker compose exec moodle php admin/cli/purge_caches.php
   ```

### Modifying Themes

1. Edit SCSS in `elearning/theme/<themename>/scss/`
2. Compile: `npx grunt css`
3. Purge theme cache: `php admin/cli/purge_caches.php`
4. May need to increment theme version in `version.php`

### Working with Database

**Direct Database Access:**
```bash
docker compose exec db mariadb -uroot -p'gov@123' moodle
```

**Moodle Database API (in PHP code):**
```php
global $DB;

// Get records
$users = $DB->get_records('user', ['deleted' => 0]);

// Get single record
$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

// Insert
$id = $DB->insert_record('tablename', $dataobject);

// Update
$DB->update_record('tablename', $dataobject);

// Execute SQL (use sparingly)
$DB->execute('UPDATE {user} SET deleted = 1 WHERE id = ?', [$userid]);
```

**Table Naming:** All tables use `{tablename}` placeholders which resolve to `mdl_tablename` based on `$CFG->prefix`.

## Important Notes

### File Permissions
Moodle requires specific permissions on `moodledata/`:
- Inside container: owned by `www-data`
- Directory permissions: `02775`
- File permissions: `0664`

If permission issues occur:
```bash
docker compose exec moodle chown -R www-data:www-data /var/www/moodledata
docker compose exec moodle chmod -R 02775 /var/www/moodledata
```

### Security Considerations

- **Never commit** `config.php` with production credentials
- Default database password (`gov@123`) should be changed for production
- Review `elearning/local/` plugins for security before deployment
- Enable HTTPS in production by updating `$CFG->wwwroot` and setting `$CFG->sslproxy = true`

### Version Control

**Excluded from Git** (should be in `.gitignore`):
- `config.php`
- `moodledata/`
- `db/` (MariaDB data)
- `elearning/cache/`
- `elearning/localcache/`
- `node_modules/`

**Include in Git:**
- Custom plugins in `local/`
- Custom themes in `theme/`
- `compose.yaml`, `Dockerfile`
- `config-dist.php` (template)

### Performance Optimization

For development, caching can be disabled in `config.php`:
```php
$CFG->cachejs = false;
$CFG->langstringcache = false;
```

For production, enable caching and consider:
- Setting up Redis/Memcached
- Enabling OPcache
- Using CDN for static assets
- Configuring `$CFG->localcachedir`

## Plugin Development Structure

Standard plugin file structure (example for `local/myplugin`):
```
version.php           # Required: version and metadata
settings.php          # Admin settings page
lang/en/local_myplugin.php  # Language strings
db/
  ├── install.xml     # Database schema
  ├── upgrade.php     # Database upgrades
  ├── events.php      # Event observers
  └── access.php      # Capability definitions
classes/              # Autoloaded classes (PSR-4)
  ├── event/          # Custom events
  └── privacy/        # GDPR privacy provider
tests/                # PHPUnit tests
  └── *_test.php
amd/src/              # JavaScript (AMD modules)
scss/                 # SCSS stylesheets
```

## Useful References

- Moodle config reference: `elearning/config-dist.php`
- CLI scripts: `elearning/admin/cli/`
- Database schema definitions: `*/db/install.xml`
- Coding standards: Follow Moodle coding style (use `.eslintrc`, `.jshintrc`, `.stylelintrc`)

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Architecture

This is a GPS tracking system (**FastrackGPS**) that has been reorganized into a modular architecture with three main parts:

### 1. Legacy System (`/legacy-fastrackgps/`)
- Original PHP codebase using older patterns (mysql_* functions, framesets)
- Still functional and may be in production
- Uses traditional PHP without composer, direct MySQL connections
- Entry points: `index.php`, various admin files in `/administracao/`

### 2. Modern System (`/modern-fastrackgps/`)
- Refactored PHP 8.1+ application using Domain-Driven Design
- PSR-4 autoloading with Composer
- Clean architecture with strict separation of concerns
- Modern security practices and type safety

### 3. Shared Modules (`/shared-modules/`)
- Common configurations, assets, and utilities
- Bridges legacy and modern systems through symlinks and shared configs
- Database configurations, JavaScript, CSS, and images

## Development Commands

### Modern System Commands (in `/modern-fastrackgps/`)

```bash
# Install dependencies
composer install

# Run tests
composer test
composer run test-coverage

# Code quality
composer run phpstan          # Static analysis (Level 8)
composer run cs-fix           # Fix code style (PSR-12)
composer run cs-check         # Check code style
composer run rector           # Automated refactoring
composer run quality          # Run all quality checks

# Development workflow
composer run test && composer run phpstan && composer run cs-check
```

### Legacy System
- No build process - direct PHP execution
- Uses traditional include/require statements
- Database connection via `mysql.php` (redirects to shared config)

## Key Configuration Files

### Environment Setup
- `.env.example` - Comprehensive environment configuration template
- `shared-modules/config/database.php` - Shared database configuration
- `shared-modules/config/.env.shared` - Shared environment variables

### Code Quality
- `.php-cs-fixer.php` - PSR-12 code standards
- `phpstan.neon` - Level 8 static analysis
- `phpunit.xml` - Test configuration with coverage

## Architecture Patterns

### Modern System Structure
```
src/
├── Auth/          # Authentication & authorization
├── Vehicle/       # Vehicle management domain
├── Tracking/      # GPS tracking & positions
├── Alert/         # Alert system
├── Geofence/      # Virtual fence management
├── Command/       # GPS device commands
├── Payment/       # Payment processing
├── Core/          # Shared infrastructure
└── Security/      # Security utilities
```

Each domain follows DDD patterns:
- `Entity/` - Domain entities
- `Repository/` - Data access interfaces & implementations  
- `Service/` - Business logic
- `Controller/` - HTTP handlers
- `ValueObject/` - Immutable value types

### Database Access
- **Legacy**: Direct PDO/mysql_* functions
- **Modern**: Repository pattern with QueryBuilder
- **Shared**: `shared-modules/config/database.php` provides unified connection

### Asset Management
- Shared assets in `shared-modules/assets/`
- Legacy system uses symlinks: `imagens -> ../shared-modules/assets/images/`
- Modern system: `public/shared -> ../../shared-modules/assets/`

## Testing Approach

### Modern System Testing
- **PHPUnit 10** with strict configuration
- **Unit tests**: `tests/Unit/` for isolated components
- **Integration tests**: `tests/Integration/` for database interactions
- **Coverage target**: >90% (excludes Legacy code)
- **Test database**: SQLite in-memory for speed

### Legacy System
- No automated tests currently
- Manual testing required
- Database: Usually MySQL `tracker2` schema

## Security Considerations

### Modern System Security
- **CSRF protection** via `CsrfTokenManager`
- **Input sanitization** via `InputSanitizer`
- **Prepared statements** exclusively
- **Type safety** with PHP 8.1 strict types
- **Session security** with configurable settings

### Legacy System Warnings
- Contains known security vulnerabilities
- Uses deprecated MySQL functions in some areas
- Minimal input validation
- **Do not extend legacy code** - migrate to modern system instead

## Development Workflow

### For New Features
1. **Always use modern system** (`/modern-fastrackgps/`)
2. Follow DDD patterns and existing structure
3. Write tests for new functionality
4. Run quality checks before committing

### For Legacy Maintenance
1. **Minimal changes only** - fix critical bugs
2. **Create backups** before any changes
3. Consider migration path to modern system
4. Test thoroughly as no automated tests exist

### Asset Updates
1. Add new assets to `shared-modules/assets/`
2. Both systems will access via symlinks automatically
3. Update references in both systems if needed

## Database Schema

- **Primary database**: Usually named `tracker2` or `fastrackgps`
- **Main tables**: vehicles, positions, alerts, users, geofences
- **Schema location**: Check `shared-modules/scripts/database/` for setup scripts
- **Migrations**: Modern system should use proper migration scripts

## File Reference Update Scripts

When moving files or updating references:
- `update-references.php` - Updates include/require paths
- `fix-asset-references.php` - Fixes asset path references  
- `validate-references.php` - Validates all references work

## Common Development Tasks

### Adding a New Domain (Modern System)
1. Create domain folder in `src/`
2. Add Entity, Repository (interface + MySQL impl), Service, Controller
3. Add ValueObjects for domain-specific types
4. Write unit tests for business logic
5. Add integration tests for repository

### Fixing Legacy Issues
1. Check if issue exists in modern system first
2. If legacy-only, make minimal targeted fix
3. Document the issue for future migration
4. Consider if modern system needs similar protection

### Asset Management
1. Add to `shared-modules/assets/` appropriate subfolder
2. Reference via relative paths (symlinks handle routing)
3. Test in both legacy and modern systems

## Performance Considerations

- **Legacy system**: Direct queries, minimal caching
- **Modern system**: Query builder with potential for caching layers
- **Shared assets**: Served directly, no build process currently
- **Database**: Consider connection pooling for high-traffic deployments

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a PHP-based GPS tracking web application called FastrackGPS. The system provides vehicle tracking, geofencing, command sending, and fleet management capabilities.

## Architecture

### Core Structure
- **Legacy PHP Application**: Uses older PHP syntax and practices (no composer, direct MySQL queries)
- **Frame-based UI**: Uses HTML framesets for layout (topo.php, menu.php, mapa.php, listagem.html)
- **Database**: MySQL database named 'tracker2' 
- **Frontend**: Bootstrap 3.x with jQuery for UI components
- **Maps Integration**: Google Maps API with custom KML processing

### Key Directories
- `/administracao/` - Admin panel for user and vehicle management
- `/OBD/` - OBD-related functionality and interface
- `/ajax/` - AJAX endpoints and real-time data processing
- `/server/` - Core tracking server logic and GPS data processing
- `/webservices/` - API endpoints for external integrations
- `/javascript/` - Client-side JavaScript modules
- `/imagens/` - Static assets and icons

### Database Configuration
- Database connections are handled in multiple files:
  - `Connection.simple.php` - PDO-based connection
  - `mysql.php` - Legacy MySQL connection
  - Various `config.php` files in subdirectories

### Authentication System
- Session-based authentication in `default.php`
- Token-based security (MD5 tokens)
- Multi-level access: regular users, admin, and master admin
- User management in `/usuario/` and `/administracao/`

### GPS Tracking Core
- Server tracking logic in `/server/tracker.php` and `/server/tracker2.php`
- KML generation for Google Maps in `/server/kml.php`
- Real-time position updates via AJAX
- Historical route playback functionality

### Key Features
- **Vehicle Management**: Add, edit, delete vehicles and assign to users
- **Real-time Tracking**: Live GPS positions on Google Maps
- **Geofencing**: Virtual fence creation and alerts
- **Command System**: Send commands to GPS devices
- **Historical Reports**: Route history with playback controls
- **Alert System**: Various alert types for security and monitoring
- **Multi-tenant**: Support for multiple clients/accounts

## Development Notes

### Database Credentials
- Default database: `tracker2`
- Connection files contain placeholder credentials that need to be updated for production

### Google Maps Integration
- API key stored in `config.php`
- Custom map icons in `/server/icones/`
- KML processing for route display

### Legacy Considerations
- Uses deprecated MySQL functions (mysql_* instead of mysqli_* or PDO)
- Frame-based layout (deprecated HTML)
- Mixed PHP versions compatibility
- Direct SQL queries without ORM

### File Organization
- PHP files are scattered across multiple directories
- Similar functionality duplicated (e.g., CSS and JS files repeated in `/OBD/` and `/ajax/`)
- Configuration files exist in multiple locations

### Security Considerations
- Session management across framesets
- Token-based authentication system
- Database credentials in plain text files
- Mixed authentication methods

## No Build System
This is a traditional PHP application without modern build tools, package managers, or automated testing. Files are served directly by the web server without compilation or bundling steps.
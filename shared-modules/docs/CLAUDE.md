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
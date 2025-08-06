# FastrackGPS - Projetos Segregados por Módulos

Sistema GPS tracking com arquitetura modular separando legacy e moderno, com módulos comuns compartilhados.

## 📁 Estrutura Modular

### `/legacy-fastrackgps/` - Sistema Legacy
- Sistema PHP original em produção
- Módulos específicos legacy
- **⚠️ Usar apenas para manutenção**

### `/modern-fastrackgps/` - Sistema Moderno  
- Sistema PHP 8.1+ refatorado
- Módulos modernos com DDD
- **✅ Usar para novas funcionalidades**

### `/shared-modules/` - Módulos Compartilhados
- Configurações comuns
- Assets compartilhados (CSS, JS, imagens)
- Utilitários comuns
- Banco de dados compartilhado

## 📋 Requirements

- PHP 8.1 or higher
- MySQL 5.7+ or 8.0+
- Composer 2.0+
- Web server (Apache/Nginx)

## ⚡ Quick Start

### 1. Installation

```bash
# Clone the repository
git clone <repository-url>
cd Fastrackgpsweb

# Install dependencies
composer install

# Copy environment configuration
cp .env.example .env
```

### 2. Configuration

Edit `.env` file with your settings:

```env
# Database
DB_HOST=localhost
DB_DATABASE=tracker2
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Google Maps
GOOGLE_MAPS_API_KEY=your_api_key_here
```

### 3. Database Setup

```bash
# Run database migrations (if available)
php bin/console migrate

# Or import your existing database
mysql -u username -p tracker2 < database/backup.sql
```

### 4. Development Server

```bash
# Start PHP development server
php -S localhost:8000 -t public/
```

## 🔧 Development

### Code Quality Tools

```bash
# Run all quality checks
composer run quality

# Individual commands
composer run cs-check      # Check code style
composer run cs-fix        # Fix code style
composer run phpstan       # Static analysis
composer run test          # Run tests
composer run test-coverage # Run tests with coverage
```

### Testing

```bash
# Run unit tests
composer run test

# Run tests with coverage
composer run test-coverage

# Run specific test
./vendor/bin/phpunit tests/Unit/Auth/Domain/UserTest.php
```

### Code Style

This project follows PSR-12 coding standards:

```bash
# Check code style
composer run cs-check

# Auto-fix code style issues
composer run cs-fix
```

### Static Analysis

We use PHPStan at level 8 for maximum type safety:

```bash
# Run static analysis
composer run phpstan

# Generate baseline for legacy code
./vendor/bin/phpstan analyse --generate-baseline
```

### Automated Refactoring

Use Rector to automatically upgrade code:

```bash
# Preview changes
composer run rector

# Apply changes
composer run rector-fix
```

## 🏗️ Architecture

### Directory Structure

```
src/
├── Auth/           # Authentication domain
├── Core/           # Core infrastructure
├── Tracking/       # GPS tracking logic
├── Vehicle/        # Vehicle management
├── Admin/          # Admin functionality
└── Security/       # Security utilities

tests/
├── Unit/           # Unit tests
└── Integration/    # Integration tests
```

### Key Components

- **Domain Objects**: Pure business logic (User, Vehicle, Coordinates)
- **Repositories**: Data access layer with interfaces
- **Services**: Application services coordinating business logic
- **Controllers**: HTTP request handlers
- **Value Objects**: Type-safe data containers

## 🔒 Security Features

### CSRF Protection
```php
// Generate CSRF token
$token = $csrfManager->generateToken('login');

// Validate token
$csrfManager->requireValidToken($requestToken, 'login');
```

### Input Sanitization
```php
// Sanitize user input
$cleanInput = InputSanitizer::sanitizeString($userInput);
$email = InputSanitizer::sanitizeEmail($emailInput);
```

### Secure Sessions
- HTTP-only cookies
- Secure flag for HTTPS
- Session regeneration on login
- Configurable session lifetime

## 📊 Monitoring

### Logging

Logs are written to `storage/logs/` using Monolog:

```php
$logger->info('User authenticated', ['user_id' => $user->getId()]);
$logger->error('Database connection failed', ['error' => $e->getMessage()]);
```

### Error Handling

Centralized exception handling with typed exceptions:

```php
try {
    $user = $authService->authenticate($username, $password);
} catch (ValidationException $e) {
    // Handle validation errors
    $errors = $e->getErrors();
}
```

## 🚀 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Configure secure database credentials
- [ ] Set up HTTPS with secure session cookies
- [ ] Configure log rotation
- [ ] Set up monitoring and alerting
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Set proper file permissions

### CI/CD Pipeline

```yaml
# .github/workflows/ci.yml
name: CI
on: [push, pull_request]
jobs:
  quality:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.1
      - name: Install dependencies
        run: composer install
      - name: Run quality checks
        run: composer run quality
```

## 🧪 Testing Strategy

### Unit Tests
- Domain objects and value objects
- Business logic services
- Utility classes

### Integration Tests
- Database repositories
- Authentication flows
- API endpoints

### Test Coverage
- Minimum 80% coverage for new code
- 100% coverage for critical business logic

## 📝 Contributing

1. Follow PSR-12 coding standards
2. Write tests for new functionality
3. Ensure all quality checks pass
4. Update documentation as needed

## 🐛 Code Smells Resolved

See [REFACTORING_REPORT.md](REFACTORING_REPORT.md) for detailed analysis of legacy code issues and their solutions.

## 📄 License

Proprietary - All rights reserved
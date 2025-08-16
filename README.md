# Budget Manager

Personal budget management MVP application built with PHP using Symfony 6.4 LTS.

## 🚀 Features

- **User Management**
  - User registration and login
  - JWT authentication for API
  - `isActive` flag for each user

- **Budgets**
  - Create unlimited number of budgets
  - Required name and optional description
  - Automatic balance calculation

- **Transactions**
  - Add income and expenses to budgets
  - Required comment for each transaction
  - Complete transaction history

- **User Interface**
  - Responsive design with Bootstrap 5
  - Stimulus for interactivity
  - Clean dashboard with statistics

- **REST API**
  - Complete OpenAPI/Swagger documentation
  - JWT authorization
  - Secure access to own budgets only

## 🛠️ Technologies

- **Backend**: PHP 8.1, Symfony 6.4 LTS
- **Frontend**: Bootstrap 5, Stimulus, SCSS
- **Database**: SQLite (development)
- **Authentication**: JWT Tokens
- **Tools**: Webpack Encore, Doctrine ORM

## 📦 Installation

### Requirements
- PHP 8.1+
- Node.js 20+
- Composer

### Installation Steps

```bash
# Clone repository
git clone <repo-url>
cd budget_manager

# Install dependencies
composer install
npm install

# Generate JWT keys
php bin/console lexik:jwt:generate-keypair

# Setup database and load fixtures
php bin/console app:db:reset --force

# Build assets
npm run build

# Start development server
symfony server:start
```

## 🔧 Configuration

### Environment Variables

Configure in `.env.local` file:

```env
APP_ENV=dev
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_dev.db"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_jwt_passphrase
```

## 🎮 Usage

### Test Accounts

After loading fixtures, these test accounts are available:
- **Test User**: `test@example.com` | `password123`
- **Anna Kowalska**: `anna.kowalska@example.com` | `password456`  
- **Jan Nowak**: `jan.nowak@example.com` | `password789`

Each user has multiple budgets with sample transactions.

### URLs

- **Dashboard**: `http://localhost:8000/`
- **API Documentation**: `http://localhost:8000/api/docs` (no authentication required)

## 🧪 Testing

The application includes comprehensive unit tests for all entities and DTOs.

### Running Tests

```bash
# Run all tests
php vendor/bin/phpunit

# Run specific test suites
php vendor/bin/phpunit tests/Entity/     # Entity tests only
php vendor/bin/phpunit tests/DTO/       # DTO tests only

# Run with coverage (requires Xdebug)
php vendor/bin/phpunit --coverage-text

# Run specific test class
php vendor/bin/phpunit tests/Entity/UserTest.php
```

### Test Coverage

- **Entity Tests**: Complete coverage of User, Budget, and Transaction entities
  - Property getters/setters
  - Entity relationships
  - Business logic (balance calculations)
  - Edge cases and validation

- **DTO Tests**: Full validation testing for all Data Transfer Objects
  - UserRegistrationDTO
  - UserLoginDTO
  - ChangePasswordDTO
  - BudgetCreateDTO
  - TransactionCreateDTO

**Total: 143 Unit Tests** covering core business logic and data validation.

### Test Structure

```
tests/
├── Entity/
│   ├── UserTest.php                    # User entity tests
│   ├── BudgetTest.php                  # Budget entity tests
│   └── TransactionTest.php             # Transaction entity tests
└── DTO/
    ├── UserRegistrationDTOTest.php     # Registration validation tests
    ├── UserLoginDTOTest.php            # Login validation tests
    ├── ChangePasswordDTOTest.php       # Password change validation tests
    ├── BudgetCreateDTOTest.php         # Budget creation validation tests
    └── TransactionCreateDTOTest.php    # Transaction validation tests
```

## 🚀 CI/CD Pipeline

The project uses GitHub Actions for continuous integration.

### Automated Testing

On every push and pull request to `master`/`main` branches:
- Tests run on PHP 8.1 and 8.2
- Database setup and migrations
- JWT key generation
- PHPUnit test execution
- Asset building verification
- Security vulnerability checks

### Workflow

1. Create feature branch from `master`
2. Make changes and commit
3. Push branch and create Pull Request
4. GitHub Actions automatically runs tests
5. Review test results in Actions tab
6. Merge after ensuring tests pass

## 🛠️ Development Commands

```bash
# Database
php bin/console app:db:reset --force     # Reset DB with fixtures
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

# Assets
npm run dev          # Development build
npm run watch        # Development build with watch
npm run build        # Production build

# Testing
php vendor/bin/phpunit                      # All tests
php vendor/bin/phpunit tests/Entity/        # Entity tests only
php vendor/bin/phpunit tests/DTO/           # DTO tests only
php vendor/bin/phpunit --coverage-text      # With coverage report
php vendor/bin/phpunit --testdox            # Human-readable test output

# JWT
php bin/console lexik:jwt:generate-keypair

# Cache & Debug
php bin/console cache:clear
php bin/console debug:router
php bin/console debug:container
```

## 📝 TODO / Future Features

- [ ] Transaction categories
- [ ] Data export to CSV/Excel
- [ ] Charts and statistics
- [ ] Spending limit notifications
- [ ] Mobile API
- [ ] Bank integrations
- [ ] Integration tests
- [ ] Functional tests
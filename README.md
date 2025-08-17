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

The application includes comprehensive testing at multiple levels: unit tests, integration tests, and end-to-end tests.

### Unit Tests

```bash
# Run all unit tests
php vendor/bin/phpunit

# Run specific test suites
php vendor/bin/phpunit tests/Entity/     # Entity tests only
php vendor/bin/phpunit tests/DTO/       # DTO tests only

# Run with coverage (requires Xdebug)
php vendor/bin/phpunit --coverage-text

# Run specific test class
php vendor/bin/phpunit tests/Entity/UserTest.php
```

### End-to-End Tests (E2E)

Modern E2E tests using **Playwright** that test complete user workflows:

```bash
# Run all E2E tests
npm run test:e2e

# Run E2E tests with UI (interactive mode)
npm run test:e2e:ui

# Run E2E tests in headed mode (visible browser)
npm run test:e2e:headed

# Debug E2E tests step by step
npm run test:e2e:debug

# Run specific E2E test
npx playwright test --grep "should complete full user workflow"
```

### Test Coverage & Results

**Total: 148 Tests ✅ (100% Success Rate)**

#### Unit Tests: 143/143 PASSED ✅
- **Entity Tests (44 tests)**: Complete coverage of User, Budget, and Transaction entities
  - ✔ Budget (15 tests): Creation, getters/setters, balance calculations
  - ✔ Transaction (19 tests): Amount handling, types, relationships
  - ✔ User (10 tests): Role management, budget collections
  
- **DTO Tests (99 tests)**: Full validation testing for all Data Transfer Objects
  - ✔ UserRegistrationDTO (12 tests): Email & password validation
  - ✔ UserLoginDTO (15 tests): Login validation
  - ✔ ChangePasswordDTO (16 tests): Password change validation
  - ✔ BudgetCreateDTO (23 tests): Budget creation validation
  - ✔ TransactionCreateDTO (33 tests): Transaction validation

#### E2E Tests: 5/5 PASSED ✅
- **Complete User Workflow**: Registration → Login → Budget Creation → 3 Transactions → Financial Verification
  - ✓ User registration with unique email
  - ✓ JWT authentication and token handling
  - ✓ Budget creation and verification
  - ✓ Transaction creation (Income: 3000 + 800, Expense: 250.5)
  - ✓ Balance calculation verification (3549.5)
- **Authentication Security**: Unauthorized access protection (401 responses)
- **User Isolation**: Users can only access their own data (404 for foreign resources)
- **Input Validation**: Duplicate email rejection, invalid data handling
- **API Documentation**: OpenAPI/Swagger endpoint availability testing

**Performance**: Unit tests ~0.5s, E2E tests ~5.5s

### Test Structure

```
tests/                                  # PHPUnit tests only
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

e2e-tests/                              # Playwright E2E tests
└── user-workflow.spec.js               # End-to-end user workflow tests
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
php vendor/bin/phpunit                      # All unit tests (143/143 ✅)
php vendor/bin/phpunit tests/Entity/        # Entity tests only (44 tests)
php vendor/bin/phpunit tests/DTO/           # DTO tests only (99 tests)
php vendor/bin/phpunit --coverage-text      # With coverage report
php vendor/bin/phpunit --testdox            # Human-readable test output

npm run test:e2e                           # All E2E tests (5/5 ✅)
npm run test:e2e:ui                        # E2E tests with interactive UI
npm run test:e2e:headed                    # E2E tests with visible browser

# JWT
php bin/console lexik:jwt:generate-keypair

# Cache & Debug
php bin/console cache:clear
php bin/console debug:router
php bin/console debug:container
```
# 🧪 Testing Documentation

This document provides comprehensive information about the testing strategy and results for the Budget Manager application.

## Overview

The application includes comprehensive testing at multiple levels: unit tests, integration tests, and end-to-end tests.

## Test Coverage & Results

**Total: 148 Tests ✅ (100% Success Rate)**

### Unit Tests: 143/143 PASSED ✅

#### Entity Tests (44 tests)
Complete coverage of User, Budget, and Transaction entities:

- **Budget (15 tests)**: Creation, getters/setters, balance calculations
  - Budget creation and property management
  - Transaction collection handling
  - Balance calculation with mixed transactions
  - Fluent interface validation
  - Edge cases with string amounts

- **Transaction (19 tests)**: Amount handling, types, relationships
  - Amount validation (zero, negative, precision)
  - Type validation (income/expense)
  - Comment handling with special characters
  - Budget relationship management
  - Complete transaction workflows

- **User (10 tests)**: Role management, budget collections
  - User creation and authentication
  - Role management with ROLE_USER default
  - Budget collection operations
  - Security and access control

#### DTO Tests (99 tests)
Full validation testing for all Data Transfer Objects:

- **UserRegistrationDTO (12 tests)**: Email & password validation
  - Email format validation (valid/invalid formats)
  - Password length requirements (minimum 6 characters)
  - Required field validation
  - Special characters and Unicode support

- **UserLoginDTO (15 tests)**: Login validation
  - Email format validation
  - Password validation (no length restriction for login)
  - Empty field handling
  - Multiple validation error scenarios

- **ChangePasswordDTO (16 tests)**: Password change validation
  - Current password verification
  - New password requirements
  - Password confirmation matching
  - Security edge cases

- **BudgetCreateDTO (23 tests)**: Budget creation validation
  - Name requirements and length limits
  - Description handling (optional field)
  - Special characters, Unicode, and emoji support
  - HTML and multiline content validation

- **TransactionCreateDTO (33 tests)**: Transaction validation
  - Amount validation (positive, zero, precision)
  - Type validation (income/expense)
  - Comment requirements and length limits
  - Error handling and edge cases

### E2E Tests: 5/5 PASSED ✅

Modern E2E tests using **Playwright** that test complete user workflows:

#### Complete User Workflow Test
Full end-to-end user journey testing:
- ✓ User registration with unique email (e2e-test-{timestamp}@example.com)
- ✓ JWT authentication and token handling
- ✓ Budget creation and verification ("E2E Test Budget")
- ✓ Transaction creation:
  - Salary Income: 3000.00 (income)
  - Grocery Shopping: 250.50 (expense)  
  - Freelance Project: 800.00 (income)
- ✓ Financial calculation verification:
  - Total Income: 3800.00
  - Total Expenses: 250.50
  - Balance: 3549.50
- ✓ Data persistence and retrieval verification

#### Security Tests
- **Authentication Security**: Unauthorized access protection (401 responses)
- **User Isolation**: Users can only access their own data (404 for foreign resources)
- **Token Validation**: Invalid JWT token handling

#### Validation Tests
- **Input Validation**: Duplicate email rejection, invalid data handling
- **API Error Handling**: Proper error responses and status codes

#### API Documentation Test
- **OpenAPI/Swagger**: Endpoint availability testing

**Performance**: Unit tests ~0.5s, E2E tests ~5.5s

## Running Tests

### Unit Tests

```bash
# Run all unit tests
php vendor/bin/phpunit

# Run specific test suites
php vendor/bin/phpunit tests/Entity/     # Entity tests only (44 tests)
php vendor/bin/phpunit tests/DTO/       # DTO tests only (99 tests)

# Run with coverage (requires Xdebug)
php vendor/bin/phpunit --coverage-text

# Run specific test class
php vendor/bin/phpunit tests/Entity/UserTest.php

# Human-readable test output
php vendor/bin/phpunit --testdox
```

### End-to-End Tests

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

## Test Structure

```
tests/                                  # PHPUnit tests only
├── Entity/
│   ├── UserTest.php                    # User entity tests (10 tests)
│   ├── BudgetTest.php                  # Budget entity tests (15 tests)
│   └── TransactionTest.php             # Transaction entity tests (19 tests)
├── DTO/
│   ├── UserRegistrationDTOTest.php     # Registration validation tests (12 tests)
│   ├── UserLoginDTOTest.php            # Login validation tests (15 tests)
│   ├── ChangePasswordDTOTest.php       # Password change validation tests (16 tests)
│   ├── BudgetCreateDTOTest.php         # Budget creation validation tests (23 tests)
│   └── TransactionCreateDTOTest.php    # Transaction validation tests (33 tests)
└── README.md                           # This documentation file

e2e-tests/                              # Playwright E2E tests
└── user-workflow.spec.js               # End-to-end user workflow tests (5 tests)
```

## Test Configuration

### PHPUnit Configuration
- Configuration file: `phpunit.dist.xml`
- Bootstrap file: `tests/bootstrap.php`
- Test environment: `APP_ENV=test`
- Symfony test extensions enabled

### Playwright Configuration
- Configuration file: `playwright.config.js`
- Test directory: `e2e-tests/`
- Browser: Chromium (headless by default)
- Base URL: `http://localhost:8000`
- Timeout: 30 seconds per test
- Screenshots on failure
- Traces on retry

## CI/CD Integration

### Automated Testing Pipeline

The tests run automatically on every push and pull request to `master`/`main` branches:

**Pipeline Jobs:**
1. **Unit Tests** (143 tests) - Entity and DTO validation
2. **E2E Tests** (5 tests) - Complete user workflows with Playwright
3. **Asset Building** - Frontend compilation
4. **Security Check** - Dependency vulnerability scanning

### GitHub Actions Workflow

```yaml
# Unit Tests Job
- name: Run PHPUnit tests
  run: php vendor/bin/phpunit --no-coverage --display-deprecations

# E2E Tests Job  
- name: Install Playwright browsers
  run: npx playwright install chromium
- name: Run Playwright E2E tests
  run: npm run test:e2e
```

## Test Results Summary

### Latest Test Run Results

#### Unit Tests Results ✅
```
PHPUnit 10.5.52 by Sebastian Bergmann and contributors.

Budget (App\Tests\Entity\Budget)
 ✔ Budget creation
 ✔ Name getter and setter
 ✔ Description getter and setter
 ✔ Get balance with mixed transactions
 [... 15 tests total]

Transaction (App\Tests\Entity\Transaction)
 ✔ Transaction creation
 ✔ Amount getter and setter
 ✔ Type getter and setter
 [... 19 tests total]

User (App\Tests\Entity\User)
 ✔ User creation
 ✔ Email getter and setter
 ✔ Roles always include role user
 [... 10 tests total]

[DTO Tests - 99 tests all passing]

OK (143 tests, 287 assertions)
Time: 00:00.539, Memory: 28.00 MB
```

#### E2E Tests Results ✅
```
Running 5 tests using 1 worker

✓ Complete user workflow: register → login → create budget → add transactions
✓ Authentication security tests
✓ User isolation enforcement  
✓ Input validation tests
✓ API documentation availability

5 passed (5.5s)
```

## Best Practices

### Writing Unit Tests
- Use descriptive test method names
- Test one concept per test method
- Include edge cases and error scenarios
- Use data providers for multiple similar tests
- Mock external dependencies

### Writing E2E Tests
- Use unique test data (timestamps in emails)
- Test complete user workflows
- Verify both positive and negative scenarios
- Include security and authorization tests
- Clean up test data when possible

### Test Maintenance
- Keep tests fast and reliable
- Update tests when business logic changes
- Maintain test documentation
- Monitor test coverage
- Review failing tests promptly

## Troubleshooting

### Common Issues

**Unit Tests:**
- Database connection issues: Ensure SQLite is properly configured
- JWT key issues: Run `php bin/console lexik:jwt:generate-keypair`
- Memory issues: Increase PHP memory limit if needed

**E2E Tests:**
- Server not starting: Check if port 8000 is available
- Browser issues: Ensure Chromium is installed via `npx playwright install chromium`
- Timeout issues: Increase timeout in `playwright.config.js`

### Debugging
- Use `--testdox` flag for readable PHPUnit output
- Use `npm run test:e2e:debug` for step-by-step E2E debugging
- Check GitHub Actions logs for CI/CD issues
- Use `--display-deprecations` to catch deprecation warnings

## Contributing

When adding new features:
1. Write unit tests for new entities/DTOs
2. Add E2E tests for new user workflows
3. Ensure all tests pass before submitting PR
4. Update this documentation if needed
5. Maintain 100% test success rate
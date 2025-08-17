# Budget Manager - Project Architecture Overview (Updated 2025-08-17)

## Core Domain Model
The application follows a clean domain model with three main entities:

### Entities
1. **User Entity** (`src/Entity/User.php`)
   - Properties: id, email, roles, password, isActive, budgets (Collection)
   - Implements UserInterface for Symfony Security
   - Methods: Full CRUD, role management, budget collection management
   - Security: bcrypt password hashing, active status flag

2. **Budget Entity** (`src/Entity/Budget.php`) 
   - Properties: id, name, description, user, createdAt, transactions (Collection)
   - Key method: getBalance() - calculates total from transactions
   - Relationships: ManyToOne with User, OneToMany with Transaction

3. **Transaction Entity** (`src/Entity/Transaction.php`)
   - Properties: id, amount (string for precision), type (income/expense), comment, createdAt, budget
   - Types: 'income' or 'expense' 
   - Relationships: ManyToOne with Budget

## API Architecture

### Authentication Endpoints
- `POST /api/auth/register` - User registration with UserRegistrationDTO
- `POST /api/auth/login` - JWT authentication with UserLoginDTO  
- `POST /api/auth/change-password` - Password change with ChangePasswordDTO

### Budget Management Endpoints
- `GET /api/budgets` - List user's budgets
- `POST /api/budgets` - Create budget with BudgetCreateDTO
- `GET /api/budgets/{id}` - Get budget details

### Transaction Management Endpoints
- `GET /api/budgets/{budgetId}/transactions` - List budget transactions
- `POST /api/budgets/{budgetId}/transactions` - Add transaction with TransactionCreateDTO

## Controllers Structure
- **AuthController**: Handles authentication, registration, password changes
- **BudgetController**: API endpoints for budget management
- **TransactionController**: API endpoints for transaction management  
- **DashboardController**: Web interface dashboard
- **WebBudgetController**: Web interface budget management

## Data Validation System
Comprehensive DTO validation for all user inputs:
- **UserRegistrationDTO**: Email validation, password min 6 chars
- **UserLoginDTO**: Email format validation
- **ChangePasswordDTO**: Current password verification, new password confirmation
- **BudgetCreateDTO**: Name required, description optional
- **TransactionCreateDTO**: Amount positive validation, type enum, comment required

## Security Implementation
- JWT authentication with RSA key pairs
- Password hashing with bcrypt
- CSRF protection for web forms
- User data isolation (users only see their own data)
- Input validation and sanitization

## Database Schema
- SQLite for development
- Doctrine ORM with migrations
- Custom database reset command: `php bin/console app:db:reset`
- Fixtures for test data (3 test users with sample budgets/transactions)

## Testing Strategy (148 tests total)
- **Unit Tests**: 143 tests (Entity: 44, DTO: 99)
- **E2E Tests**: 5 Playwright tests covering complete user workflows
- **CI/CD**: GitHub Actions with automated testing
- **Coverage**: Comprehensive validation and business logic testing

## Frontend Implementation
- Bootstrap 5 + Stimulus framework
- SCSS styling with Webpack Encore
- Modal-based interactions
- Responsive design
- Form validation integration

## Development Tools
- Symfony 6.4 LTS framework
- Composer for PHP dependencies
- NPM/Webpack for frontend assets
- OpenAPI/Swagger documentation at `/api/docs`
- Custom CLI commands for database management

## Test Accounts
- test@example.com / password123
- anna.kowalska@example.com / password456
- jan.nowak@example.com / password789
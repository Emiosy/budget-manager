# API Architecture and Endpoints

## Authentication Strategy
Budget Manager uses **dual authentication**:
- **JWT Tokens** for API endpoints (`/api/*`)
- **Session-based** for web interface (`/login`, `/register`, etc.)

### JWT Configuration
- **Algorithm**: RS256 (RSA with SHA-256)
- **Token Location**: `Authorization: Bearer {token}`
- **Key Files**: 
  - Private: `config/jwt/private.pem`
  - Public: `config/jwt/public.pem`
  - Passphrase: `XYZ123` (configurable via `.env`)

## API Endpoints Structure

### Authentication Endpoints
```
POST /api/register
POST /api/login
```

### Budget Management
```
GET    /api/budgets              # List user's budgets
POST   /api/budgets              # Create new budget
GET    /api/budgets/{id}         # Get budget details
PUT    /api/budgets/{id}         # Update budget
DELETE /api/budgets/{id}         # Delete budget
```

### Transaction Management  
```
GET    /api/budgets/{id}/transactions     # List budget transactions
POST   /api/budgets/{id}/transactions     # Add transaction
PUT    /api/transactions/{id}             # Update transaction
DELETE /api/transactions/{id}             # Delete transaction
```

## Controller Architecture

### API Controllers (`/src/Controller/`)
- **AuthController.php**: Registration and login
- **BudgetController.php**: Budget CRUD operations
- **TransactionController.php**: Transaction management

### Web Controllers
- **DashboardController.php**: Main web interface
- **WebBudgetController.php**: Web-based budget management

## Data Transfer Objects (DTOs)

### Key DTOs (`/src/DTO/`)
- **UserRegistrationDTO**: API user registration
- **BudgetCreateDTO**: Budget creation requests
- **TransactionDTO**: Transaction data

### DTO Pattern Benefits
- Input validation
- API documentation generation
- Type safety
- Clear API contracts

## OpenAPI Documentation

### Automatic Generation
- **Bundle**: `nelmio/api-doc-bundle`
- **Location**: `/api/doc`
- **Format**: OpenAPI 3.0 with Swagger UI

### Documentation Features
- Interactive API testing
- Schema definitions
- Authentication examples
- Response samples
- Error codes

### Implementation Pattern
```php
#[Route('/api/budgets', methods: ['GET'])]
#[OA\Get(
    summary: 'List user budgets',
    description: 'Returns all budgets belonging to the authenticated user',
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of budgets',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(ref: new Model(type: Budget::class))
            )
        )
    ]
)]
#[Security(name: 'bearerAuth')]
```

## Response Patterns

### Success Responses
```json
{
  "id": 1,
  "name": "Monthly Budget",
  "balance": 1250.50,
  "transactions": [...]
}
```

### Error Responses
```json
{
  "error": "Invalid credentials",
  "code": 401
}
```

### Validation Errors
```json
{
  "error": "Validation failed",
  "details": {
    "email": ["This value is not a valid email address"],
    "password": ["This value is too short"]
  }
}
```

## Security Implementation

### Input Validation
- All DTOs use Symfony validation constraints
- Database-level constraints prevent data corruption
- XSS protection via proper output escaping

### Authentication Security
- JWT tokens expire after configuration period
- RSA keys stored securely (gitignored)
- bcrypt for password hashing
- User activation flag for account management

### Authorization Patterns
```php
// Method-level security
#[Security("user.getId() === budget.getUser().getId()")]
public function updateBudget(Budget $budget)

// Route-level requirements
#[Route('/api/budgets', requirements: ['id' => '\d+'])]
```

## Database Integration

### Entity Relationships
```
User (1) ←→ (∞) Budget (1) ←→ (∞) Transaction
```

### Balance Calculation
- Calculated dynamically in `Budget::getBalance()`
- Supports real-time balance updates
- Handles income (+) and expense (-) transactions

### SQL Compatibility
- Uses quoted keywords: `"password"` 
- Custom table names: `budget_transaction`
- SQLite optimized with PostgreSQL/MySQL compatibility

## Error Handling Strategy
- HTTP status codes follow REST standards
- Detailed error messages for development
- Generic messages for production
- Comprehensive logging for debugging

## Testing Strategy
- API endpoints testable via `/api/doc`
- Fixtures provide consistent test data
- Custom reset command enables clean testing
- curl examples in documentation
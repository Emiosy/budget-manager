# Code Style and Conventions

## PHP Code Style (PSR-12 Compatible)

### Naming Conventions
- **Classes**: PascalCase (`User`, `BudgetController`, `DatabaseResetCommand`)
- **Methods**: camelCase (`getBalance()`, `findByUser()`, `resetDatabase()`)
- **Properties**: camelCase (`$isActive`, `$createdAt`, `$userEmail`)
- **Constants**: UPPER_SNAKE_CASE (`JWT_SECRET_KEY`, `DATABASE_URL`)
- **Files**: PascalCase for classes (`User.php`, `BudgetController.php`)

### Entity Conventions
- Use Doctrine annotations with PHP 8 attributes
- Always include OpenAPI documentation attributes
- Use proper validation constraints
- Example:
```php
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[OA\Schema(description: 'User account with authentication')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[OA\Property(description: 'Whether the user account is active', example: true)]
    private bool $isActive = true;
}
```

### Controller Conventions
- Separate API and Web controllers
- API controllers return JSON responses
- Web controllers return Twig templates
- Use DTOs for API request/response objects
- Include comprehensive OpenAPI documentation
- Example API controller:
```php
#[Route('/api/budgets', methods: ['POST'])]
#[OA\Post(
    summary: 'Create a new budget',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: BudgetCreateDTO::class))
    )
)]
public function create(Request $request): JsonResponse
```

### Security Conventions
- Use bcrypt for password hashing
- Quote SQL reserved keywords in migrations
- Validate all input data
- Use CSRF protection for forms
- Never expose sensitive data in API responses

## Database Conventions

### Table Naming
- Use snake_case for table names
- Prefix with descriptive names to avoid reserved keywords
- Example: `budget_transaction` instead of `transaction`

### Migration Conventions
- Use clean sequential numbering: `Version20250101010000.php`
- Include descriptive comments in migration classes
- Always test migrations with the custom reset command
- Quote reserved keywords in SQL:
```php
$this->addSql('CREATE TABLE user (
    "password" VARCHAR(255) NOT NULL  -- Quote reserved keyword
)');
```

## Frontend Conventions

### JavaScript (Stimulus)
- Use Stimulus controllers for interactive elements
- Follow naming: `hello_controller.js`
- Keep controllers focused and small

### SCSS/CSS
- Use Bootstrap 5 utility classes first
- Custom SCSS in `assets/styles/`
- Follow BEM methodology for custom components
- Example:
```scss
// Use Bootstrap utilities
.budget-card {
    @extend .card;
    @extend .mb-3;
}

// Custom components
.transaction {
    &__amount {
        font-weight: bold;
        
        &--income { color: $success; }
        &--expense { color: $danger; }
    }
}
```

## File Organization
```
src/
├── Command/          # Custom console commands
├── Controller/       # Web and API controllers
├── DTO/             # Data Transfer Objects
├── Entity/          # Doctrine entities
├── DataFixtures/    # Database fixtures
└── Repository/      # Custom repository methods

config/
├── packages/        # Bundle configurations
└── routes/          # Route definitions

templates/           # Twig templates
assets/             # Frontend assets
migrations/         # Database migrations
```

## Documentation Standards
- All API endpoints must have OpenAPI annotations
- Use meaningful descriptions in OpenAPI schemas
- Include examples in API documentation
- Maintain CLAUDE.md for development guidelines

## Error Handling
- Use proper HTTP status codes
- Provide meaningful error messages
- Log errors appropriately
- Validate input before processing

## Testing Approach
- Use fixtures for consistent test data
- Test both API and web interfaces
- Use the custom `app:db:reset` command for test setup
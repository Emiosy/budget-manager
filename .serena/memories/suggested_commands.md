# Suggested Commands for Budget Manager

## Essential Development Commands

### Project Setup
```bash
# Install dependencies (first time setup)
composer install
npm install

# Generate JWT keys (required)
php bin/console lexik:jwt:generate-keypair

# Setup database
php bin/console app:db:reset --force
```

### Development Workflow
```bash
# Start development server
symfony server:start
# Alternative: php -S localhost:8000 -t public/

# Asset compilation
npm run dev          # One-time build
npm run watch        # Development with file watching
npm run build        # Production build

# Database operations
php bin/console app:db:reset --force              # Custom command: reset entire database
php bin/console doctrine:migrations:migrate       # Run migrations only
php bin/console doctrine:fixtures:load           # Load fixtures only
```

### Task Completion Commands
When completing tasks, ALWAYS run these commands in order:

1. **Clear cache**: `php bin/console cache:clear`
2. **Check migrations**: `php bin/console doctrine:migrations:status`
3. **Test database**: `php bin/console app:db:reset --force` (if needed)
4. **Build assets**: `npm run build`
5. **Verify server**: `symfony server:start` or `php -S localhost:8000 -t public/`

### Debugging and Development
```bash
# Debug commands
php bin/console debug:router           # List all routes
php bin/console debug:container        # Show services
php bin/console doctrine:mapping:info  # Entity mapping info

# Cache management
php bin/console cache:clear            # Clear all cache
php bin/console cache:warmup          # Warm up cache

# Database debugging
php bin/console doctrine:query:sql "SELECT * FROM user"  # Raw SQL
```

### Security and JWT
```bash
# Regenerate JWT keys (if needed)
php bin/console lexik:jwt:generate-keypair --overwrite

# Check JWT configuration
php bin/console debug:config lexik_jwt_authentication
```

### Asset and Frontend
```bash
# Clean rebuild (if issues)
rm -rf node_modules package-lock.json
npm install
npm run build

# SCSS compilation issues
npm run dev --verbose
```

### Testing and Quality
```bash
# Check API documentation
curl http://localhost:8000/api/doc

# Test API endpoints (after server start)
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

## Useful System Commands (Darwin/macOS)
```bash
# File operations
ls -la                    # List files with details
find . -name "*.php"      # Find PHP files
grep -r "searchterm" src/ # Search in source files

# Process management
ps aux | grep php         # Find running PHP processes
lsof -i :8000            # Check what's using port 8000

# Git operations
git status
git add .
git commit -m "message"
```

## Custom Application Commands
- `php bin/console app:db:reset`: **Most important custom command** - safely resets database without deleting the file
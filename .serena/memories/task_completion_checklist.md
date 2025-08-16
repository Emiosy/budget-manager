# Task Completion Checklist

## CRITICAL: Always Follow These Steps When Completing Tasks

### 1. Code Quality Checks
```bash
# Clear application cache
php bin/console cache:clear

# Verify no cache issues
symfony server:start  # Test if server starts without errors
```

### 2. Database Integrity
```bash
# Check migration status
php bin/console doctrine:migrations:status

# If database changes were made, test full reset
php bin/console app:db:reset --force

# Verify fixtures load properly and test data is available
```

### 3. Asset Compilation
```bash
# Always rebuild assets after frontend changes
npm run build

# For development, ensure watch works
npm run watch  # Test that changes are detected
```

### 4. API Documentation
```bash
# After API changes, verify documentation updates
# Visit: http://localhost:8000/api/doc
# Ensure new endpoints/schemas are properly documented
```

### 5. Security Verification
- Check that new endpoints have proper authentication
- Verify sensitive data is not exposed
- Ensure CSRF protection is maintained for forms
- Test JWT token functionality if auth was modified

### 6. Test Key Functionality
```bash
# Start development server
symfony server:start

# Test critical paths:
# 1. User registration/login
# 2. Budget creation
# 3. Transaction management
# 4. API endpoints with curl or Postman
```

### 7. Code Structure Validation
- Ensure new files follow naming conventions
- Verify proper namespace usage
- Check that entities have OpenAPI documentation
- Confirm DTOs are used for API inputs/outputs

## What to Do After Each Type of Task

### After Entity/Database Changes
1. Run migrations: `php bin/console doctrine:migrations:migrate`
2. Update fixtures if needed
3. Test with: `php bin/console app:db:reset --force`
4. Verify relationships work correctly

### After Controller Changes
1. Clear cache: `php bin/console cache:clear`
2. Test routes: `php bin/console debug:router`
3. Verify API documentation: `/api/doc`
4. Test endpoints with sample data

### After Frontend Changes
1. Rebuild assets: `npm run build`
2. Clear Symfony cache: `php bin/console cache:clear`
3. Test responsive design
4. Verify Stimulus controllers work

### After Configuration Changes
1. Clear cache: `php bin/console cache:clear`
2. Test application startup
3. Verify new configuration is loaded
4. Check debug information: `php bin/console debug:config`

## Red Flags to Watch For
- ❌ Server won't start after changes
- ❌ Migration errors or conflicts
- ❌ Asset compilation failures
- ❌ Missing API documentation for new endpoints
- ❌ Broken authentication flows
- ❌ SQL reserved keyword issues

## Recovery Commands (If Something Breaks)
```bash
# Nuclear option - complete reset
php bin/console cache:clear
rm -rf var/cache/*
php bin/console app:db:reset --force
npm run build

# JWT key regeneration
php bin/console lexik:jwt:generate-keypair --overwrite

# Asset rebuild
rm -rf node_modules package-lock.json public/build
npm install
npm run build
```

## Success Indicators
- ✅ Server starts without errors
- ✅ Database reset completes successfully
- ✅ Assets compile without warnings
- ✅ API documentation is up-to-date
- ✅ Test account can login and perform basic operations
- ✅ All new functionality works as expected

**Remember**: The custom `app:db:reset --force` command is your friend - use it liberally during development to ensure database consistency!
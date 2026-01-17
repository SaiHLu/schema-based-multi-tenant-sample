# AGENTS.md

This document provides guidelines for agentic coding agents working in this Laravel multi-tenant application.

## Build, Lint, and Test Commands

### PHP Commands
```bash
# Install PHP dependencies
composer install

# Run all tests (Pest + PHPUnit)
composer test

# Run a single test file
./vendor/bin/pest tests/Feature/ExampleTest.php

# Run a specific test by name
./vendor/bin/pest --filter="test_example"

# Run tests with coverage
./vendor/bin/pest --coverage

# Format PHP code with Laravel Pint
./vendor/bin/pint

# Format and verify without modifying files
./vendor/bin/pint --dry-run

# Fix style violations only
./vendor/bin/pint --dirty
```

### Frontend Commands
```bash
# Install npm dependencies
npm install

# Start Vite dev server
npm run dev

# Build for production
npm run build
```

### Development Server
```bash
# Start Laravel dev server (requires running all services)
composer dev

# Or start individual services:
php artisan serve                    # HTTP server
php artisan queue:listen --tries=1   # Queue worker
php artisan pail --timeout=0         # Log viewer
npm run dev                          # Vite dev server
```

### Database Commands
```bash
# Run main migrations (shared tables)
php artisan migrate

# Run tenant migrations for a specific schema
php artisan tenants:migrate {schema_name}

# Create tenant migration file
php artisan tenants:make-migration create_table_name
```

## Code Style Guidelines

### PHP Style (Laravel + Pint)

**General:**
- Follow PSR-12 standards
- Use strict types: `declare(strict_types=1);` at top of files
- Laravel Pint handles formatting automatically; run it before committing

**Naming Conventions:**
- Classes: `PascalCase` (e.g., `TenantObserver`, `SetTenantSchema`)
- Methods and variables: `camelCase` (e.g., `createTenant()`, `$schemaName`)
- Constants: `SCREAMING_SNAKE_CASE` (e.g., `MAX_RETRY_COUNT`)
- Database tables: `snake_case` (handled by Eloquent conventions)
- Test methods: `camelCase` with descriptive names (e.g., `it_creates_tenant_schema()` for Pest)

**Imports:**
- Use fully qualified class names when not imported
- Group imports: PHP core, Composer packages, then application imports
- Sort alphabetically within groups

**Type Hints and Return Types:**
- Use return type hints on all methods
- Use nullable types with `?Type` when applicable
- Union types: `int|string|null`
- Strict typing enforced at file level

**Error Handling:**
- Use exceptions for exceptional cases
- Return appropriate HTTP status codes in controllers
- Validate input using Form Requests or manual validation
- Catch specific exceptions; avoid bare `catch (...)` blocks
- Log errors using `Log::error()` or `logger()` with context

**Controller Patterns:**
- Keep controllers thin; delegate logic to services
- Use dependency injection for services
- Return JSON responses consistently: `response()->json([...], status)`
- Use route model binding where appropriate

**Model Guidelines:**
- Define `$fillable` for mass-assignable fields
- Use `$hidden` for sensitive data that shouldn't be serialized
- Define relationships as methods returning `Relation` objects
- Set appropriate `$table` if not following conventions

### Multi-Tenant Specific Patterns

**Tenant Identification:**
- Always extract tenant from `x-tenant` header
- Use `SetTenantSchema` middleware for tenant-specific routes
- Validate tenant exists before processing requests

**Schema Naming:**
- Use lowercase `snake_case` schema names
- Avoid reserved PostgreSQL words
- Consider using company/organization identifiers

**Query Isolation:**
- Queries in tenant context use tenant's schema automatically
- Cross-tenant queries must use explicit schema prefixing
- Test with multiple tenant schemas to verify isolation

### Testing with Pest

**Structure:**
- Feature tests in `tests/Feature/` for integration/API tests
- Unit tests in `tests/Unit/` for isolated unit tests
- Use descriptive test names: `it_can_create_tenant_with_migrations()`

**Assertions:**
- Use Pest expectations: `expect($value)->toBe($expected)`
- Chain matchers: `->toBeString()->not->toBeEmpty()`

**Database:**
- Use in-memory SQLite for fast tests
- Set `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`
- Consider `RefreshDatabase` trait for test isolation

### Frontend (Vite + Tailwind)

**JavaScript:**
- ES modules with `import`/`export`
- Use strict mode
- Follow existing patterns in `resources/js/`

**Styling:**
- Tailwind CSS v4
- Utility-first approach
- Avoid custom CSS when Tailwind classes suffice

## File Organization

```
app/
├── Console/Commands/        # Artisan commands
├── Http/
│   ├── Controllers/         # API/resource controllers
│   └── Middleware/          # Route middleware
├── Models/
│   ├── Tenant.php           # Shared tenant model
│   ├── User.php             # Shared user model
│   └── Tenants/             # Tenant-specific models
└── Observers/               # Eloquent observers
database/migrations/
├── *.php                    # Main/shared migrations
└── tenant/                  # Tenant-specific migrations
routes/
├── api.php                  # API routes
└── web.php                  # Web routes
tests/
├── Feature/                 # Integration tests
├── Unit/                    # Unit tests
└── Pest.php                 # Pest configuration
```

## Common Tasks

**Add a new tenant-specific model:**
1. Create model in `app/Models/Tenants/`
2. Create tenant migration: `php artisan tenants:make-migration create_table_table`
3. Add to existing tenant or create new tenant for testing
4. Write tests in `tests/Feature/`

**Add a new shared table:**
1. Create migration in `database/migrations/`
2. Create model in `app/Models/`
3. Add relationships as needed
4. Write tests in `tests/Feature/` or `tests/Unit/`

**Add new API endpoint:**
1. Create/update controller method
2. Add route in `routes/api.php` with `SetTenantSchema` middleware if tenant-specific
3. Write feature tests with tenant header
4. Test with: `curl -H "x-tenant: {id}" http://localhost/api/endpoint`

# Laravel Multi-Tenant Application

This Laravel application implements a multi-tenant architecture using PostgreSQL schemas. Each tenant has its own isolated database schema while sharing the same application codebase.

## Tenant Migration System

### Overview

The application uses a dual migration system:
- **Main migrations**: Located in `database/migrations/` - for shared tables like `tenants`, `users`, etc.
- **Tenant migrations**: Located in `database/migrations/tenant/` - for tenant-specific tables

### Creating Tenant Migration Files

To create a new migration file for tenant-specific tables, use the custom Artisan command:

```bash
php artisan tenants:make-migration create_table_name
```

**Example:**
```bash
php artisan tenants:make-migration create_roles_table
```

This command will:
- Create a new migration file in the `database/migrations/tenant/` directory
- Use the standard Laravel migration naming convention
- Place the migration in the correct path for tenant-specific migrations

### Running Tenant Migrations

#### Automatic Migration (via Observer)

When a new tenant is created, migrations are automatically run through the `TenantObserver`:

```php
// This happens automatically when creating a tenant
$tenant = Tenant::create([
    'name' => 'Company ABC',
    'schema_name' => 'company_abc'
]);
// Observer will create schema and run tenant migrations
```

#### Manual Migration

To manually run tenant migrations for a specific schema:

```bash
php artisan tenants:migrate {schema_name}
```

**Example:**
```bash
php artisan tenants:migrate company_abc
```

This command will:
- Set the PostgreSQL search path to the specified schema
- Run all migrations in `database/migrations/tenant/`
- Display completion message

### SetTenantSchema Middleware

The `SetTenantSchema` middleware is responsible for setting the correct database schema context for tenant-specific requests.

#### How it works:

1. **Header-based tenant identification**: The middleware expects an `x-tenant` header containing the tenant ID
2. **Schema switching**: It sets the PostgreSQL search path to the tenant's schema
3. **Request processing**: Subsequent database queries will use the tenant's schema

#### Usage in Routes:

```php
// In routes/api.php
Route::middleware(SetTenantSchema::class)->group(function () {
    Route::get('/projects', function () {
        return Project::all(); // Will query from tenant's schema
    });
});
```

#### Making Requests with Tenant Context:

When making API requests, include the tenant ID in the header:

```bash
curl -H "x-tenant: 1" -H "Content-Type: application/json" http://your-app.com/api/projects
```

Or in JavaScript:
```javascript
fetch('/api/projects', {
    headers: {
        'x-tenant': '1',
        'Content-Type': 'application/json'
    }
})
```

## Project Structure

### Models

- **Main Models**: `app/Models/` - Shared across all tenants
  - `Tenant.php` - Main tenant model
  - `User.php` - User model (shared)

- **Tenant Models**: `app/Models/Tenants/` - Tenant-specific models
  - `Project.php` - Example tenant-specific model

### Commands

- `app/Console/Commands/CreateTenantMigration.php` - Creates tenant migration files
- `app/Console/Commands/TenantsMigrate.php` - Runs tenant migrations

### Middleware

- `app/Http/Middleware/SetTenantSchema.php` - Sets database schema context

### Observers

- `app/Observers/TenantObserver.php` - Handles tenant creation (schema creation + migrations)

## Best Practices

### 1. Migration Organization
- Keep shared tables in main migrations (`database/migrations/`)
- Keep tenant-specific tables in tenant migrations (`database/migrations/tenant/`)

### 2. Model Organization
- Place tenant-specific models in `app/Models/Tenants/`
- Ensure tenant models don't reference shared tables directly

### 3. Schema Naming
- Use consistent, lowercase schema names
- Consider using company slugs or identifiers
- Avoid special characters and spaces

### 4. Error Handling
- Always validate tenant existence before processing requests
- Handle invalid tenant scenarios gracefully
- Log tenant-related errors for debugging

### 5. Testing
- Test with multiple tenant schemas
- Verify data isolation between tenants
- Test middleware functionality with various headers

## Database Schema Structure

```
PostgreSQL Database
├── public schema (default)
│   ├── tenants
│   ├── users
│   ├── cache
│   └── other shared tables
├── tenant_1 schema
│   ├── projects
│   ├── todos
│   ├── roles
│   └── other tenant-specific tables
└── tenant_2 schema
    ├── projects
    ├── todos
    ├── roles
    └── other tenant-specific tables
```

## Getting Started

1. **Run main migrations**:
   ```bash
   php artisan migrate
   ```

2. **Create a tenant**:
   ```php
   $tenant = Tenant::create([
       'name' => 'My Company',
       'schema_name' => 'my_company'
   ]);
   ```

3. **Create tenant-specific migrations**:
   ```bash
   php artisan tenants:make-migration create_custom_table
   ```

4. **Test tenant context**:
   ```bash
   curl -H "x-tenant: 1" http://your-app.com/api/projects
   ```

## Troubleshooting

### Common Issues

1. **Schema not found**: Ensure the tenant exists and has a valid `schema_name`
2. **Migration failures**: Check PostgreSQL permissions and schema creation
3. **Data isolation**: Verify middleware is applied to tenant-specific routes
4. **Missing tables**: Ensure tenant migrations have been run for the schema

### Debugging Commands

```bash
# Check available schemas
SELECT schema_name FROM information_schema.schemata;

# Check current search path
SHOW search_path;

# List tables in a specific schema
SELECT table_name FROM information_schema.tables WHERE table_schema = 'your_schema_name';
```
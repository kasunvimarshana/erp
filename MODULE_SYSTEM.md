# Module System Documentation

## Overview

The ERP system uses a modular architecture inspired by Odoo's proven design patterns. Each module is an independent, self-contained package that can be enabled, disabled, or extended without affecting other modules.

## Module Structure

### Standard Module Layout

```
modules/
├── iam/
│   ├── src/
│   │   ├── Controllers/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   ├── Models/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   │   ├── api.php
│   │   └── web.php
│   ├── tests/
│   │   ├── Unit/
│   │   └── Feature/
│   ├── resources/
│   │   └── views/
│   ├── config/
│   │   └── iam.php
│   └── module.json
```

## Module Manifest (module.json)

Every module must have a `module.json` file that defines its metadata:

```json
{
  "name": "iam",
  "display_name": "Identity & Access Management",
  "description": "Comprehensive authentication and authorization system",
  "version": "1.0.0",
  "author": "ERP Team",
  "dependencies": [],
  "requires": {
    "php": ">=8.2",
    "laravel": ">=10.0"
  },
  "providers": [
    "Modules\\IAM\\Providers\\IAMServiceProvider"
  ],
  "aliases": {
    "IAM": "Modules\\IAM\\Facades\\IAM"
  },
  "autoload": {
    "psr-4": {
      "Modules\\IAM\\": "src/"
    }
  },
  "extra": {
    "module-type": "core",
    "priority": 1,
    "routes": {
      "api": "routes/api.php",
      "web": "routes/web.php"
    },
    "migrations": "database/migrations",
    "permissions": [
      {
        "name": "iam.users.view",
        "description": "View users"
      },
      {
        "name": "iam.users.create",
        "description": "Create users"
      },
      {
        "name": "iam.users.edit",
        "description": "Edit users"
      },
      {
        "name": "iam.users.delete",
        "description": "Delete users"
      }
    ]
  }
}
```

## Module Types

### 1. Core Modules

**Priority**: 1-10
**Required**: Yes
**Examples**: IAM, Tenant, System

Core modules provide essential functionality that other modules depend on. They cannot be disabled.

```json
{
  "extra": {
    "module-type": "core",
    "priority": 1
  }
}
```

### 2. Base Modules

**Priority**: 11-50
**Required**: Usually
**Examples**: Inventory, CRM, Sales

Base modules provide fundamental business functionality. They can be disabled but are typically needed.

```json
{
  "extra": {
    "module-type": "base",
    "priority": 20
  }
}
```

### 3. Optional Modules

**Priority**: 51-100
**Required**: No
**Examples**: Manufacturing, eCommerce, Advanced Analytics

Optional modules provide additional features that can be enabled as needed.

```json
{
  "extra": {
    "module-type": "optional",
    "priority": 50
  }
}
```

### 4. Integration Modules

**Priority**: 101-200
**Required**: No
**Examples**: Stripe, Shopify, QuickBooks

Integration modules connect to third-party services.

```json
{
  "extra": {
    "module-type": "integration",
    "priority": 100
  }
}
```

## Module Dependencies

### Declaring Dependencies

```json
{
  "dependencies": [
    "iam",
    "tenant"
  ],
  "optional_dependencies": [
    "crm",
    "sales"
  ]
}
```

### Dependency Resolution

The module system automatically:
1. Checks if all required dependencies are installed
2. Loads modules in correct order based on dependencies and priority
3. Prevents circular dependencies
4. Validates version compatibility

## Module Lifecycle

### 1. Installation

```bash
php artisan module:install inventory
```

**Steps**:
1. Copy module files to modules directory
2. Register module in system
3. Run migrations
4. Seed initial data
5. Register permissions
6. Clear cache

### 2. Activation

```bash
php artisan module:enable inventory
```

**Steps**:
1. Verify dependencies
2. Update module status
3. Register routes
4. Register service providers
5. Publish assets
6. Clear cache

### 3. Deactivation

```bash
php artisan module:disable inventory
```

**Steps**:
1. Check dependent modules
2. Update module status
3. Unregister routes
4. Clear cache

### 4. Uninstallation

```bash
php artisan module:uninstall inventory
```

**Steps**:
1. Verify no dependent modules
2. Run rollback migrations (optional)
3. Remove module files
4. Clear cache

## Inter-Module Communication

### Service Layer Pattern

Modules should communicate through service layers, not directly:

```php
// ✅ Good: Using service layer
use Modules\CRM\Services\ContactService;

class SalesOrderService
{
    public function __construct(
        private ContactService $contactService
    ) {}
    
    public function createOrder(array $data)
    {
        $contact = $this->contactService->findById($data['contact_id']);
        // ... create order
    }
}
```

```php
// ❌ Bad: Direct model access
use Modules\CRM\Models\Contact;

class SalesOrderService
{
    public function createOrder(array $data)
    {
        $contact = Contact::find($data['contact_id']); // Direct access
        // ... create order
    }
}
```

### Event-Based Communication

Use events for loose coupling:

```php
// In CRM Module
use Illuminate\Support\Facades\Event;

class ContactService
{
    public function create(array $data)
    {
        $contact = Contact::create($data);
        
        Event::dispatch(new ContactCreated($contact));
        
        return $contact;
    }
}
```

```php
// In Sales Module
use Illuminate\Support\Facades\Event;
use Modules\CRM\Events\ContactCreated;

class SalesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Event::listen(
            ContactCreated::class,
            [ContactCreatedListener::class, 'handle']
        );
    }
}
```

## Module Extension

### Extending Models

```php
// In custom module
namespace Modules\CustomCRM\Models;

use Modules\CRM\Models\Contact as BaseContact;

class Contact extends BaseContact
{
    protected $table = 'crm_contacts'; // Same table
    
    // Add custom methods or relationships
    public function customData()
    {
        return $this->hasOne(ContactCustomData::class);
    }
}
```

### Extending Services

```php
namespace Modules\CustomCRM\Services;

use Modules\CRM\Services\ContactService as BaseContactService;

class ContactService extends BaseContactService
{
    public function create(array $data)
    {
        // Add custom logic before
        $this->validateCustomFields($data);
        
        // Call parent
        $contact = parent::create($data);
        
        // Add custom logic after
        $this->createCustomData($contact, $data);
        
        return $contact;
    }
}
```

### Extending Views

```php
// In module service provider
public function boot()
{
    $this->loadViewsFrom(__DIR__.'/../resources/views', 'custom-crm');
    
    // Override base views
    View::composer('crm::contacts.show', function ($view) {
        $view->with('customData', $this->getCustomData());
    });
}
```

## Module Configuration

### Module-Specific Config

```php
// config/iam.php
return [
    'password_min_length' => env('IAM_PASSWORD_MIN_LENGTH', 8),
    'password_require_special' => env('IAM_PASSWORD_REQUIRE_SPECIAL', true),
    'session_lifetime' => env('IAM_SESSION_LIFETIME', 120),
    'mfa_enabled' => env('IAM_MFA_ENABLED', false),
];
```

### Accessing Configuration

```php
config('iam.password_min_length'); // Module config
config('app.name'); // Global config
```

## Module Permissions

### Defining Permissions

```json
{
  "extra": {
    "permissions": [
      {
        "name": "inventory.products.view",
        "description": "View products",
        "group": "Products"
      },
      {
        "name": "inventory.products.create",
        "description": "Create products",
        "group": "Products"
      },
      {
        "name": "inventory.stock.manage",
        "description": "Manage stock levels",
        "group": "Stock"
      }
    ]
  }
}
```

### Checking Permissions

```php
// In controller
$this->authorize('inventory.products.view');

// In blade
@can('inventory.products.create')
    <button>Create Product</button>
@endcan

// In code
if (auth()->user()->can('inventory.products.view')) {
    // ...
}
```

## Module Testing

### Unit Tests

```php
namespace Modules\Inventory\Tests\Unit;

use Tests\TestCase;
use Modules\Inventory\Services\StockService;

class StockServiceTest extends TestCase
{
    public function test_can_calculate_available_stock()
    {
        $service = new StockService();
        $stock = $service->getAvailableStock($productId, $warehouseId);
        
        $this->assertIsFloat($stock);
    }
}
```

### Feature Tests

```php
namespace Modules\Inventory\Tests\Feature;

use Tests\TestCase;
use Modules\Inventory\Models\Product;

class ProductApiTest extends TestCase
{
    public function test_can_create_product()
    {
        $response = $this->postJson('/api/inventory/products', [
            'sku' => 'TEST-001',
            'name' => 'Test Product',
            'price' => 99.99
        ]);
        
        $response->assertStatus(201)
                ->assertJson(['sku' => 'TEST-001']);
    }
}
```

## Module Commands

### List Modules

```bash
php artisan module:list
```

### Module Information

```bash
php artisan module:info inventory
```

### Create New Module

```bash
php artisan module:make CustomModule
```

### Enable/Disable

```bash
php artisan module:enable inventory
php artisan module:disable inventory
```

### Update Module

```bash
php artisan module:update inventory
```

### Migrate Module

```bash
php artisan module:migrate inventory
php artisan module:migrate-rollback inventory
```

### Seed Module

```bash
php artisan module:seed inventory
```

## Best Practices

### 1. Single Responsibility

Each module should have a clear, single purpose:
- ✅ Good: `inventory` module handles all inventory-related functionality
- ❌ Bad: `misc` module with unrelated features

### 2. Loose Coupling

Modules should not directly depend on each other's internals:
- ✅ Use service layer interfaces
- ✅ Use events for communication
- ❌ Direct model access across modules

### 3. High Cohesion

Related functionality should be in the same module:
- ✅ Product, Stock, Warehouse in `inventory`
- ❌ Product in one module, Stock in another

### 4. Clear Boundaries

Define clear module boundaries:
- Each module has its own database tables
- Each module has its own routes
- Each module has its own permissions

### 5. Documentation

Every module should have:
- README.md with overview
- API documentation
- User guide
- Migration guide

### 6. Testing

Each module should have:
- Unit tests for services
- Feature tests for API endpoints
- Integration tests for workflows

### 7. Versioning

Use semantic versioning:
- MAJOR: Breaking changes
- MINOR: New features, backward compatible
- PATCH: Bug fixes

## Module Development Workflow

1. **Plan**: Define module scope and dependencies
2. **Create**: Use `module:make` command
3. **Develop**: Implement features following structure
4. **Test**: Write comprehensive tests
5. **Document**: Add README and API docs
6. **Version**: Tag with semantic version
7. **Publish**: Make available for installation

## Troubleshooting

### Module Not Loading

1. Check module.json syntax
2. Verify dependencies are installed
3. Clear cache: `php artisan cache:clear`
4. Check logs: `storage/logs/laravel.log`

### Permission Errors

1. Run permission sync: `php artisan module:sync-permissions`
2. Clear cache: `php artisan permission:cache-reset`

### Migration Issues

1. Check migration file syntax
2. Verify database connection
3. Run migrations separately: `php artisan module:migrate inventory`

## Conclusion

The module system provides a flexible, scalable way to organize and extend the ERP system. By following these guidelines, developers can create maintainable, testable, and reusable modules that integrate seamlessly with the platform.

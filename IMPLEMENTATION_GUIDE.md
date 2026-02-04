# Implementation Guide

## Quick Start

This guide provides step-by-step instructions for implementing the ERP system based on the architectural principles defined in `ARCHITECTURE.md`.

## Phase 1: Foundation Setup

### 1.1 Project Initialization

#### Option A: Laravel Backend

```bash
# Create Laravel project
composer create-project laravel/laravel erp-backend
cd erp-backend

# Install essential packages
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require spatie/laravel-multitenancy
composer require spatie/laravel-query-builder
composer require maatwebsite/excel
```

#### Option B: NestJS Backend

```bash
# Create NestJS project
npm i -g @nestjs/cli
nest new erp-backend
cd erp-backend

# Install essential packages
npm install @nestjs/typeorm typeorm pg
npm install @nestjs/passport passport passport-jwt
npm install @nestjs/config
npm install @nestjs/bull bull
npm install @nestjs/swagger
```

#### Frontend Setup (Vue 3 + Vite)

```bash
# Create Vue project
npm create vite@latest erp-frontend -- --template vue-ts
cd erp-frontend

# Install essential packages
npm install vue-router pinia
npm install axios
npm install @vueuse/core
npm install tailwindcss postcss autoprefixer
npm install @headlessui/vue @heroicons/vue
```

### 1.2 Database Setup

```sql
-- Create main database
CREATE DATABASE erp_system;

-- Create user with appropriate privileges
CREATE USER erp_admin WITH ENCRYPTED PASSWORD 'your_secure_password';
GRANT ALL PRIVILEGES ON DATABASE erp_system TO erp_admin;

-- Enable necessary extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm"; -- For full-text search
```

### 1.3 Environment Configuration

**Backend (.env)**
```env
APP_NAME="ERP System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=erp_system
DB_USERNAME=erp_admin
DB_PASSWORD=your_secure_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
```

**Frontend (.env)**
```env
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=ERP System
```

## Phase 2: Core Module Implementation

### 2.1 Multi-Tenancy Setup

#### Database Schema (Row-Level Isolation)

```sql
-- Tenants table
CREATE TABLE tenants (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(255) NOT NULL,
    subdomain VARCHAR(100) UNIQUE NOT NULL,
    database_name VARCHAR(100), -- For schema-per-tenant
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'active',
    settings JSONB DEFAULT '{}'
);

-- Add tenant_id to all tables
-- Example for users table
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    tenant_id UUID NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(tenant_id, email)
);

-- Create index for tenant queries
CREATE INDEX idx_users_tenant_id ON users(tenant_id);
```

#### Tenant Middleware (Laravel Example)

```php
// app/Http/Middleware/SetTenant.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetTenant
{
    public function handle($request, Closure $next)
    {
        // Extract tenant from subdomain or header
        $subdomain = $this->extractSubdomain($request);
        
        if ($subdomain) {
            $tenant = Tenant::where('subdomain', $subdomain)->firstOrFail();
            app()->instance('tenant', $tenant);
            
            // Set tenant context for queries
            config(['database.connections.pgsql.search_path' => $tenant->database_name]);
        }
        
        return $next($request);
    }
    
    private function extractSubdomain($request)
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        return count($parts) > 2 ? $parts[0] : null;
    }
}
```

### 2.2 IAM Module

#### Database Schema

```sql
-- Roles table
CREATE TABLE roles (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    tenant_id UUID NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(tenant_id, name)
);

-- Permissions table
CREATE TABLE permissions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(100) NOT NULL UNIQUE,
    resource VARCHAR(100) NOT NULL,
    action VARCHAR(50) NOT NULL,
    description TEXT
);

-- Role-Permission mapping
CREATE TABLE role_permissions (
    role_id UUID REFERENCES roles(id) ON DELETE CASCADE,
    permission_id UUID REFERENCES permissions(id) ON DELETE CASCADE,
    PRIMARY KEY (role_id, permission_id)
);

-- User-Role mapping
CREATE TABLE user_roles (
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    role_id UUID REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, role_id)
);
```

#### Service Layer (Laravel Example)

```php
// app/Services/IAM/AuthenticationService.php
namespace App\Services\IAM;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthenticationService
{
    public function login(string $email, string $password): array
    {
        $tenant = app('tenant');
        
        $user = User::where('tenant_id', $tenant->id)
                    ->where('email', $email)
                    ->first();
        
        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception('Invalid credentials');
        }
        
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return [
            'user' => $user->load('roles.permissions'),
            'token' => $token
        ];
    }
    
    public function register(array $data): User
    {
        $tenant = app('tenant');
        
        return User::create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
```

### 2.3 Inventory Module

#### Database Schema

```sql
-- Products table
CREATE TABLE products (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    tenant_id UUID NOT NULL REFERENCES tenants(id),
    sku VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    product_type VARCHAR(50) DEFAULT 'physical', -- physical, service, digital
    category_id UUID REFERENCES product_categories(id),
    unit_of_measure VARCHAR(50),
    cost_price DECIMAL(15, 2),
    selling_price DECIMAL(15, 2),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(tenant_id, sku)
);

-- Stock ledger (append-only)
CREATE TABLE stock_ledger_entries (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    tenant_id UUID NOT NULL REFERENCES tenants(id),
    product_id UUID NOT NULL REFERENCES products(id),
    warehouse_id UUID NOT NULL REFERENCES warehouses(id),
    transaction_type VARCHAR(50) NOT NULL, -- in, out, adjustment
    quantity DECIMAL(15, 4) NOT NULL,
    balance_qty DECIMAL(15, 4) NOT NULL, -- Running balance
    unit_cost DECIMAL(15, 2),
    reference_type VARCHAR(100), -- purchase_order, sales_order, adjustment
    reference_id UUID,
    batch_no VARCHAR(100),
    serial_no VARCHAR(100),
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by UUID REFERENCES users(id)
);

-- Create index for ledger queries
CREATE INDEX idx_stock_ledger_product ON stock_ledger_entries(tenant_id, product_id, warehouse_id);
CREATE INDEX idx_stock_ledger_created ON stock_ledger_entries(created_at DESC);
```

#### Service Layer Example

```php
// app/Services/Inventory/StockService.php
namespace App\Services\Inventory;

use App\Models\StockLedgerEntry;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function recordStockMovement(array $data): StockLedgerEntry
    {
        return DB::transaction(function () use ($data) {
            $tenant = app('tenant');
            
            // Get current balance
            $currentBalance = $this->getCurrentBalance(
                $data['product_id'],
                $data['warehouse_id']
            );
            
            // Calculate new balance
            $quantity = $data['transaction_type'] === 'out' 
                ? -abs($data['quantity'])
                : abs($data['quantity']);
            
            $newBalance = $currentBalance + $quantity;
            
            if ($newBalance < 0) {
                throw new \Exception('Insufficient stock');
            }
            
            // Create ledger entry
            return StockLedgerEntry::create([
                'tenant_id' => $tenant->id,
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'transaction_type' => $data['transaction_type'],
                'quantity' => $quantity,
                'balance_qty' => $newBalance,
                'unit_cost' => $data['unit_cost'] ?? null,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'batch_no' => $data['batch_no'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });
    }
    
    private function getCurrentBalance(string $productId, string $warehouseId): float
    {
        $tenant = app('tenant');
        
        $latest = StockLedgerEntry::where('tenant_id', $tenant->id)
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->orderBy('created_at', 'desc')
            ->first();
        
        return $latest ? $latest->balance_qty : 0;
    }
}
```

## Phase 3: Frontend Implementation

### 3.1 Authentication Flow

```typescript
// src/stores/auth.ts
import { defineStore } from 'pinia'
import axios from 'axios'

interface User {
  id: string
  name: string
  email: string
  roles: Role[]
}

interface Role {
  id: string
  name: string
  permissions: Permission[]
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as User | null,
    token: localStorage.getItem('token'),
    loading: false,
  }),
  
  getters: {
    isAuthenticated: (state) => !!state.token,
    hasPermission: (state) => (permission: string) => {
      if (!state.user) return false
      return state.user.roles.some(role =>
        role.permissions.some(p => p.name === permission)
      )
    }
  },
  
  actions: {
    async login(email: string, password: string) {
      this.loading = true
      try {
        const response = await axios.post('/api/auth/login', {
          email,
          password
        })
        
        this.token = response.data.token
        this.user = response.data.user
        localStorage.setItem('token', this.token)
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
      } finally {
        this.loading = false
      }
    },
    
    async logout() {
      await axios.post('/api/auth/logout')
      this.token = null
      this.user = null
      localStorage.removeItem('token')
      delete axios.defaults.headers.common['Authorization']
    },
    
    async fetchUser() {
      if (!this.token) return
      const response = await axios.get('/api/auth/user')
      this.user = response.data
    }
  }
})
```

### 3.2 Module-Based Routing

```typescript
// src/router/index.ts
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      component: () => import('@/views/auth/Login.vue'),
      meta: { guest: true }
    },
    {
      path: '/',
      component: () => import('@/layouts/MainLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: 'dashboard',
          component: () => import('@/views/Dashboard.vue')
        },
        {
          path: 'inventory',
          component: () => import('@/views/inventory/InventoryLayout.vue'),
          meta: { permission: 'inventory.view' },
          children: [
            {
              path: 'products',
              component: () => import('@/views/inventory/Products.vue')
            },
            {
              path: 'stock',
              component: () => import('@/views/inventory/Stock.vue')
            }
          ]
        }
      ]
    }
  ]
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next('/dashboard')
  } else if (to.meta.permission && !authStore.hasPermission(to.meta.permission as string)) {
    next('/403')
  } else {
    next()
  }
})

export default router
```

## Phase 4: Testing

### 4.1 Backend Unit Tests (Laravel PHPUnit)

```php
// tests/Unit/Services/StockServiceTest.php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\Inventory\StockService;
use App\Models\{Product, Warehouse, Tenant};
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;
    
    protected $stockService;
    protected $tenant;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = new StockService();
        $this->tenant = Tenant::factory()->create();
        app()->instance('tenant', $this->tenant);
    }
    
    public function test_can_record_stock_in()
    {
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);
        $warehouse = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);
        
        $entry = $this->stockService->recordStockMovement([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'transaction_type' => 'in',
            'quantity' => 100,
            'unit_cost' => 10.00,
        ]);
        
        $this->assertEquals(100, $entry->balance_qty);
        $this->assertDatabaseHas('stock_ledger_entries', [
            'product_id' => $product->id,
            'quantity' => 100
        ]);
    }
    
    public function test_cannot_record_stock_out_with_insufficient_balance()
    {
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);
        $warehouse = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock');
        
        $this->stockService->recordStockMovement([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'transaction_type' => 'out',
            'quantity' => 50,
        ]);
    }
}
```

### 4.2 Frontend Unit Tests (Vitest)

```typescript
// src/stores/__tests__/auth.spec.ts
import { setActivePinia, createPinia } from 'pinia'
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { useAuthStore } from '../auth'
import axios from 'axios'

vi.mock('axios')

describe('Auth Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })
  
  it('logs in user successfully', async () => {
    const mockResponse = {
      data: {
        token: 'test-token',
        user: { id: '1', name: 'Test User', email: 'test@example.com' }
      }
    }
    
    vi.mocked(axios.post).mockResolvedValue(mockResponse)
    
    const store = useAuthStore()
    await store.login('test@example.com', 'password')
    
    expect(store.isAuthenticated).toBe(true)
    expect(store.user?.name).toBe('Test User')
  })
  
  it('handles login failure', async () => {
    vi.mocked(axios.post).mockRejectedValue(new Error('Invalid credentials'))
    
    const store = useAuthStore()
    
    await expect(store.login('test@example.com', 'wrong')).rejects.toThrow()
    expect(store.isAuthenticated).toBe(false)
  })
})
```

## Phase 5: Deployment

### 5.1 Docker Setup

**docker-compose.yml**
```yaml
version: '3.8'

services:
  postgres:
    image: postgres:15
    environment:
      POSTGRES_DB: erp_system
      POSTGRES_USER: erp_admin
      POSTGRES_PASSWORD: your_secure_password
    volumes:
      - postgres_data:/var/lib/postgresql/data
    ports:
      - "5432:5432"
  
  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
  
  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile
    depends_on:
      - postgres
      - redis
    environment:
      DB_HOST: postgres
      REDIS_HOST: redis
    ports:
      - "8000:8000"
    volumes:
      - ./backend:/app
  
  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile
    ports:
      - "3000:3000"
    volumes:
      - ./frontend:/app
      - /app/node_modules

volumes:
  postgres_data:
```

### 5.2 CI/CD Pipeline (GitHub Actions)

**.github/workflows/ci.yml**
```yaml
name: CI/CD Pipeline

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      postgres:
        image: postgres:15
        env:
          POSTGRES_PASSWORD: postgres
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: pgsql, redis
      
      - name: Install Dependencies
        run: composer install --no-interaction --prefer-dist
        working-directory: ./backend
      
      - name: Run Tests
        run: php artisan test
        working-directory: ./backend
        env:
          DB_CONNECTION: pgsql
          DB_HOST: localhost
          DB_DATABASE: testing
      
      - name: Setup Node
        uses: actions/setup-node@v3
        with:
          node-version: '18'
      
      - name: Install Frontend Dependencies
        run: npm ci
        working-directory: ./frontend
      
      - name: Run Frontend Tests
        run: npm run test:unit
        working-directory: ./frontend
  
  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    
    steps:
      - name: Deploy to Production
        run: |
          # Add deployment steps here
          echo "Deploying to production..."
```

## Next Steps

1. **Implement Additional Modules**: Follow the same pattern for CRM, Sales, Accounting, etc.
2. **Add Advanced Features**: Implement workflow engine, reporting system, analytics
3. **Optimize Performance**: Add caching, optimize queries, implement CDN
4. **Enhance Security**: Add rate limiting, implement MFA, regular security audits
5. **Scale Infrastructure**: Move to Kubernetes, implement microservices for critical modules

## Resources

- [Laravel Documentation](https://laravel.com/docs)
- [NestJS Documentation](https://docs.nestjs.com/)
- [Vue.js Documentation](https://vuejs.org/)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [Docker Documentation](https://docs.docker.com/)

## Support

For questions or issues, please refer to the project documentation or create an issue in the repository.

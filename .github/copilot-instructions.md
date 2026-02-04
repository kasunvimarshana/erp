# Copilot Instructions for ERP System

## Project Overview

This is an enterprise-grade SaaS ERP (Enterprise Resource Planning) system designed for scalability, maintainability, and multi-tenant operations. The system synthesizes best practices from production ERP implementations including multi-x-erp-saas, GlobalSaaS-ERP, UnityERP-SaaS, and Odoo ERP.

## Technology Stack

### Backend (Primary Recommendation)
- **Framework**: Laravel 10.x/11.x (PHP 8.2+)
- **Alternative**: NestJS 10.x (Node.js + TypeScript) or Django 4.2 (Python)
- **Database**: PostgreSQL 15+ (primary), MySQL 8.0 (alternative)
- **Cache/Queue**: Redis 7.x
- **Search**: PostgreSQL Full-Text Search or Elasticsearch 8.x for advanced needs
- **File Storage**: S3/MinIO/Local storage

### Frontend (Primary Recommendation)
- **Framework**: Vue.js 3.4 + Vite 5.x + TypeScript
- **Alternative**: React 18 or Angular 17
- **State Management**: Pinia (Vue) / Redux Toolkit (React) / NgRx (Angular)
- **Styling**: Tailwind CSS 3 + HeadlessUI
- **Testing**: Vitest + Playwright

### Infrastructure
- **Containerization**: Docker + Docker Compose
- **Orchestration**: Kubernetes (production)
- **CI/CD**: GitHub Actions / GitLab CI / Jenkins
- **Monitoring**: Sentry / New Relic / DataDog
- **Logging**: ELK Stack / Graylog

## Architecture Principles

### 1. Clean Architecture & DDD
Follow Clean Architecture with clear separation:
- **Presentation Layer**: Controllers, Views, API Endpoints
- **Application Layer**: Services, Use Cases, DTOs
- **Domain Layer**: Entities, Value Objects, Aggregates
- **Infrastructure Layer**: Repositories, External Services

### 2. Controller → Service → Repository Pattern
- **Controllers**: Handle HTTP requests, validate input, delegate to services
- **Services**: Contain business logic, coordinate between repositories
- **Repositories**: Handle data persistence, abstract database operations

### 3. Modular Design (Inspired by Odoo)
- Each module addresses a single business domain
- Modules can be enabled/disabled independently
- Clear module dependencies via manifest files
- No direct module-to-module dependencies (use service layer mediation)

### 4. SOLID Principles
Always follow SOLID principles:
- Single Responsibility
- Open/Closed
- Liskov Substitution
- Interface Segregation
- Dependency Inversion

## Coding Conventions

### General Guidelines
- Use descriptive variable and function names
- Keep functions small and focused (single responsibility)
- Write self-documenting code; add comments only for complex logic
- Follow DRY (Don't Repeat Yourself) principle
- Prefer composition over inheritance
- Use dependency injection for loose coupling

### PHP/Laravel Specific
- Follow PSR-12 coding standards
- Use type hints for all function parameters and return types
- Use strict typing (`declare(strict_types=1);`)
- Prefer Eloquent ORM over raw queries
- Use Laravel's built-in features (validation, events, queues)
- Name controllers with the singular resource name (e.g., `UserController`, not `UsersController`)
- Use resource controllers for CRUD operations
- Service classes should be in `app/Services` directory
- Repository classes should be in `app/Repositories` directory

### TypeScript/JavaScript Specific
- Use TypeScript for all new code
- Enable strict mode in `tsconfig.json`
- Use interfaces for type definitions
- Prefer `const` over `let`, avoid `var`
- Use arrow functions for callbacks
- Use async/await over promises chains
- Use modern ES6+ features

### Vue.js Specific
- Use Composition API with `<script setup>`
- Prefer composables for reusable logic
- Use TypeScript for all components
- Follow Vue.js 3 style guide
- Use Pinia for state management
- Component names should be multi-word (e.g., `UserList`, `ProductCard`)

### Database
- Always use migrations for schema changes
- Never modify existing migrations; create new ones
- Use transactions for operations affecting multiple tables
- Index foreign keys and commonly queried columns
- Use UUIDs for primary keys in multi-tenant scenarios
- Follow append-only pattern for critical data (like stock ledger)

### Security
- Never commit secrets or API keys
- Always validate and sanitize user input
- Use parameterized queries (never concatenate SQL)
- Implement proper authentication and authorization checks
- Use HTTPS for all production APIs
- Implement rate limiting on public endpoints
- Enable CSRF protection
- Sanitize output to prevent XSS attacks

### Testing
- Write unit tests for all business logic
- Write integration tests for API endpoints
- Target 80%+ code coverage
- Use factories/fixtures for test data
- Mock external dependencies
- Name test methods descriptively (e.g., `test_user_can_create_order`)

### API Design
- Follow RESTful conventions
- Use proper HTTP methods (GET, POST, PUT, PATCH, DELETE)
- Use plural nouns for resource endpoints (e.g., `/api/users`, `/api/products`)
- Return appropriate HTTP status codes
- Use versioning (e.g., `/api/v1/users`)
- Document all endpoints with OpenAPI/Swagger
- Implement pagination for list endpoints
- Return consistent error responses

### Git Workflow
- Write clear, descriptive commit messages
- Use conventional commits format (e.g., `feat:`, `fix:`, `docs:`)
- Keep commits focused and atomic
- Create feature branches from main/master
- Request code reviews before merging
- Squash commits when merging to main

## Multi-X Capabilities

The system supports multiple key features:
- **Multi-Tenancy**: Complete tenant isolation (database-per-tenant, schema-per-tenant, or row-level)
- **Multi-Organization**: Complex organizational hierarchies
- **Multi-Vendor**: Vendor and supplier management
- **Multi-Branch**: Branch and location management
- **Multi-Warehouse**: Distributed inventory
- **Multi-Currency**: Real-time currency conversion
- **Multi-Language**: Full i18n support
- **Multi-Timezone**: Automatic timezone handling
- **Multi-Unit**: Flexible unit of measure management

When implementing features, always consider these multi-x aspects.

## Core Modules

The system includes these core modules:
1. **IAM**: Identity & Access Management (OAuth2, SAML, JWT, RBAC, ABAC, MFA, SSO)
2. **CRM**: Customer Relationship Management
3. **Inventory**: Product, SKU, stock ledger, batch/lot tracking
4. **Procurement**: Purchase orders, vendor management, RFQ
5. **Sales & POS**: Sales orders, quotations, POS integration
6. **Invoicing & Payments**: Invoice generation, payment gateways
7. **Manufacturing**: BOM, production planning, work orders
8. **Accounting & Finance**: Chart of accounts, general ledger, reporting
9. **Human Resources**: Employee, attendance, leave, payroll
10. **Reporting & Analytics**: Custom reports, dashboards, data visualization

## Cross-Cutting Concerns

- **Workflow Engine**: Customizable approval workflows
- **Notification System**: Email, SMS, push, in-app notifications
- **Audit Trail**: Immutable audit logs for all changes
- **Document Management**: File storage and versioning
- **API Integration**: RESTful and GraphQL APIs
- **Webhooks**: Event-driven integrations
- **Import/Export**: Bulk data operations (CSV/Excel)

## Development Workflow

### Before Starting
1. Review existing documentation (ARCHITECTURE.md, IMPLEMENTATION_GUIDE.md, etc.)
2. Understand the module system and dependencies
3. Check for existing tests and maintain consistency

### During Development
1. Create feature branch from main
2. Write tests first (TDD approach preferred)
3. Implement minimal changes to pass tests
4. Run linters and formatters
5. Ensure all tests pass
6. Update documentation if needed

### Before Submitting
1. Run full test suite
2. Check code coverage
3. Run linters and fix issues
4. Update CHANGELOG if applicable
5. Create clear pull request description

## Common Patterns

### Event-Driven Architecture
Use events for loose coupling between modules:
```php
// Dispatch event
event(new OrderCreated($order));

// Listen to event
class SendOrderConfirmationEmail {
    public function handle(OrderCreated $event) {
        // Send email
    }
}
```

### Repository Pattern
Abstract data access:
```php
interface UserRepositoryInterface {
    public function find(int $id): ?User;
    public function create(array $data): User;
}
```

### Service Layer
Encapsulate business logic:
```php
class OrderService {
    public function createOrder(array $data): Order {
        // Validation
        // Business logic
        // Persistence
        return $order;
    }
}
```

## Resources

- **Architecture**: See [ARCHITECTURE.md](../ARCHITECTURE.md)
- **Implementation Guide**: See [IMPLEMENTATION_GUIDE.md](../IMPLEMENTATION_GUIDE.md)
- **Technology Stack**: See [TECHNOLOGY_STACK.md](../TECHNOLOGY_STACK.md)
- **Module System**: See [MODULE_SYSTEM.md](../MODULE_SYSTEM.md)
- **Quick Start**: See [QUICK_START.md](../QUICK_START.md)

## Important Notes

- This project is in early development phase
- Architecture and documentation are established, but implementation is ongoing
- Follow the documented patterns and principles strictly
- When in doubt, refer to the comprehensive documentation in the repository
- Maintain consistency with the existing codebase style
- Prioritize code quality and maintainability over speed
- Security and data integrity are paramount

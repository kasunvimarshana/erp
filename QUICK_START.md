# Quick Start Guide

## Overview

This guide will help you get started with the ERP system quickly, whether you're a developer setting up the project or an end-user learning to use the system.

## For Developers

### Prerequisites

Before you begin, ensure you have the following installed:

- **PHP** 8.2 or higher
- **Composer** 2.x
- **Node.js** 18.x or higher
- **npm** or **yarn**
- **PostgreSQL** 15.x or higher
- **Redis** 7.x or higher
- **Git**

### Option 1: Docker Setup (Recommended)

The fastest way to get started is using Docker:

```bash
# 1. Clone the repository
git clone https://github.com/kasunvimarshana/erp.git
cd erp

# 2. Start all services
docker-compose up -d

# 3. Install backend dependencies
docker-compose exec backend composer install

# 4. Install frontend dependencies
docker-compose exec frontend npm install

# 5. Run database migrations
docker-compose exec backend php artisan migrate --seed

# 6. Access the application
# Frontend: http://localhost:3000
# Backend: http://localhost:8000
# API Docs: http://localhost:8000/api/documentation
```

### Option 2: Manual Setup

#### Step 1: Clone and Configure

```bash
# Clone the repository
git clone https://github.com/kasunvimarshana/erp.git
cd erp
```

#### Step 2: Backend Setup

```bash
cd backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your .env file with database credentials
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=erp_system
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Run migrations and seed data
php artisan migrate --seed

# Start development server
php artisan serve
```

The backend API will be available at `http://localhost:8000`

#### Step 3: Frontend Setup

```bash
cd frontend

# Install dependencies
npm install

# Copy environment file
cp .env.example .env

# Configure your .env file
# VITE_API_URL=http://localhost:8000/api

# Start development server
npm run dev
```

The frontend will be available at `http://localhost:3000`

### Step 4: Create Your First Admin User

```bash
cd backend
php artisan tinker

# In tinker console:
$user = App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => bcrypt('password')
]);

$user->assignRole('admin');
```

### Step 5: Login

1. Open your browser and go to `http://localhost:3000`
2. Login with:
   - Email: `admin@example.com`
   - Password: `password`

## For End Users

### Logging In

1. Navigate to your ERP system URL (provided by your administrator)
2. Enter your email address and password
3. Click "Login"

If you've forgotten your password, click "Forgot Password?" to reset it.

### Dashboard Overview

After logging in, you'll see the main dashboard with:

- **Navigation Menu**: Access all modules from the left sidebar
- **Quick Actions**: Common tasks in the top bar
- **Widgets**: Key metrics and recent activities
- **Notifications**: System alerts in the top right

### Basic Navigation

#### Left Sidebar

- **Dashboard**: Overview and key metrics
- **CRM**: Customer management
- **Inventory**: Products and stock
- **Sales**: Orders and quotations
- **Purchasing**: Purchase orders
- **Invoicing**: Invoice management
- **Reports**: Analytics and reports
- **Settings**: System configuration

#### Top Bar

- **Search**: Quick search across all modules
- **Notifications**: Bell icon for alerts
- **User Menu**: Profile and logout

### Common Tasks

#### Creating a Product

1. Click **Inventory** → **Products**
2. Click **+ New Product**
3. Fill in required fields:
   - SKU (unique identifier)
   - Product Name
   - Category
   - Price
4. Click **Save**

#### Creating a Customer

1. Click **CRM** → **Contacts**
2. Click **+ New Contact**
3. Fill in customer details:
   - Name
   - Email
   - Phone
   - Address
4. Click **Save**

#### Creating a Sales Order

1. Click **Sales** → **Orders**
2. Click **+ New Order**
3. Select customer
4. Add products:
   - Click **Add Item**
   - Select product
   - Enter quantity
5. Review total
6. Click **Create Order**

#### Generating an Invoice

1. Go to **Sales** → **Orders**
2. Open the order
3. Click **Create Invoice**
4. Review invoice details
5. Click **Generate**
6. Download or email to customer

### Getting Help

#### In-App Help

- Click the **?** icon in the top bar
- Hover over field labels for tooltips
- Click **Help** in any module for context-specific guidance

#### Documentation

- Access full documentation from **Settings** → **Help** → **Documentation**
- Watch video tutorials for step-by-step guides

#### Support

Contact your system administrator or support team:
- Email: support@example.com
- Phone: +1 (555) 123-4567
- Support Portal: https://support.example.com

## Module-Specific Quick Starts

### CRM Module

**Key Features:**
- Contact management
- Lead tracking
- Opportunity pipeline
- Activity logging

**Quick Start:**
1. Add contacts: **CRM** → **Contacts** → **+ New**
2. Track leads: **CRM** → **Leads** → **+ New**
3. Create opportunities: **CRM** → **Opportunities** → **+ New**

### Inventory Module

**Key Features:**
- Product catalog
- Stock management
- Warehouse operations
- Stock transfers

**Quick Start:**
1. Set up warehouses: **Inventory** → **Warehouses**
2. Add products: **Inventory** → **Products**
3. Record stock: **Inventory** → **Stock Movements**

### Sales Module

**Key Features:**
- Quotations
- Sales orders
- Delivery notes
- Customer portal

**Quick Start:**
1. Create quotation: **Sales** → **Quotations** → **+ New**
2. Convert to order: Open quotation → **Convert to Order**
3. Process delivery: **Sales** → **Deliveries**

### Purchasing Module

**Key Features:**
- Purchase requisitions
- RFQ management
- Purchase orders
- Goods receipt

**Quick Start:**
1. Create requisition: **Purchasing** → **Requisitions** → **+ New**
2. Generate RFQ: **Purchasing** → **RFQ** → **+ New**
3. Create PO: **Purchasing** → **Orders** → **+ New**

## Keyboard Shortcuts

- **Ctrl/Cmd + K**: Global search
- **Ctrl/Cmd + S**: Save current form
- **Ctrl/Cmd + E**: Edit current record
- **Esc**: Close modal/dialog
- **?**: Show keyboard shortcuts

## Mobile App

Download the mobile app for on-the-go access:

- **iOS**: Available on App Store
- **Android**: Available on Google Play

## Tips and Best Practices

### For Optimal Performance

1. **Use filters**: Narrow down lists with filters
2. **Bookmark frequently used pages**: Use browser bookmarks
3. **Export data**: Use export features for offline analysis
4. **Regular backups**: Administrators should schedule regular backups

### Data Entry Tips

1. **Use templates**: Save time with templates for recurring items
2. **Batch operations**: Use bulk actions for multiple records
3. **Import data**: Use CSV import for large datasets
4. **Auto-complete**: Utilize auto-complete for faster entry

### Security Best Practices

1. **Strong passwords**: Use complex, unique passwords
2. **Enable MFA**: Turn on multi-factor authentication
3. **Review permissions**: Regularly review your access permissions
4. **Logout**: Always logout on shared computers

## Troubleshooting

### Can't Login

- Verify your email and password
- Check CAPS LOCK is off
- Try "Forgot Password" if needed
- Contact your administrator

### Page Not Loading

- Refresh your browser (Ctrl/Cmd + R)
- Clear browser cache
- Try a different browser
- Check your internet connection

### Data Not Saving

- Check for validation errors (red text)
- Ensure all required fields are filled
- Check your internet connection
- Try saving again

### Permission Denied

- You may not have access to this feature
- Contact your administrator to request access
- Check if you're on the correct tenant/organization

## Next Steps

### Developers

1. Read [ARCHITECTURE.md](./ARCHITECTURE.md) for system design
2. Review [IMPLEMENTATION_GUIDE.md](./IMPLEMENTATION_GUIDE.md) for development
3. Explore [MODULE_SYSTEM.md](./MODULE_SYSTEM.md) for module development
4. Check [TECHNOLOGY_STACK.md](./TECHNOLOGY_STACK.md) for tech details

### Users

1. Complete user training modules
2. Explore each module you'll use
3. Customize your dashboard
4. Set up notifications preferences
5. Configure report favorites

### Administrators

1. Complete initial system configuration
2. Set up user roles and permissions
3. Configure integrations
4. Set up backup schedules
5. Configure email notifications
6. Customize company branding

## Resources

### Documentation

- [Architecture Guide](./ARCHITECTURE.md)
- [Implementation Guide](./IMPLEMENTATION_GUIDE.md)
- [Module System](./MODULE_SYSTEM.md)
- [Technology Stack](./TECHNOLOGY_STACK.md)
- [API Documentation](http://localhost:8000/api/documentation)

### Community

- GitHub: https://github.com/kasunvimarshana/erp
- Forum: https://forum.example.com
- Discord: https://discord.gg/example
- Stack Overflow: Tag `erp-system`

### Training

- Video tutorials: https://tutorials.example.com
- Webinars: Monthly feature webinars
- User guides: Downloadable PDF guides
- Certification: ERP certification program

## Support

### Getting Help

- **Documentation**: Check docs first
- **Community Forum**: Ask the community
- **Support Tickets**: For urgent issues
- **Email**: support@example.com
- **Phone**: +1 (555) 123-4567

### Support Hours

- **Community**: 24/7
- **Email/Tickets**: Mon-Fri, 9 AM - 5 PM EST
- **Phone**: Mon-Fri, 9 AM - 5 PM EST
- **Emergency**: 24/7 for critical issues

## Feedback

We value your feedback! Help us improve:

- **Feature Requests**: https://github.com/kasunvimarshana/erp/issues
- **Bug Reports**: https://github.com/kasunvimarshana/erp/issues
- **Surveys**: Quarterly user surveys
- **User Testing**: Join our beta testing program

---

**Welcome to the ERP System! We hope this guide helps you get started quickly. For detailed information, refer to the comprehensive documentation linked throughout this guide.**

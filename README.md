# Budget Manager

Personal budget management MVP application built with PHP using Symfony 6.4 LTS.

## 🚀 Features

- **User Management**
  - User registration and login
  - JWT authentication for API
  - `isActive` flag for each user

- **Budgets**
  - Create unlimited number of budgets
  - Required name and optional description
  - Automatic balance calculation

- **Transactions**
  - Add income and expenses to budgets
  - Required comment for each transaction
  - Complete transaction history

- **User Interface**
  - Responsive design with Bootstrap 5
  - Stimulus for interactivity
  - Clean dashboard with statistics

- **REST API**
  - Complete OpenAPI/Swagger documentation
  - JWT authorization
  - Secure access to own budgets only

## 🛠️ Technologies

- **Backend**: PHP 8.1, Symfony 6.4 LTS
- **Frontend**: Bootstrap 5, Stimulus, SCSS
- **Database**: SQLite (development)
- **Authentication**: JWT Tokens
- **Tools**: Webpack Encore, Doctrine ORM

## 📦 Installation

### Requirements
- PHP 8.1+
- Node.js 20+
- Composer

### Installation Steps

```bash
# Clone repository
git clone <repo-url>
cd budget_manager

# Install dependencies
composer install
npm install

# Generate JWT keys
php bin/console lexik:jwt:generate-keypair

# Setup database and load fixtures
php bin/console app:db:reset --force

# Build assets
npm run build

# Start development server
symfony server:start
```

## 🔧 Configuration

### Environment Variables

Configure in `.env.local` file:

```env
APP_ENV=dev
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_dev.db"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_jwt_passphrase
```

## 🎮 Usage

### Test Account

After loading fixtures, test account is available:
- **Email**: `test@example.com`
- **Password**: `password123`

### URLs

- **Dashboard**: `http://localhost:8000/`
- **API Documentation**: `http://localhost:8000/api/doc`

## 📝 TODO / Future Features

- [ ] Transaction categories
- [ ] Data export to CSV/Excel
- [ ] Charts and statistics
- [ ] Spending limit notifications
- [ ] Mobile API
- [ ] Bank integrations
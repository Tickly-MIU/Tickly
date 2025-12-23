# Tickly - Task Management Application

<div align="center">

![Tickly Logo](public/Tickly_logo.png)

**A modern, full-stack task management application built with Angular and PHP**

[![Angular](https://img.shields.io/badge/Angular-20.0.0-red.svg)](https://angular.io/)
[![PHP](https://img.shields.io/badge/PHP-7.3+-blue.svg)](https://www.php.net/)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.8-blue.svg)](https://www.typescriptlang.org/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-orange.svg)](https://www.mysql.com/)

</div>

---

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Running the Application](#running-the-application)
- [API Documentation](#api-documentation)
- [Architecture](#architecture)
- [Design Patterns](#design-patterns)
- [Testing](#testing)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [License](#license)

---

## 🎯 Overview

**Tickly** is a comprehensive task management application designed to help users organize, track, and manage their daily tasks efficiently. The application features a modern, responsive user interface with robust backend functionality, including user authentication, task categorization, reminders, and an administrative dashboard.

### Key Highlights

- ✅ **Full-stack application** with separated frontend and backend
- ✅ **RESTful API** architecture
- ✅ **Session-based authentication** with secure password reset
- ✅ **Real-time task management** with categories and priorities
- ✅ **Reminder system** with automatic deadline notifications
- ✅ **Admin dashboard** with user statistics and activity logs
- ✅ **Responsive design** built with Tailwind CSS
- ✅ **Production-ready** with deployment configurations

---

## ✨ Features

### User Features

- **Authentication & Authorization**
  - User registration and login
  - Secure password reset via email (OTP-based)
  - Session management
  - Protected routes with guards

- **Task Management**
  - Create, read, update, and delete tasks
  - Task categorization
  - Priority levels (low, medium, high)
  - Task status tracking (pending, completed)
  - Deadline management
  - Task descriptions

- **Reminders**
  - Create custom reminders for tasks
  - Automatic deadline reminders (24 hours before)
  - Email notifications
  - Reminder management (CRUD operations)

- **User Profile**
  - View and edit profile information
  - User statistics dashboard
  - Activity history

### Admin Features

- **User Management**
  - View all users
  - Delete users
  - Update user roles
  - Add new administrators

- **System Overview**
  - User statistics and analytics
  - Activity logs
  - System-wide metrics
  - Dashboard with charts and visualizations

---

## 🛠 Technology Stack

### Frontend

- **Framework**: Angular 20.0.0
- **Language**: TypeScript 5.8
- **Styling**: Tailwind CSS 4.1
- **UI Components**: Flowbite 4.0
- **Charts**: Chart.js 4.5
- **Animations**: AOS (Animate On Scroll) 2.3
- **Icons**: Font Awesome 7.1
- **HTTP Client**: Angular HttpClient (RxJS)
- **Routing**: Angular Router with lazy loading
- **State Management**: Angular Signals

### Backend

- **Language**: PHP 7.3+
- **Architecture**: Custom MVC Framework
- **Database**: MySQL 8.0
- **Session Management**: PHP Sessions
- **Email**: SMTP (via Mailer class)
- **Testing**: PHPUnit
- **Dependencies**: Composer

### Infrastructure

- **Web Server**: Apache (via XAMPP) / Express.js
- **Database Hosting**: Aiven / InfinityFree MySQL
- **Deployment**: Heroku (Backend), Express Server (Frontend)
- **Version Control**: Git

---

## 📁 Project Structure

```
Tickly/
├── Client/                          # Angular Frontend Application
│   ├── src/
│   │   ├── app/
│   │   │   ├── core/               # Core functionality
│   │   │   │   ├── guards/        # Route guards (auth, admin)
│   │   │   │   ├── models/        # TypeScript interfaces
│   │   │   │   └── services/      # API services
│   │   │   ├── features/          # Feature modules
│   │   │   │   ├── auth/          # Authentication (login, register, reset)
│   │   │   │   ├── dashboard/     # Admin dashboard
│   │   │   │   ├── home/          # Task management
│   │   │   │   ├── landing-page/  # Landing page
│   │   │   │   ├── profile/       # User profile
│   │   │   │   └── not-found/     # 404 page
│   │   │   ├── shared/            # Shared components
│   │   │   │   └── components/    # Navbar, Footer
│   │   │   ├── environment/       # Environment configurations
│   │   │   ├── app.routes.ts      # Route definitions
│   │   │   └── app.ts             # Root component
│   │   ├── index.html
│   │   └── main.ts
│   ├── dist/                       # Production build
│   ├── public/                     # Static assets
│   ├── angular.json
│   ├── package.json
│   ├── server.js                   # Express server for production
│   └── tailwind.config.js
│
├── Server/                         # PHP Backend API
│   ├── config/                     # Configuration files
│   │   ├── config.php             # General config
│   │   ├── database.php           # Database connection
│   │   └── mail.php               # Email configuration
│   ├── controllers/               # MVC Controllers
│   │   ├── AdminController.php
│   │   ├── AuthController.php
│   │   ├── CategoryController.php
│   │   ├── PasswordResetController.php
│   │   ├── RemindersController.php
│   │   ├── TasksController.php
│   │   └── UsersController.php
│   ├── core/                      # Framework core
│   │   ├── AuthMiddleware.php    # Authentication middleware
│   │   ├── Controller.php        # Base controller
│   │   ├── Mailer.php            # Email service
│   │   ├── Response.php          # Response handler
│   │   └── Router.php            # Router
│   ├── models/                    # Data models (Repository pattern)
│   │   ├── ActivityLog.php
│   │   ├── Category.php
│   │   ├── PasswordReset.php
│   │   ├── Reminders.php
│   │   ├── Tasks.php
│   │   ├── User.php
│   │   ├── Users.php
│   │   └── UserStatistics.php
│   ├── routes/                    # Route definitions
│   │   └── api.php
│   ├── tests/                     # PHPUnit tests
│   ├── public/                    # Public entry point
│   │   └── index.php
│   ├── composer.json
│   └── phpunit.xml
│
├── public/                         # Root public assets
├── dist/                          # Built frontend (production)
├── .htaccess                      # Apache rewrite rules
├── .gitignore
└── README.md
```

---

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **Node.js** (v18 or higher) and **npm**
- **PHP** (7.3 or higher)
- **Composer** (PHP dependency manager)
- **MySQL** (8.0 or higher) or access to a MySQL database
- **XAMPP** (for local development) or Apache server
- **Git**

### Optional

- **Angular CLI** (globally): `npm install -g @angular/cli`
- **PHPUnit** (for running tests)

---

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd Tickly
```

### 2. Backend Setup

```bash
# Navigate to Server directory
cd Server

# Install PHP dependencies (if any)
composer install

# Configure database connection (see Configuration section)
```

### 3. Frontend Setup

```bash
# Navigate to Client directory
cd Client

# Install Node.js dependencies
npm install

# Configure environment (see Configuration section)
```

---

## ⚙️ Configuration

### Backend Configuration

#### Database Configuration

Edit `Server/config/database.php` or set environment variables:

```php
// For local development (XAMPP)
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=tickly
DB_PORT=3306

// For production (Aiven/Heroku)
// Set these as environment variables in your hosting platform
```

#### Email Configuration

Edit `Server/config/mail.php`:

```php
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
SMTP_FROM=noreply@tickly.com
```

#### Session Configuration

Session settings are configured in `Server/public/index.php`. For production, ensure:
- HTTPS is enabled
- Secure cookies are set
- CORS is properly configured

### Frontend Configuration

#### Environment Files

**Development** (`Client/src/app/environment/environment.ts`):
```typescript
export const environment = {
  production: false,
  API_BASE: 'http://localhost/Tickly/api'  // Local development
};
```

**Production** (`Client/src/app/environment/environment.prod.ts`):
```typescript
export const environment = {
  production: true,
  API_BASE: 'https://your-backend-url.com/api'
};
```

### Apache Configuration (.htaccess)

The `.htaccess` file handles:
- API routing to PHP backend (`/api/*` → `Server/public/index.php`)
- Angular routing (SPA fallback to `index.html`)
- Static file serving

Ensure `mod_rewrite` is enabled in Apache.

---

## 🏃 Running the Application

### Local Development

#### Option 1: XAMPP (Recommended for PHP Backend)

1. **Start XAMPP**:
   - Start Apache and MySQL services

2. **Backend**:
   - Place project in `htdocs/Tickly/`
   - Access API at: `http://localhost/Tickly/api/`

3. **Frontend**:
   ```bash
   cd Client
   npm start
   # or
   ng serve
   ```
   - Access frontend at: `http://localhost:4200/`

#### Option 2: Separate Servers

**Backend (PHP)**:
```bash
cd Server/public
php -S localhost:8000
# API available at: http://localhost:8000/api/
```

**Frontend (Angular)**:
```bash
cd Client
ng serve
# Frontend at: http://localhost:4200/
```

### Production Build

**Build Frontend**:
```bash
cd Client
npm run build
# Output: Client/dist/tickly/browser/
```

**Serve with Express**:
```bash
cd Client
npm start
# Uses server.js to serve built files
```

---

## 📡 API Documentation

### Base URL

- **Local**: `http://localhost/Tickly/api`
- **Production**: `https://tickly-backend-a247ddfb7eba.herokuapp.com/api`

### Authentication

All protected routes require a valid session (set via login).

### Endpoints

#### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/login` | User login | No |
| POST | `/api/register` | User registration | No |
| POST/GET | `/api/logout` | User logout | Yes |
| GET | `/api/session-check` | Check session validity | Yes |
| POST | `/api/forgot-password` | Request password reset | No |
| POST | `/api/reset-password` | Reset password with OTP | No |

#### Tasks

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/tasks` | Get all user tasks | Yes |
| POST | `/api/tasks/create` | Create new task | Yes |
| POST | `/api/tasks/show` | Get single task | Yes |
| POST | `/api/tasks/update` | Update task | Yes |
| POST | `/api/tasks/delete` | Delete task | Yes |

#### Categories

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/category/create` | Create category | Yes |
| POST | `/api/category/read` | Get categories | Yes |
| POST | `/api/category/update` | Update category | Yes |
| POST | `/api/category/delete` | Delete category | Yes |

#### Reminders

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/reminders/create` | Create reminder | Yes |
| GET | `/api/reminders` | Get user reminders | Yes |
| POST | `/api/reminders/update` | Update reminder | Yes |
| POST | `/api/reminders/delete` | Delete reminder | Yes |

#### User Profile

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/profile` | Get user profile | Yes |

#### Admin (Admin Only)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/admin/users` | Get all users | Admin |
| GET | `/api/admin/statistics` | Get user statistics | Admin |
| GET | `/api/admin/activity-logs` | Get activity logs | Admin |
| GET | `/api/admin/overview` | Get system overview | Admin |
| POST | `/api/admin/user/delete` | Delete user | Admin |
| POST | `/api/admin/user/role` | Update user role | Admin |
| POST | `/api/admin/add-admin` | Add new admin | Admin |

### Response Format

All API responses follow this structure:

```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... },
  "timestamp": "2025-01-XX XX:XX:XX"
}
```

### Error Responses

```json
{
  "success": false,
  "message": "Error message",
  "data": [],
  "timestamp": "2025-01-XX XX:XX:XX"
}
```

---

## 🏗 Architecture

### Backend Architecture (MVC)

```
Request → Router → Controller → Model → Database
                ↓
            Response (JSON)
```

**Components**:
- **Router**: Routes HTTP requests to appropriate controllers
- **Controllers**: Handle business logic and request processing
- **Models**: Data access layer (Repository pattern)
- **Middleware**: Authentication and authorization
- **Response**: Standardized JSON responses

### Frontend Architecture (Component-Based)

```
Route → Guard → Component → Service → HTTP → Backend API
                ↓
            Template (View)
```

**Components**:
- **Routes**: Lazy-loaded route definitions
- **Guards**: Route protection (auth, admin)
- **Components**: UI components with templates
- **Services**: API communication and business logic
- **Models**: TypeScript interfaces for type safety

### Design Patterns

The project implements **18 design patterns** across both layers:

#### Backend (PHP)
1. **MVC Pattern** - Model-View-Controller separation
2. **Repository Pattern** - Data access abstraction
3. **Factory Pattern** - Model instantiation
4. **Template Method Pattern** - Base controller structure
5. **Singleton Pattern** - Database connection management
6. **Strategy Pattern** - Router dispatch strategies
7. **Middleware Pattern** - Authentication middleware
8. **Facade Pattern** - Response facade
9. **Dependency Injection** - Constructor injection
10. **Observer Pattern** - Activity logging

#### Frontend (Angular/TypeScript)
1. **Component-Based Architecture** - Angular components
2. **Service Pattern** - Dependency injection services
3. **Guard Pattern** - Route guards
4. **Observer Pattern** - RxJS Observables
5. **Interface Pattern** - TypeScript interfaces
6. **Strategy Pattern** - Guard strategies
7. **Lazy Loading Pattern** - Route lazy loading
8. **Signal Pattern** - Angular Signals

For detailed analysis, see [`DESIGN_PATTERNS_AND_OOP_ANALYSIS.md`](DESIGN_PATTERNS_AND_OOP_ANALYSIS.md).

---

## 🧪 Testing

### Backend Tests (PHPUnit)

```bash
cd Server
phpunit
# or
vendor/bin/phpunit
```

**Test Files**:
- `Server/tests/Controllers/AuthControllerTest.php`
- `Server/tests/Controllers/TasksControllerTest.php`
- `Server/tests/Controllers/CategoryControllerTest.php`
- `Server/tests/Controllers/UsersControllerTest.php`

### Frontend Tests (Karma/Jasmine)

```bash
cd Client
npm test
# or
ng test
```

**Test Files**:
- Component spec files: `*.spec.ts`
- Service spec files: `*.spec.ts`
- Guard spec files: `*.spec.ts`

---

## 🚢 Deployment

### Backend Deployment (Heroku)

1. **Prepare Heroku**:
   ```bash
   cd Server
   heroku create tickly-backend
   ```

2. **Set Environment Variables**:
   ```bash
   heroku config:set DB_HOST=your-db-host
   heroku config:set DB_USER=your-db-user
   heroku config:set DB_PASS=your-db-pass
   heroku config:set DB_NAME=your-db-name
   ```

3. **Deploy**:
   ```bash
   git push heroku main
   ```

### Frontend Deployment

**Option 1: Express Server**
```bash
cd Client
npm run build
npm start  # Uses server.js
```

**Option 2: Static Hosting**
- Build: `npm run build`
- Deploy `dist/tickly/browser/` to:
  - Netlify
  - Vercel
  - GitHub Pages
  - Apache/Nginx

### Environment Variables

Ensure production environment variables are set:
- Database credentials
- SMTP email settings
- API base URLs
- CORS allowed origins

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Code Style

- **PHP**: Follow PSR-12 coding standards
- **TypeScript**: Follow Angular style guide
- **Commit Messages**: Use conventional commits format

---

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

## 👥 Authors

- **Development Team** - Tickly Project

---

## 🙏 Acknowledgments

- Angular team for the excellent framework
- PHP community for robust backend solutions
- Tailwind CSS for beautiful styling utilities
- All open-source contributors

---

## 📞 Support

For support, email support@tickly.com or open an issue in the repository.

---

## 🔗 Links

- **Live Demo**: [https://tickly.page.gd](https://tickly.page.gd)
- **Backend API**: [https://tickly-backend-a247ddfb7eba.herokuapp.com](https://tickly-backend-a247ddfb7eba.herokuapp.com)
- **Documentation**: See [`DESIGN_PATTERNS_AND_OOP_ANALYSIS.md`](DESIGN_PATTERNS_AND_OOP_ANALYSIS.md)

---

<div align="center">

**Built with ❤️ using Angular and PHP**

[⬆ Back to Top](#tickly---task-management-application)

</div>


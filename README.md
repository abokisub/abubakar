# 🚀 KoboPoint - VTU Platform

A comprehensive Virtual Top-Up (VTU) platform built with Laravel and React, featuring data/airtime purchases, bill payments, cable subscriptions, and more.

## ✨ Features

- 📱 **Data & Airtime Purchase** - MTN, GLO, 9Mobile, Airtel
- 📺 **Cable TV Subscriptions** - DSTV, GOTV, Startimes, etc.
- ⚡ **Electricity Bills** - All major discos
- 💳 **Payment Integration** - Paystack, Monnify, Xixapay
- 👥 **User Management** - Multiple user types (Admin, Agent, API, etc.)
- 📊 **Admin Dashboard** - Complete transaction management
- 🔐 **API Access** - RESTful API for integrations
- 📱 **Mobile App Support** - API endpoints for mobile applications

## 🎯 Zero-Config cPanel Deployment

This project is **optimized for shared hosting (cPanel)** with zero-configuration deployment:

- ✅ **No CLI Required** - Upload and configure via cPanel
- ✅ **Auto-Detection** - Automatically detects localhost vs production
- ✅ **No Hardcoded URLs** - All URLs from environment variables
- ✅ **Beginner-Friendly** - Step-by-step deployment guide

**👉 See [README_DEPLOYMENT.md](./README_DEPLOYMENT.md) for deployment instructions**

## 📚 Documentation

- **[DEPLOYMENT_GUIDE.md](./DEPLOYMENT_GUIDE.md)** - Complete deployment guide
- **[README_DEPLOYMENT.md](./README_DEPLOYMENT.md)** - Quick deployment reference
- **[REFACTORING_SUMMARY.md](./REFACTORING_SUMMARY.md)** - Technical changes summary

## 🛠️ Tech Stack

**Backend:**
- Laravel 8
- PHP 7.3+/8.0+
- MySQL
- Laravel Sanctum (API Authentication)

**Frontend:**
- React 18
- Material-UI (MUI)
- Redux Toolkit
- React Router v6
- Axios

## 🚀 Quick Start (Development)

### Backend Setup
```bash
# Install dependencies
composer install

# Copy environment file
cp env.example.laravel .env

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate

# Start server
php artisan serve
```

### Frontend Setup
```bash
cd frontend

# Install dependencies
npm install

# Copy environment file
cp env.example.react .env

# Start development server
npm start
```

## 📦 Production Deployment

For cPanel deployment, see **[DEPLOYMENT_GUIDE.md](./DEPLOYMENT_GUIDE.md)**

Quick steps:
1. Upload files to cPanel
2. Create MySQL database
3. Update `.env` file
4. Import database
5. Done!

## 🔧 Environment Variables

### Required (Laravel)
- `APP_URL` - Application URL
- `FRONTEND_URL` - Frontend URL
- `API_URL` - API endpoint URL
- `DB_*` - Database credentials
- `APP_KEY` - Application encryption key
- `ADEX_APP_KEY` - Allowed origins

### Required (React)
- `REACT_APP_API_URL` - Backend API URL
- `REACT_APP_APP_URL` - Application URL

See `env.example.laravel` and `frontend/env.example.react` for complete list.

## 📝 License

This project is proprietary software.

## 🙏 Credits

Built with Laravel and React

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

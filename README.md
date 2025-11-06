# PHP Engineer panel assignment

## Quick Start

### Automated Setup (Recommended)
```bash
./setup.sh
```

This will:
- Install all dependencies (Composer & npm)
- Setup environment file
- Generate application key
- Create SQLite database
- Run migrations
- Build frontend assets

### Manual Setup
See [IMPLEMENTATION_PLAN.md](IMPLEMENTATION_PLAN.md) for detailed manual setup instructions.

### After Setup

1. **Configure Geoapify API Key**:
   ```bash
   # Edit .env and add your API key
   GEOAPIFY_API_KEY=your_actual_api_key_here
   ```

2. **Start the application**:
   ```bash
   # Terminal 1: Laravel server
   php artisan serve

   # Terminal 2: Queue worker
   php artisan queue:work

   # Terminal 3 (optional): Vite for hot reload
   npm run dev
   ```

3. **Visit**: http://localhost:8000

---
## Architecture Highlights

### Domain-Driven Design
```
app/
├── Domain/AddressValidation/    # Business logic
│   ├── Actions/                 # Use cases
│   ├── Services/                # Business services  
│   ├── ValueObjects/            # Domain concepts
│   └── DTOs/                    # Data transfer objects
├── Application/Http/            # HTTP layer
└── Infrastructure/              # External services
```

### API Endpoints
```
POST   /api/csv-uploads              - Upload CSV
GET    /api/csv-uploads              - List uploads
GET    /api/csv-uploads/{id}         - Get upload status
GET    /api/csv-uploads/{id}/results - Get validation results
GET    /api/csv-uploads/{id}/statistics - Get statistics
```

## Tech Stack

- **Backend**: Laravel 11, PHP 8.2+
- **Frontend**: Vue 3, Inertia.js, Tailwind CSS
- **Database**: SQLite (configurable to MySQL/PostgreSQL)
- **Queue**: Laravel Queues (database driver)
- **CSV Parsing**: League CSV
- **HTTP Client**: Laravel HTTP
- **Testing**: PHPUnit
- **Validation Provider**: Geoapify (with Manager pattern for easy provider switching)
## License
MIT

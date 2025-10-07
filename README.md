# Event Management Portal - Backend (Laravel)

## Requirements
- PHP 8.x
- Composer
- MySQL
- Node/npm (optional for assets)
- Optional: laravel-websockets for local broadcasting, or Pusher account

## Setup
1. Clone repo `cp .env.example .env`
2. `cp .env.example .env` and configure DB + broadcast pusher keys
3. `composer install`
4. `php artisan key:generate`
5. `php artisan migrate`
6. `php artisan storage:link`
7. If using laravel-websockets:
   - `php artisan websockets:install`
   - `php artisan migrate` (websockets adds migrations)
   - `php artisan websockets:serve` (start websocket server)
8. Run app: `php artisan serve` (default http://localhost:8000)

## API endpoints
- POST `/api/login` — body `{ username, password }`
- POST `/api/logout` — header: `Authorization: Bearer <token>`
- GET `/api/events?tz=Asia/Kolkata` — list published events, converted to timezone
- POST `/api/events` (multipart) — fields: `title`, `description`, `category_id`, `publish_date (Y-m-d)`, `publish_time (H:i)`, `timezone`, `photos[]`
- DELETE `/api/events/{id}?force=1` — delete event (force=1 for permanent)

## Admin endpoints
- GET `/api/admin/events?status=published|scheduled|all`
- POST `/api/admin/categories` — add category with optional `parent_id`
- GET `/api/admin/categories` — list categories (nested)

## Real-time logout
- Configure broadcasting driver to `pusher` and either use Pusher or `laravel-websockets`.
- Client must subscribe to `private-user.{id}` and compare `new_token_id` payload.

## Notes
- All `publish_at` times stored in UTC.
- Use `X-Timezone` header or `tz` query param to display times correctly on clients.
- Login route is rate-limited.


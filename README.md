# Event Management Portal - Backend (Laravel)

## Requirements
- PHP 8.x
- Composer
- MySQL


## Setup
1. Clone repo `https://github.com/Atish721/event-management-assessment.git`
2. `cp .env.example .env` and configure DB
3. `composer install`
4. `php artisan key:generate`
5. `php artisan migrate`
6. `php artisan storage:link`
8. Run app: `php artisan serve` (default http://localhost:8000)

## API endpoints
- `POST http://127.0.0.1:8000/api/login`
- Headers : `Content-Type: application/json`
- Payload :
```
{
    "username": "admin",
    "password": "admin123",
    "timezone": "Asia/Kolkata"
}
```

### `GET http://127.0.0.1:8000/api/check-auth`
- Headers : - `Authorization: Bearer your_token_here`
               `X-Login-Token: your_login_token_here`


### `POST http://127.0.0.1:8000/api/logout`
- Headers : - `Authorization: Bearer your_token_here`
               `X-Login-Token: your_login_token_here`


### `GET http://127.0.0.1:8000/api/categories`
- Headers : - `Authorization: Bearer your_token_here`
               `X-Login-Token: your_login_token_here`


### `GET http://127.0.0.1:8000/api/categories/nested`
- Headers : - `Authorization: Bearer your_token_here`
               `X-Login-Token: your_login_token_here`


### `POST http://127.0.0.1:8000/api/categories`
- Headers : - `Authorization: Bearer your_token_here`
               `X-Login-Token: your_login_token_here`
               `Content-Type: application/json`
- Payload :
```
{
    "name": "Test Category",
    "parent_id": null
}
```

### `GET http://127.0.0.1:8000/api/events`
- Headers : - `Authorization: Bearer your_token_here`
               `X-Login-Token: your_login_token_here`


### `GET http://127.0.0.1:8000/api/admin/events?filter=all`
- Query Parameters : - `filter=all` or `filter=published` or `filter=waiting`
- Headers : - `Authorization: Bearer your_token_here`
               `X-Login-Token: your_login_token_here`


### `POST http://127.0.0.1:8000/api/events`
- Query Parameters : - `filter=all` or `filter=published` or `filter=waiting`
- Headers : - `Authorization: Bearer your_token_here`
               `X-Login-Token: your_login_token_here`
               `Content-Type: multipart/form-data`

```
title: "New Test Event"

description: "This is a test event description"

category_id: 1

publish_date: "2024-01-20 14:00:00"

photos[]: (select image files)
```


### `DELETE http://127.0.0.1:8000/api/events/1`
- Query Parameters : - `filter=all` or `filter=published` or `filter=waiting`
- Headers : - `Authorization: Bearer your_token_here`
               `X-Login-Token: your_login_token_here`

## Notes
- All `publish_at` times stored in UTC.
- Login route is rate-limited.


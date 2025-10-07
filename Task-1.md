# Завдання 1. API для управління замовленнями

Створіть невеликий проєкт на базі Laravel (можна використати існуючий), в якому реалізуйте API для управління замовленнями.

### Вимоги:
- Модель повинна містити як мінімум поля: статус, ціна, дата створення.
- Створіть окремі маршрути для:
  • створення замовлення
  • оновлення статусу
  • виводу списку з фільтрацією
- Не використовуйте стандартний php artisan make:resource.
- Валідацію запитів та авторизацію реалізуйте власноруч (через middleware або інші механізми).
- API має повертати відповіді у власному форматі (не стандартному Laravel), наприклад:
  {
  "statusCode": 200,
  "payload": {...},
  "message": "Order created"
  }

### Що надати:
- структура маршруту
- приклад запиту та відповіді (JSON)
- короткий опис логіки

# Реалізація:
## Orders API (Laravel skeleton)

Невеликий приклад API для управління замовленнями на базі Laravel (можна вбудувати існуючий проект).
Реалізовано:
- Модель замовлення з полями: статус, ціна, дата створення (created_at)
- Маршрути:
- POST /api/orders - створення замовлення
- PATCH /api/orders/{order}/status — оновлення статусу
- GET /api/orders — список із фільтрацією
- Кастомний формат відповідей (не стандартний Laravel Resource)
- Своя авторизація та валідація через middleware (без make: resource)

## Структура файлів
- routes/api.php - маршрути API
- app/Http/Controllers/OrderController.php - контролер замовлень (ручна валідація, без Resource)
- app/Http/Middleware/ApiAuth.php - проста авторизація по API-ключу (X-API-KEY)
- app/Http/Middleware/ValidateJson.php — перевірка Content-Type та валідності JSON
- app/Http/Responses/ApiResponse.php - помічник для формування відповідей в єдиному форматі
- app/Models/Order.php - модель замовлення
- database/migrations/2025_10_06_000000_create_orders_table.php - міграція для таблиці orders

## Реєстрація middleware (у існуючому Laravel-проекті)
У app/Http/Kernel.php потрібно додати аліаси:

```
protected $routeMiddleware = [
    // ...
    'api.auth' => \App\Http\Middleware\ApiAuth::class,
    'validate.json' => \App\Http\Middleware\ValidateJson::class,
];
```

## Змінні оточення
В .env задайте ключ для API:

```
API_KEY=secret123
```

## Маршрути
Визначено в routes/api.php:
- POST /api/orders (middleware: api, api.auth, validate.json)
- PATCH /api/orders/{order}/status (middleware: api, api.auth, validate.json)
- GET /api/orders (middleware: api, api.auth)

## Приклад запитів та відповідей

1) Створення замовлення
- Запит:
```
POST /api/orders
X-API-KEY: secret123
Content-Type: application/json

{
  "name": "Test User" 
  "price": 1499.50,
  "status": "new"   // необов'язкове поле за замовчуванням "new"; допустимо: new, processing, completed, cancelled
}
```
- Успішна відповідь:
```
{
  "statusCode": 201,
  "payload": {
    "id": 1,
    "status": "new",
    "price": 1499.5,
    "created_at": "2025-10-06T13:52:00Z",
    "updated_at": "2025-10-06T13:52:00Z"
  },
  "message": "Order created"
}
```
- Помилка валідації (приклад):
```
{
  "statusCode": 422,
  "payload": {
    "errors": {
      "price": "Price is required"
    }
  },
  "message": "Validation failed"
}
```

2) Оновлення статусу замовлення
- Запит:
```
PATCH /api/orders/1/status
X-API-KEY: secret123
Content-Type: application/json

{
  "status": "processing"
}
```
- Успішна відповідь:
```
{
  "statusCode": 200,
  "payload": {
    "id": 1,
    "status": "processing",
    "price": 1499.5,
    "created_at": "2025-10-06T13:52:00Z",
    "updated_at": "2025-10-06T14:03:23Z"
  },
  "message": "Status updated"
}
```
- Помилка валідації (неправильний статус):
```
{
  "statusCode": 422,
  "payload": {
    "errors": {
      "status": "Status is required and must be one of: new, processing, completed, cancelled"
    }
  },
  "message": "Validation failed"
}
```

3) Список замовлень із фільтрацією
   Query-параметри, що підтримуються:
- status (new|processing|completed|cancelled)
- date_from (YYYY-MM-DD)
- date_to (YYYY-MM-DD)
- price_min (число)
- price_max (число)
- paginate=true|false (по умолчанию true)
- per_page=15 (1..100)

- Запит:
```
GET /api/orders?status=completed&date_from=2025-10-01&price_min=1000&paginate=false
X-API-KEY: secret123
```
- Успішна відповідь (без пагінації):
```
{
  "statusCode": 200,
  "payload": {
    "items": [
      {
        "id": 2,
        "status": "completed",
        "price": 2200,
        "created_at": "2025-10-02T10:00:00Z",
        "updated_at": "2025-10-03T11:00:00Z"
      }
    ]
  },
  "message": "Orders list"
}
```
- Успішна відповідь (з пагінацією за умовчанням):
```
{
  "statusCode": 200,
  "payload": {
    "items": [ /* ... поточна сторінка ... */ ],
    "pagination": {
      "total": 37,
      "per_page": 15,
      "current_page": 1,
      "last_page": 3
    }
  },
  "message": "Orders list"
}
```

## Короткий опис логіки
- Кастомний формат відповіді забезпечується класом app/Http/Responses/ApiResponse.php.
- Авторизація реалізована через middleware app/Http/Middleware/ApiAuth.php: перевіряється заголовок X-API-KEY проти значення API_KEY в .env. При помилці – відповідь 401 у кастомному форматі.
- Валідація:
    - На рівні middleware ValidateJson – перевірка Content-Type та коректності JSON.
    - У контролері OrderController – ручна перевірка полів для створення та зміни статусу.
- Список замовлень підтримує фільтри за статусом, датою створення (діапазон), ціною (діапазон), а також пагінацією (вкл/викл).
- Не використовується стандартний make: resource; серіалізація – силами Eloquent + наш формат відповіді.

## Модель та міграція
- Модель: app/Models/Order.php з полями status (string), price (decimal/float cast), created_at/updated_at (timestamps).
- Міграція: database/migrations/2025_10_06_000000_create_orders_table.php створює таблицю orders: id, status, price, timestamps.


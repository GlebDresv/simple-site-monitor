# Simple Uptime Monitor

Monorepo: Laravel 12 API + React Admin frontend + PostgreSQL + Redis.

## Структура

```
./
├── api/                      # Laravel 12 + Sanctum SPA, Dockerfile для dev (PHP-FPM)
├── docker/
│   └── nginx/                # nginx для локальной разработки
├── frontend/                 # React + react-admin
├── railway/                  # production-деплой (Dockerfile, nginx, supervisor)
├── railway.toml              # конфиг Railway (только в корне репозитория)
└── docker-compose.yml        # локальная разработка
```

## Локальный запуск

```bash
docker compose up --build
```

Приложение: http://localhost  
API: http://localhost/api (префикс снимается nginx перед передачей в PHP)

Переменные Laravel — в `api/.env` (скопируйте из `api/.env.example`).

## Авторизация

- Sanctum SPA Authentication (cookie + CSRF)
- Главная страница — форма входа с ссылкой на регистрацию
- После успешного входа/регистрации — редирект в `/cabinet`

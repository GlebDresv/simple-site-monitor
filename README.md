# Simple Uptime Monitor (SUM)

Сервис мониторинга доступности сайтов и доменов: периодические HTTP-проверки, журнал ответов, уведомления в Telegram при смене статуса. Монорепозиторий с Laravel API, React-кабинетом, PostgreSQL и Redis.

## Содержание

- [Возможности](#возможности)
- [Архитектура](#архитектура)
- [Стек технологий](#стек-технологий)
- [Структура репозитория](#структура-репозитория)
- [Модель данных](#модель-данных)
- [Как работает мониторинг](#как-работает-мониторинг)
- [Уведомления](#уведомления)
- [Веб-интерфейс](#веб-интерфейс)
- [API](#api)
- [Локальная разработка](#локальная-разработка)
- [Переменные окружения](#переменные-окружения)
- [Production-деплой](#production-деплой)
- [Демо-деплой на Railway](#демо-деплой-на-railway)
- [Фоновые процессы и обслуживание](#фоновые-процессы-и-обслуживание)

## Возможности

- **Учётные записи** — регистрация, вход, выход; изоляция данных по пользователю.
- **Домены** — добавление URL или имени хоста (без схемы подставляется `https://`); привязка к профилю проверки; отображение последнего статуса (`up` / `down` / `unknown`).
- **Профили проверки** — HTTP-метод (`GET` / `HEAD`), таймаут запроса, интервал (1, 2, 3, 5, 10, 20, 30 или 60 минут), связь с настройками уведомлений.
- **Проверки** — фоновые HTTP-запросы с учётом редиректов (до 4), замер времени ответа, сохранение кода, заголовков и тела (для `GET`, до 64 КБ).
- **Логи** — история проверок с фильтрацией по домену; автоматическая очистка записей старше заданного срока.
- **Уведомления** — Telegram при падении и/или восстановлении; повтор при rate limit API Telegram.

## Архитектура

### Запросы из браузера

```
  Браузер
     │
     ▼
  nginx (:80)
     ├── /      ──► React (кабинет, вход)
     └── /api/* ──► Laravel API (Sanctum, REST)
                        │
            ┌───────────┴───────────┐
            ▼                       ▼
      PostgreSQL                 Redis
      (данные)              (очереди, кэш статусов)
```

Все запросы идут на один хост; nginx маршрутизирует по пути. Префикс `/api` снимается перед передачей в PHP.

### Фоновый мониторинг

```
  schedule:work          queue: domain-checks      queue: default
  (каждую минуту)              │                         │
       │                       ▼                         ▼
       └──► ставит job ──► воркер ──► HTTP к домену   воркер ──► Telegram
                    │              │
                    └──────────────┴──► лог в PostgreSQL, статус в Redis
```

Планировщик раз в минуту выбирает домены по интервалу проверки и кладёт задачи в Redis. Воркер выполняет HTTP-проверку; при смене статуса отдельная задача уходит в очередь `default` для уведомления.

### Очередь `domain-checks` и масштабирование

Проверки доменов вынесены в **отдельную очередь** (`CheckDomainJob::QUEUE = 'domain-checks'`), а уведомления остаются в `default`. Это сделано намеренно: при росте числа доменов узким местом станут именно HTTP-проверки (исходящий трафик, таймауты, параллелизм), а не API и не отправка в Telegram.

| Очередь | Нагрузка | Как масштабировать |
|---------|----------|-------------------|
| `domain-checks` | HTTP к внешним URL, запись логов, обновление статуса | **Первый кандидат на горизонтальное масштабирование** — поднимать дополнительные реплики `worker-domain-checks` |
| `default` | Короткие задачи уведомлений | Обычно хватает одного воркера; масштабировать при отставании очереди |
| `scheduler` | Раз в минуту, только постановка job | **Один экземпляр** (`schedule:work`), дублировать нельзя |

В `docker-compose.yml` для локальной разработки уже заложена эта схема: сервисы `worker` и `worker-domain-checks` разделены. Для production ориентируйтесь на ту же раскладку (см. [Production-деплой](#production-деплой)), а не на all-in-one образ Railway.

`CheckDomainJob` реализует `ShouldBeUnique` с `uniqueId()` по `domain_id` и `uniqueFor = 120`: пока для домена уже есть задача в очереди или в работе, повторная не ставится; при нескольких воркерах блокировка общая (через cache/Redis).

### Локальная разработка и демо на Railway

| | `docker compose` (dev + эталон prod) | Railway (`railway/`) |
|---|--------------------------------------|----------------------|
| Назначение | Локальная разработка; **референсная схема для production** | Упрощённый **демо-деплой** «в один клик» |
| Фронтенд | Vite dev server в контейнере `frontend` | Статика из `npm run build` в том же образе |
| nginx / API | Раздельные контейнеры | PHP-FPM + nginx в одном контейнере |
| Воркеры и scheduler | `scheduler`, `worker`, `worker-domain-checks` — отдельные сервисы | Всё в одном контейнере через `supervisord` |
| Масштабирование воркеров | `docker compose up --scale worker-domain-checks=N` | Не предусмотрено (демо) |

## Стек технологий

| Слой | Технологии |
|------|------------|
| Backend | PHP 8.4, Laravel 12, Laravel Sanctum (SPA), laravel-notification-channels/telegram |
| Frontend | React 19, react-admin 5, Vite 8, TypeScript |
| БД | PostgreSQL 16 |
| Кэш / очереди / статус домена | Redis 7 |
| Локальная / production-инфраструктура | Docker Compose, nginx, раздельные воркеры |
| Демо-деплой | Railway, all-in-one Dockerfile (`railway/`), supervisord |

## Структура репозитория

```
./
├── api/                          # Laravel 12 API
│   ├── app/
│   │   ├── Console/Commands/     # check-logs:prune и др.
│   │   ├── Enums/                # DomainStatus, CheckInterval, HttpMethod
│   │   ├── Http/Controllers/     # REST + Auth
│   │   ├── Jobs/                 # CheckDomainJob, SendDomainStatusChangedNotification
│   │   ├── Models/
│   │   ├── Notifications/
│   │   └── Services/
│   │       ├── DomainCheck/      # checker, scheduler, status store/updater
│   │       └── Telegram/
│   ├── config/domain_check.php   # срок хранения логов
│   ├── database/migrations/
│   ├── docker/entrypoint.sh      # migrate + key:generate при старте контейнера
│   ├── routes/
│   │   ├── api.php               # защищённые REST-ресурсы
│   │   ├── web.php               # register, login, logout, user
│   │   └── console.php           # расписание artisan
│   └── Dockerfile                # PHP-FPM для dev/prod-сборки API
├── frontend/                     # React SPA + react-admin (/cabinet)
├── docker/
│   └── nginx/default.conf        # прокси: / → Vite, /api → PHP-FPM
├── railway/                      # демо-деплой (всё в одном контейнере)
│   ├── Dockerfile
│   ├── nginx.conf
│   ├── supervisord.conf
│   └── start-web.sh
├── railway.toml                  # конфиг Railway (корень репозитория)
└── docker-compose.yml            # локальная разработка; эталон production-раскладки
```

## Модель данных

Сущности связаны так:

```
User
 ├── domains (name, last_status, check_settings_id?)
 ├── notification_settings (name, tg_chat_id, notify_on_*, ...)
 └── (через notification_settings)
      └── check_settings (name, method, request_timeout, check_interval, ...)
           └── domains.check_settings_id

Domain
 └── check_logs (checked_at, response_code, response_time, headers, body, redirects_count)
```

- **Уникальность домена** — пара `(user_id, name)`; один пользователь не может добавить один и тот же адрес дважды.
- **Check settings** не привязаны к `user_id` напрямую: доступ проверяется через `notification_settings.user_id`.
- **Статус в Redis** — ключ `domain:{id}:status`, TTL 24 ч; при отсутствии в Redis используется `domains.last_status` из БД.

## Как работает мониторинг

1. **Планировщик** (`DomainCheckScheduler`) запускается каждую минуту (`schedule:work` в `routes/console.php`).
2. По текущей минуте часа выбираются интервалы из `CheckInterval::matchingMinute()` (например, на 10-й минуте срабатывают интервалы 1, 2, 5, 10 минут).
3. Для каждого `CheckSetting` с подходящим интервалом в очередь **`domain-checks`** ставится `CheckDomainJob` на каждый связанный домен.
4. **Воркер `worker-domain-checks`** выполняет `DomainChecker::check()`:
   - HTTP `GET` или `HEAD` с таймаутом из профиля;
   - до 4 редиректов;
   - при ошибке соединения — запись в лог с `response_code = null`.
5. **Статус** — HTTP 2xx → `up`, иначе (включая отсутствие ответа) → `down`.
6. **Смена статуса** (`DomainStatusUpdater`): сравнение с предыдущим значением в Redis; при изменении обновляется `last_status` в БД и при необходимости ставится job уведомления.
7. **Уникальность задачи** — `ShouldBeUnique` на домен (`uniqueFor` 120 с); см. также [очередь `domain-checks`](#очередь-domain-checks-и-масштабирование).

Ручной запуск планировщика:

```bash
docker compose exec api php artisan domain-check:schedule
```

## Уведомления

- Канал: **Telegram** (`NotificationSetting.tg_chat_id`).
- Флаги: `notify_on_shutdown` (падение), `notify_on_recovery` (восстановление).
- Тексты (рус.): «Домен {name} недоступен» / «Домен {name} восстановлен».
- Отправка через очередь `default`; при 429 от Telegram — `release()` с задержкой из заголовка `retry-after` (`TelegramRateLimitParser`).
- Имя бота для UI подтягивается из `GET /constants` (`telegram_bot_name`).

Для работы уведомлений задайте в `api/.env`:

```env
TELEGRAM_BOT_API_TOKEN=...
TELEGRAM_BOT_NAME=...
```

## Веб-интерфейс

| Маршрут | Назначение |
|---------|------------|
| `/` | Вход (гости) |
| `/register` | Регистрация |
| `/cabinet/*` | react-admin: домены, настройки проверки, уведомления, логи |

Разделы кабинета:

- **Домены** — CRUD, индикатор статуса, переход к логам домена.
- **Настройки проверки** — метод, таймаут, интервал, привязка к уведомлениям.
- **Уведомления** — Telegram chat id и флаги событий.
- **Логи проверок** — список/детали по домену (`/cabinet/domains/:id/logs`).

Авторизация: **Laravel Sanctum SPA** — cookie-сессия, перед мутациями запрос `GET /api/sanctum/csrf-cookie`, заголовок `X-XSRF-TOKEN` из cookie `XSRF-TOKEN`.

## API

Публичные маршруты (`routes/web.php`, префикс `/api` на уровне nginx):

| Метод | Путь | Описание |
|-------|------|----------|
| POST | `/register` | Регистрация |
| POST | `/login` | Вход |
| POST | `/logout` | Выход (auth) |
| GET | `/user` | Текущий пользователь (auth) |

Защищённые (`auth:sanctum`, `routes/api.php`):

| Метод | Ресурс | Описание |
|-------|--------|----------|
| GET | `/me` | Пользователь (дубликат для SPA) |
| GET | `/constants` | enum-значения для форм + имя Telegram-бота |
| * | `/notification-settings` | CRUD уведомлений |
| * | `/check-settings` | CRUD профилей проверки |
| * | `/domains` | CRUD доменов |
| GET | `/check-logs` | Список логов (react-admin: `filter`, `sort`, `range`) |
| GET | `/check-logs/{id}` | Один лог |

Health check Laravel: `GET /up` (в production проксируется через nginx на PHP).

Списочные ответы отдают заголовок `X-Total-Count` для react-admin.

## Локальная разработка

### Требования

- Docker и Docker Compose
- (опционально) Node 22+ и PHP 8.4+ для работы без Docker

### Быстрый старт

```bash
cp api/.env.example api/.env
# при необходимости отредактируйте api/.env

docker compose up --build
```

После старта:

| URL | Сервис |
|-----|--------|
| http://localhost | Frontend (Vite через nginx) |
| http://localhost/api/... | API (nginx снимает префикс `/api` перед PHP-FPM) |

Контейнер `api` при первом запуске через `entrypoint.sh`: копирует `.env.example` при отсутствии `.env`, генерирует `APP_KEY`, выполняет `composer install` и `php artisan migrate`.

Сервисы Compose:

| Сервис | Роль |
|--------|------|
| `nginx` | Единая точка входа :80 |
| `api` | PHP-FPM |
| `frontend` | `npm ci` + `vite --host 0.0.0.0` |
| `db` | PostgreSQL |
| `redis` | Очереди и кэш статусов |
| `scheduler` | `php artisan schedule:work` (один экземпляр) |
| `worker` | `queue:work --queue=default` |
| `worker-domain-checks` | `queue:work --queue=domain-checks` — **масштабируемый** сервис проверок |

### Разработка без Docker
ненадо так делать =)

## Переменные окружения

Основные переменные в `api/.env` (шаблон — `api/.env.example`):

| Переменная | Назначение |
|------------|------------|
| `APP_URL`, `FRONTEND_URL` | Базовые URL приложения |
| `APP_KEY` | Ключ шифрования Laravel |
| `DB_*` | PostgreSQL (`DB_HOST=db` в Compose) |
| `REDIS_*` | Redis (`REDIS_HOST=redis` в Compose) |
| `SESSION_DOMAIN` | Домен cookie (локально `localhost`) |
| `SANCTUM_STATEFUL_DOMAINS` | Домены для SPA cookie auth |
| `CHECK_LOG_RETENTION_DAYS` | Срок хранения `check_logs` (по умолчанию 2) |
| `TELEGRAM_BOT_*` | Бот для уведомлений |

## Production-деплой

Ориентир — раскладка из [`docker-compose.yml`](docker-compose.yml): отдельные процессы под API, веб, БД, Redis, планировщик и **две очереди воркеров**.

```
nginx ──► frontend (статика после npm run build)
     └──► api (PHP-FPM)

scheduler          → 1 реплика, schedule:work
worker             → queue:work --queue=default
worker-domain-checks → queue:work --queue=domain-checks  ← масштабировать здесь
```

## Демо-деплой на Railway

Каталог [`railway/`](railway/) — **упрощённый демо-деплой**, а не целевая production-схема: один контейнер, `supervisord` поднимает nginx, PHP-FPM, оба воркера и планировщик. Удобно показать проект или потестировать на Railway, но **горизонтально масштабировать воркеры `domain-checks` в этой схеме нельзя**.

Конфигурация: [`railway.toml`](railway.toml) → [`railway/Dockerfile`](railway/Dockerfile).

1. Подключите репозиторий к Railway.
2. Добавьте **PostgreSQL** и **Redis**.
3. Задайте переменные (см. `.env.example`, блок для Railway).
4. При старте `start-web.sh` выполняет миграции; в `APP_ENV=production` — кэш config/route/view.

В контейнере supervisord запускает: `php-fpm`, `nginx` (`$PORT`, по умолчанию 8080), `queue:work` для `default` и `domain-checks`, `schedule:work`. Healthcheck: `GET /up`.

## Фоновые процессы и обслуживание

| Процесс / сервис Compose | Расписание / очередь | Назначение | Масштабирование |
|--------------------------|----------------------|------------|-----------------|
| `scheduler` | каждую минуту | постановка `CheckDomainJob` | только 1 экземпляр |
| `worker-domain-checks` | `domain-checks` | HTTP-проверки доменов | **первая точка горизонтального scale-out** |
| `worker` | `default` | Telegram-уведомления | по необходимости |
| `check-logs:prune` | ежедневно (в scheduler) | удаление старых `check_logs` | — |

Задачи планировщика (`domain-check-scheduler`, `check-logs:prune`) используют `withoutOverlapping()`, чтобы не дублировать запуск при задержках.

Локальное dev-окружение для Laravel

## Стек

- Laravel 13
- PHP 8.5 FPM
- Nginx
- MySQL 8.4
- Redis 8.6
- Redis Insight
- Docker Compose

## Первый запуск

1. Скопировать `.env.example` и переименовать в `.env`

2. Далее выполнить:
```shell
docker compose up --build -d
docker compose exec php composer install
docker compose exec php php artisan key:generate
docker compose exec php php artisan optimize:clear
docker compose exec php php artisan migrate

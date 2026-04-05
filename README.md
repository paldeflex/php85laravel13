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
```

3. Приложение будет доступно по адресу:
```text
http://localhost:8080
```

4. Redis Insight будет доступен по адресу:
```text
http://localhost:5540
```

5. Для подключения к Redis в Redis Insight использовать:
```text
Host: redis
Port: 6379
Username: пусто
Password: пусто
```

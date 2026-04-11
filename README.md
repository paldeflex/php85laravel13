# Локальное dev-окружение для Laravel

## Стек

- Laravel 13
- PHP 8.5 FPM
- Nginx 1.29
- MySQL 8.4
- Redis 8
- Node.js 24 (Vite + Tailwind CSS)

## Первый запуск

1. Установить одной командой:
    ```shell
    make install
    ```

2. Приложение доступно по адресу: http://localhost

3. Vite dev-server с HMR запускается автоматически вместе с контейнером `node`.
   Если нужно перезапустить:
    ```shell
    docker compose restart node
    ```

## Make-команды

| Команда | Описание |
|---------|----------|
| `make install` | Полная установка с нуля |
| `make up` | Запуск контейнеров |
| `make down` | Остановка контейнеров |
| `make rebuild` | Пересборка с нуля |
| `make restart s=php` | Перезапуск сервиса |
| `make logs s=php` | Логи сервиса |
| `make shell` | Shell в PHP-контейнер |
| `make shell-redis` | Redis CLI |
| `make test` | Запуск тестов |
| `make migrate` | Миграции |
| `make fresh` | Свежая БД + seed |
| `make seed` | Сидирование |
| `make cache-clear` | Очистка кэша |
| `make optimize` | Оптимизация |
| `make status` | Статус контейнеров |

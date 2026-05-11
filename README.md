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
| `make shell-node` | Shell в Node-контейнер |
| `make tinker` | Laravel Tinker |
| `make route-list` | Список маршрутов Laravel |
| `make queue-work` | Запуск Laravel queue worker |
| `make queue-restart` | Перезапуск Laravel queue workers |
| `make storage-link` | Создание storage symlink |
| `make composer-install` | Установка Composer-зависимостей |
| `make composer-update` | Обновление Composer-зависимостей |
| `make composer-dump` | Перегенерация Composer autoload |
| `make npm-install` | Установка npm-зависимостей |
| `make npm-dev` | Запуск Vite dev-server вручную |
| `make npm-build` | Production-сборка фронтенда |

## Xdebug

Xdebug установлен в режиме `trigger` — дебаг включается только по запросу.

### Настройка PhpStorm

1. **Settings → PHP → Debug** → Xdebug port: `9003`, галка `Can accept external connections`
2. **Settings → PHP → Servers** → `+`:
    - Name: любое
    - Host: `localhost`, Port: `80`, Debugger: `Xdebug`
    - ✓ Use path mappings
    - Корень проекта → `/var/www/html`
3. Включи прослушивание: **Run → Start Listening for PHP Debug Connections**

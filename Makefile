.PHONY: install up down rebuild restart logs shell shell-node shell-redis test migrate fresh seed cache-clear optimize status tinker route-list queue-work queue-restart storage-link composer-install composer-update composer-dump npm-install npm-dev npm-build npm-lint

# Полная установка с нуля
install:
	cp -n .env.example .env || true
	docker compose up --build -d
	docker compose exec php composer install
	docker compose exec php php artisan key:generate
	docker compose exec php php artisan optimize:clear
	docker compose exec php php artisan migrate
	@echo ""
	@echo "✓ Ready: http://localhost"

# Запуск / остановка
up:
	docker compose up -d

down:
	docker compose down

# Пересборка
rebuild:
	docker compose down
	docker compose up --build -d

# Перезапуск сервиса (usage: make restart s=php)
restart:
	docker compose restart $(s)

# Логи (usage: make logs s=php)
logs:
	docker compose logs -f $(s)

# Shell в контейнеры
shell:
	docker compose exec php sh

shell-node:
	docker compose exec node sh

shell-redis:
	docker compose exec redis redis-cli

# Тесты
test:
	docker compose exec php php artisan test

# Artisan
tinker:
	docker compose exec php php artisan tinker

route-list:
	docker compose exec php php artisan route:list

queue-work:
	docker compose exec php php artisan queue:work

queue-restart:
	docker compose exec php php artisan queue:restart

storage-link:
	docker compose exec php php artisan storage:link

# Миграции
migrate:
	docker compose exec php php artisan migrate

# Свежая БД (drop all + migrate + seed)
fresh:
	docker compose exec php php artisan migrate:fresh --seed

seed:
	docker compose exec php php artisan db:seed

# Кэш
cache-clear:
	docker compose exec php php artisan optimize:clear

optimize:
	docker compose exec php php artisan optimize

# Composer
composer-install:
	docker compose exec php composer install

composer-update:
	docker compose exec php composer update

composer-dump:
	docker compose exec php composer dump-autoload

# NPM
npm-install:
	docker compose exec node npm install

npm-dev:
	docker compose exec node npm run dev

npm-build:
	docker compose exec node npm run build

# Статус контейнеров
status:
	docker compose ps

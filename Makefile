COMPOSE := docker compose --env-file deploy/.env -f deploy/docker-compose.yml

.PHONY: up build start down restart logs ps shell migrate clear test lint mobile-install mobile-start

build:
	$(COMPOSE) build app

up: build
	$(COMPOSE) up -d --no-build

start:
	$(COMPOSE) up -d --no-build

down:
	$(COMPOSE) down

restart: down up

logs:
	$(COMPOSE) logs -f --tail=200

ps:
	$(COMPOSE) ps

shell:
	$(COMPOSE) exec app sh

migrate:
	$(COMPOSE) exec app php artisan migrate --force

clear:
	$(COMPOSE) exec app php artisan optimize:clear

test:
	$(COMPOSE) exec app php artisan test

lint:
	find backend/app backend/bootstrap backend/config backend/database backend/routes -name '*.php' -print0 | xargs -0 -n1 php -l

mobile-install:
	cd mobile && npm install && npx expo install --fix

mobile-start:
	cd mobile && npx expo start

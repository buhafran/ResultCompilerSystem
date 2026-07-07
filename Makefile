COMPOSE := docker compose --env-file deploy/.env -f deploy/docker-compose.yml

.PHONY: up build start down restart logs ps shell migrate clear

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


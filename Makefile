.PHONY: up down logs shell test lint mobile-install mobile-start
up:
	docker compose --env-file deploy/.env -f deploy/docker-compose.yml up -d --build
down:
	docker compose --env-file deploy/.env -f deploy/docker-compose.yml down
logs:
	docker compose --env-file deploy/.env -f deploy/docker-compose.yml logs -f --tail=150
shell:
	docker compose --env-file deploy/.env -f deploy/docker-compose.yml exec app sh
test:
	docker compose --env-file deploy/.env -f deploy/docker-compose.yml exec app php artisan test
lint:
	find backend/app backend/bootstrap backend/config backend/database backend/routes -name '*.php' -print0 | xargs -0 -n1 php -l
mobile-install:
	cd mobile && npm install && npx expo install --fix
mobile-start:
	cd mobile && npx expo start

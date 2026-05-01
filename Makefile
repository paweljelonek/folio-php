.PHONY: up down restart build install test cache-clear assets-link example shell logs

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

build:
	docker compose build --no-cache

install:
	docker compose exec app composer install

test:
	docker compose exec app php vendor/bin/phpunit --testdox

cache-clear:
	docker compose exec app php bin/console cache:clear

assets-link:
	docker compose exec app php bin/console assets:link

example:
	cp examples/.env .env && php bin/console assets:link && php -S localhost:8080 -t public/

shell:
	docker compose exec app sh

logs:
	docker compose logs -f

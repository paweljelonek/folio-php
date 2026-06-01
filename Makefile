.PHONY: help up down restart build install test phpstan cache-clear assets-link example example-docker shell logs

-include .env

.DEFAULT_GOAL := help

help:
	@echo "Usage:"
	@echo "  make \033[36m<target>\033[0m"
	@echo ""
	@echo "Targets:"
	@echo "  \033[36mhelp                \033[0m Show this help message"
	@echo "  \033[36mup                  \033[0m Start containers"
	@echo "  \033[36mdown                \033[0m Stop containers"
	@echo "  \033[36mrestart             \033[0m Restart containers"
	@echo "  \033[36mbuild               \033[0m Build containers without cache"
	@echo "  \033[36minstall             \033[0m Install composer dependencies"
	@echo "  \033[36mtest                \033[0m Run tests"
	@echo "  \033[36mphpstan             \033[0m Run static analysis"
	@echo "  \033[36mcache-clear         \033[0m Clear cache"
	@echo "  \033[36massets-link         \033[0m Link assets"
	@echo "  \033[36mexample             \033[0m local only — no Docker"
	@echo "  \033[36mexample-docker      \033[0m Run example using Docker"
	@echo "  \033[36mshell               \033[0m Open shell in the app container"
	@echo "  \033[36mlogs                \033[0m Show container logs"

.env:
	@echo "Creating .env from examples/.env.example"
	@cp examples/.env.example .env

up: .env
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

phpstan:
	docker compose exec app php vendor/bin/phpstan analyse --memory-limit=256M

cache-clear:
	docker compose exec app php bin/console cache:clear

assets-link:
	docker compose exec app php bin/console assets:link

example: .env
	php bin/console assets:link && php -S localhost:8080 -t public/

example-docker:
	@echo "Configuring example .env..."
	@cp examples/.env.example .env
	docker compose up -d
	docker compose exec app composer install
	docker compose exec app php bin/console assets:link

shell:
	docker compose exec app sh

logs:
	docker compose logs -f

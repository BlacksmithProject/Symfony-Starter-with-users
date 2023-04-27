# Misc
.DEFAULT_GOAL = help
.PHONY        : init help build up start down logs sh composer vendor sf cc migrations-generate migrations-execute test test-with-coverage cs stan

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————
init: start vendor cc migrations-execute  ## Build and start the containers, install vendors and run migrations

help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	docker compose build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	docker compose up --detach

start: build up ## Build and start the containers

down: ## Stop the docker hub
	docker compose down --remove-orphans

sh: ## Connect to the PHP FPM container
	docker compose exec php sh

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	docker compose exec php composer $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-progress --no-scripts --no-interaction
vendor: composer

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	docker compose exec php bin/console $(c)

cc: c=c:c ## Clear the cache
cc: sf

## —— Tools 🔧 —————————————————————————————————————————————————————————————————

migrations-generate:
	docker compose exec php bin/console doctrine:migrations:generate

migrations-execute:
	docker compose exec php bin/console doctrine:migrations:migrate --no-interaction

test: ## Launch PHPUnit
	docker compose exec php vendor/bin/phpunit

test-with-coverage: ## Launch PHPUnit and generate a coverage
	docker compose exec php vendor/bin/phpunit --coverage-html coverage --whitelist src/

cs:
	docker compose exec php vendor/bin/php-cs-fixer fix src/ tests/ --config=.php-cs-fixer.dist.php

stan:
	docker compose exec php vendor/bin/phpstan analyse src/ tests/ --level=max
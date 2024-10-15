COMPOSE=docker compose
CONTAINER_NAME=php
PHP_CONT=$(COMPOSE) exec $(CONTAINER_NAME)

PHP = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY = $(PHP) bin/console

build:
	$(COMPOSE) build

up:
	$(COMPOSE) up -d

down:
	$(COMPOSE) down

shell:
	$(PHP_CONT) bash

phpstan:
	$(PHP_CONT) vendor/bin/phpstan analyse src --level=9 --memory-limit=256M

csfixer:
	$(PHP_CONT) vendor/bin/php-cs-fixer fix

unit:
	$(PHP_CONT) vendor/bin/phpunit

sf:
	@$(eval c ?=)
	@$(SYMFONY) $(c)

composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)
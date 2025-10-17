PHP_VERSION ?= 8.3
VERSION ?= $$(git rev-parse --verify HEAD)
USER = $$(id -u)

# https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
.PHONY: help
.DEFAULT_GOAL := help

help: ## Display this help screen
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

composer: ## composer install
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		composer:latest \
		composer install --optimize-autoloader --ignore-platform-reqs

composer-up: ## composer update
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		composer:latest \
		composer update --no-cache --ignore-platform-reqs

composer-dump: ## composer dump-autoload
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		composer:latest \
		composer dump-autoload

composer-cli: ## composer console
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		composer:latest \
		sh

check:
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		composer:latest \
		composer check

psalm: ## psalm
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		./vendor/bin/psalm --php-version=${PHP_VERSION} --no-cache

phpstan: ## phpstan
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		./vendor/bin/phpstan analyse -c phpstan.neon

phpcs: ## php code snifferphp: detect violations of a defined coding standard
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		./vendor/bin/phpcs

phpcbf: ## php code sniffer: automatically correct
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		./vendor/bin/phpcbf

rector: ## rector
	docker run --init -it --rm -u ${USER} -v "$$(pwd):/app" -w /app \
		ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli \
		./vendor/bin/rector

## App

up: ## Run server
	USER=$(USER) docker compose -f ./docker-compose.yml --profile serve up -d --remove-orphans

stop: ## Stop server
	docker compose -f ./docker-compose.yml --profile serve stop

restart:
	docker compose -f ./docker-compose.yml --profile serve restart

down: stop
	docker compose -f ./docker-compose.yml down --remove-orphans

build:
	- USER=$(USER) docker compose -f ./docker-compose.yml build cli
	- USER=$(USER) docker compose -f ./docker-compose.yml build rabbitmq
	- USER=$(USER) docker compose -f ./docker-compose.yml build redis

remove: down _image_remove _container_remove _volume_remove

app:
	USER=$(USER) docker compose -f ./docker-compose.yml run --rm -u $(USER) -w /src cli sh

publisher:
	USER=$(USER) docker compose -f ./docker-compose.yml run --rm -u $(USER) -w /tests/simulation cli \
		php publisher.php --schema=high

worker:
	USER=$(USER) docker compose -f ./docker-compose.yml run --rm -u $(USER) -w /tests/simulation cli \
		php worker-with-exactlyonce.php --schema=high

bench: ## bench
	USER=$(USER) docker compose -f ./docker-compose.yml run --rm -u $(USER) -w / cli \
		./vendor/bin/phpbench run ./benchmark --report=aggregate --config=/benchmark/phpbench.json

_image_remove:
	docker image rm -f \
		queue_amqp-cli \
		queue_amqp-rabbitmq \
		queue_amqp-redis

_container_remove:
	docker rm -f \
		queue_amqp_rabbitmq \
		queue_amqp_redis

_volume_remove:
	docker volume rm -f \
		queue_amqp_vrabbitmq \
		queue_amqp_vredis

# HELP
# This will output the help for each task
.PHONY: help

help: ## This help.
    @awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

.DEFAULT_GOAL := help

THIS_FILE := $(lastword $(MAKEFILE_LIST))
PHP_VERSION ?= "7.4"

%:
	@echo ""
all:
	@echo ""
build:
	@if [ "$$(docker images -q php:$(PHP_VERSION)-cli-ext 2>/dev/null)" = "" ]; then \
		cd $$(pwd)/docker/php-cli-ext && docker build --build-arg PHP_VERSION=$(PHP_VERSION) -t php:$(PHP_VERSION)-cli-ext .; \
	fi
run:
	$(MAKE) build
	docker run --rm -it \
        -v $$(pwd):/srv/$$(basename "`pwd`") \
		-w /srv/$$(basename "`pwd`") \
		--user "$$(id -u):$$(id -g)" \
        --name $$(basename "`pwd`")_cli \
    php:$(PHP_VERSION)-cli-ext $(filter-out $@,$(MAKECMDGOALS))
unittest:
	$(MAKE) build
	docker run --rm -it \
        -v $$(pwd):/srv/$$(basename "`pwd`") \
		-w /srv/$$(basename "`pwd`") \
		--user "$$(id -u):$$(id -g)" \
        --name $$(basename "`pwd`")_cli \
    php:$(PHP_VERSION)-cli-ext vendor/bin/phpunit --verbose --debug tests
composer-install:
	docker run --rm -it \
        -v $$(pwd):/srv/$$(basename "`pwd`") \
        -w /srv/$$(basename "`pwd`") \
        -e COMPOSER_HOME="/srv/$$(basename "`pwd`")/.composer" \
        --user $$(id -u):$$(id -g) \
    composer composer install --no-plugins --no-scripts --no-dev --prefer-dist -v --ignore-platform-reqs
composer-update:
	docker run --rm -it \
        -v $$(pwd):/srv/$$(basename "`pwd`") \
        -w /srv/$$(basename "`pwd`") \
        -e COMPOSER_HOME="/srv/$$(basename "`pwd`")/.composer" \
        --user $$(id -u):$$(id -g) \
    composer composer update -v --no-dev --ignore-platform-reqs
composer:
	docker run --rm -it \
        -v $$(pwd):/srv/$$(basename "`pwd`") \
        -w /srv/$$(basename "`pwd`") \
        -e COMPOSER_HOME="/srv/$$(basename "`pwd`")/.composer" \
        --user $$(id -u):$$(id -g) \
    composer composer $(filter-out $@,$(MAKECMDGOALS))

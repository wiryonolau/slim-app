# HELP
# This will output the help for each task
.PHONY: help

help: ## This help.
    @awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

.DEFAULT_GOAL := help

THIS_FILE := $(lastword $(MAKEFILE_LIST))

%:
	@echo ""
all:
	@echo ""
run:
	docker run --rm -it \
        -v $$(pwd):/srv/$$(basename "`pwd`") \
		-w /srv/$$(basename "`pwd`") \
		--user "$$(id -u):$$(id -g)" \
        --name $$(basename "`pwd`")_cli \
    php:7.3-cli $(filter-out $@,$(MAKECMDGOALS))
unittest:
	docker run --rm -it \
        -v $$(pwd):/srv/$$(basename "`pwd`") \
		-w /srv/$$(basename "`pwd`") \
		--user "$$(id -u):$$(id -g)" \
        --name $$(basename "`pwd`")_cli \
    php:7.3-cli vendor/bin/phpunit --verbose --debug tests
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

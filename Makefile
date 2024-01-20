SHELL = /bin/bash
.DEFAULT_GOAL := all
HERE := $(dir $(realpath $(firstword $(MAKEFILE_LIST))))

# https://mwop.net/blog/2023-12-11-advent-makefile.html
##@ help
help:  ## Display this help
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[0-9a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

.PHONY: all
all: analyze lint tests ## (default) analyze & tests

# ### #

.PHONY: analyze
analyze: ## Static analysis with PHPStan
	@echo
	@echo "--> Analyze: PHPStan"
	@echo
	vendor/bin/phpstan
	@echo

.PHONY: lint
lint: ## Lint check
	@echo
	@echo "--> Lint"
	@echo
	php -l server.php
	php -l lib/protocols/memcached-text.php
	php -l lib/storage/sqlite.php
	@echo

.PHONY: tests
tests: server-start ## Run Pest tests
	@echo
	@echo "--> Tests: Pest"
	@echo
	bash -c "./vendor/bin/pest || php server.php stop"
	@echo
	@echo "--> Server: stop"
	@echo
	php server.php stop
	@echo

# ### #

.PHONY: server-start
server-start:
	@echo
	@echo "--> Server: start"
	@echo
	php server.php start -d
	@echo

.PHONY: server-stop
server-stop:
	@echo
	@echo "--> Server: stop"
	@echo
	php server.php stop
	@echo

SHELL = /bin/bash
.DEFAULT_GOAL := all
HERE := $(dir $(realpath $(firstword $(MAKEFILE_LIST))))

# https://mwop.net/blog/2023-12-11-advent-makefile.html
##@ help
help:  ## Display this help
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[0-9a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-40s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

.PHONY: all
all: tests

# ### #

.PHONY: tests ## Pest tests
tests:
	@echo
	@echo "--> Tests: Pest"
	@echo
	bash -c "./vendor/bin/pest"

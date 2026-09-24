.DEFAULT_GOAL := help

.PHONY: help install assets watch

help: ## Display a list of all available commands
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

install: ## Install composer and pnpm dependencies
	ddev composer install
	ddev pnpm install

assets: ## Build all frontend assets (styles + react) and compile them
	ddev pnpm run build
	ddev console asset-map:compile

assets-watch: ## Watch and rebuild all frontend assets (styles + react)
	ddev pnpm run watch

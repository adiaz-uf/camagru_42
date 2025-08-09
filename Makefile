# Variables
DOCKER_COMPOSE = docker compose
PROJECT_NAME = transcendence

# Basic commands
.PHONY: help up down build restart logs clean dbshell shell init fclean re

.SILENT:

help:  ## Display the list of available commands
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}'

up:  ## Start the Docker containers
	$(DOCKER_COMPOSE) up -d

down:  ## Stop and remove the Docker containers
	$(DOCKER_COMPOSE) down --timeout 2

build:  ## Build the Docker containers
	$(DOCKER_COMPOSE) build

restart:  ## Restart the Docker services
	$(DOCKER_COMPOSE) restart

logs:  ## Show real-time logs from containers
	$(DOCKER_COMPOSE) logs -f

clean:  ## Remove all Docker containers and volumes related to the project
	$(DOCKER_COMPOSE) down -v --timeout 2

init: build up  ## Initialize the project

fclean:  ## Completely clean the project (remove containers and volumes)
	$(DOCKER_COMPOSE) down -v
	@rm -r backend/uploads
	@docker rmi $$(docker images -qa)

re:  ## Rebuild project
	$(MAKE) down
	$(MAKE) build
	$(MAKE) up
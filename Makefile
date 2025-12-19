.PHONY: all clean docker-pull down help reset stop test up

help: ## Show this help message.
	@grep -E -h "^\w.*:.*#" $(MAKEFILE_LIST) | sed -e 's/\(.*\):.*# *\(.*\)/\1|\2/' | column -s '|' -t

all: reset up ## Reset the environment and start all services.

reset: down docker-pull up ## Reset the environment and pull latest images.

up: ## Start all services without changing container state.
	docker compose up --wait --remove-orphans

stop: ## Stop all services.
	docker compose stop

down: 
	docker compose down --volumes --remove-orphans

clean: down ## Down the environment and remove installed dependencies.
	rm -rf .last-pull-*

# Only pull images once a day.
LAST_PULL_FILE := .last-pull-$(shell date +%Y%m%d)
docker-pull: $(LAST_PULL_FILE)
$(LAST_PULL_FILE):
	rm -f .last-pull-*
	docker compose pull
	touch $(LAST_PULL_FILE)

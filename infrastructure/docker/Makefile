.DEFAULT_GOAL := help
.PHONY = help guard-% build-services push-services build-release reset logs
include ../infrastructure.env
export

help: ## Display a list of the public targets
# Find lines that starts with a word-character, contains a colon and then a
# doublehash (underscores are not word-characters, so this excludes private
# targets), then strip the hash and print.
	@grep -E -h "^\w.*:.*##" $(MAKEFILE_LIST) | sed -e 's/\(.*\):.*##\(.*\)/\1	\2/'

guard-%:
	@ if [ "${${*}}" = "" ]; then \
	  echo "Environment variable $* not set"; \
	  exit 1; \
	fi

build-service-images: ## Builds nginx and php-fpm images
	docker build \
	  -t "$(DOCKER_REPO)/nginx:$(NGINX_BUILD_TAG)" \
	  -t "$(DOCKER_REPO)/nginx:latest" \
	  -f nginx/Dockerfile \
	  nginx

	docker build \
		-t "$(DOCKER_REPO)/php-fpm:$(PHPFPM_BUILD_TAG)" \
		-t "$(DOCKER_REPO)/php-fpm:latest" \
		-f php-fpm/Dockerfile \
		php-fpm

push-service-images: ## Pushes all service-images
	docker push $(DOCKER_REPO)/nginx:$(NGINX_BUILD_TAG)
	docker push $(DOCKER_REPO)/php-fpm:$(PHPFPM_BUILD_TAG)

build-release: guard-REVISION ## Builds a release-image for the specified source REVISION
	docker build \
	  -t "$(DOCKER_REPO)/material-list-release:$(REVISION)" \
	  -t "$(DOCKER_REPO)/material-list-release:latest" \
	  --no-cache \
	-f release/Dockerfile \
	--build-arg BUILDER_IMAGE="$(DOCKER_REPO)/php-fpm:$(PHPFPM_BUILD_TAG)" \
	../../ 

push-release: guard-REVISION ## Builds a release-image for the specified source REVISION
	docker push "$(DOCKER_REPO)/material-list-release:$(REVISION)"

RELEASE?=latest
reset-release: _compile-reset-release _reset-docker-compose # Bring up a docker-compose project with the specified RELEASE (defaults to latest)

reset-dev: _compile-reset-dev _reset-docker-compose # Bring up a docker-compose project with the specified RELEASE (defaults to latest)

logs: ## Follow docker logs from the containers
	docker-compose logs -f --tail=50

_compile-reset-dev:
	echo "Resetting for development use"
	docker-compose -f docker-compose.common.yml -f docker-compose-dev.yml config > docker-compose.yml

_compile-reset-release:
	echo "Resetting for release ${RELEASE}"
	docker-compose -f docker-compose.common.yml config > docker-compose.yml

_reset-docker-compose:
# nginx-proxy/dory takes a second or two to disconnect from the container-
# network, so it can take two attemps to bring the project fully down.
	docker-compose down -v --remove-orphans || true
	docker-compose down -v --remove-orphans
	docker-compose up -d
	echo "Available via http://${VIRTUAL_HOST}"


.DEFAULT_GOAL := deploy-dry-run
deployer_docker_tag := "material-list-deployer"
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

build: ## Build the deployer
	docker build -t $(deployer_docker_tag) .

# Besides ENVIRONMENT you also need a GCLOUD_AUTH var that should
# contain the base64 encoded version of a service-accounts access-key.
deploy-dry-run: guard-ENVIRONMENT build ## Go through the motions of a deploy, but only print manifests.
	docker run \
	  --rm -ti \
	  --env-file=secrets.env \
	  -e DRY_RUN="true" \
	  -e GCLOUD_AUTH \
	  -e RELEASE_TAG="non-existing-release-sha" \
	  -v $(shell pwd)/../../../:/github/workspace \
	  -w /github/workspace \
	  $(deployer_docker_tag) \
	  ${ENVIRONMENT}

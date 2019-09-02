#!/usr/bin/env bash
# 
# Deploy a specific release to a specific environment.
# 
# Executed with the root of the repo as working directory.
# The deployer pulls in a number of environment-variables, and then
# - Authenticates against the kubernetes cluster using the gcloud cli
# - Prepares manifests for deployment by replacing secret-values into a Secret
# - Kustomizes the environment manifests and steams them to kubectl
#
set -euo pipefail
IFS=$'\n\t'

if [[ $# -lt 1 ]] ; then
    echo "Syntax: $0 <enviroment>"
    exit 1
fi

if [[ -z "${GCLOUD_AUTH:-}" ]] ; then
    echo "Missing GCLOUD_AUTH environment variable"
    exit 1
fi

if [[ -z "${GITHUB_SHA:-}" ]] ; then
    echo "Missing GITHUB_SHA environment variable"
    exit 1
fi

RELEASE_TAG=${GITHUB_SHA}
ENVIRONMENT=$1

INFRASTRUCTURE_DOT_ENV="infrastructure/infrastructure.env"
ENVIRONMENT_PATH="infrastructure/k8s/overlays/${ENVIRONMENT}"
# We expect to be run with the /infrastructure folder as current directory.
if [[ ! -d "${ENVIRONMENT_PATH}" ]] ; then
    echo "Could not find environment kustomization overlay at ${ENVIRONMENT_PATH}"
    exit 1
fi

if [[ ! -f "${INFRASTRUCTURE_DOT_ENV}" ]] ; then
    echo "Could not find Infrastructure .env at ${INFRASTRUCTURE_DOT_ENV}"
    exit 1
fi

set -a
source "${INFRASTRUCTURE_DOT_ENV}"
set +a

SUBTITUTIONS=("${ENVIRONMENT^^}_APP_KEY" "${ENVIRONMENT^^}_DB_PASSWORD" DOCKER_REPO PHPFPM_BUILD_TAG NGINX_BUILD_TAG)
for var in "${SUBTITUTIONS[@]}"
do
    if [[ -z $(eval "echo \${$var:-}") ]] ; then
        echo "Environment-variable ${var} should have been defined"
        exit
    fi
done

# Move secret-name from env-specific to general for easier templating.
APP_KEY=$(eval echo "\$${ENVIRONMENT^^}_APP_KEY")
DB_PASSWORD=$(eval echo "\$${ENVIRONMENT^^}_DB_PASSWORD")
export APP_KEY
export DB_PASSWORD

# Generate .env, it's going into a secret, so we can't envsubst directly on the manifests
if [[ ! -f "${ENVIRONMENT_PATH}/.env.template" ]] ; then
    echo "Could not find .env template at $(pwd)/${ENVIRONMENT_PATH}/.env.template"
    exit 1
fi

envsubst < "${ENVIRONMENT_PATH}/.env.template" > "${ENVIRONMENT_PATH}/.env"

echo "* Authenticating"
echo "${GCLOUD_AUTH}" | base64 -d | gcloud auth activate-service-account --key-file=-
gcloud container clusters get-credentials primary-cluster --region=europe-west1 --project=reload-material-list-3

# Verify cluster connection
echo "* Verifying cluster connection"
NAMESPACE=$ENVIRONMENT
if ! kubectl get namespace ${NAMESPACE} ; then
    echo "Could not find namespace ${NAMESPACE} in the cluster"
    exit 1
fi;

# Kustomize
echo "** Deploying Release ${RELEASE_TAG} to ${ENVIRONMENT}"

export RELEASE_TAG

if [[ ! -z "${DRY_RUN:-}" ]] ; then
    echo "** Dry run **"
    kubectl kustomize ${ENVIRONMENT_PATH} | envsubst
else
    kubectl kustomize ${ENVIRONMENT_PATH} | envsubst | kubectl apply -f -
fi

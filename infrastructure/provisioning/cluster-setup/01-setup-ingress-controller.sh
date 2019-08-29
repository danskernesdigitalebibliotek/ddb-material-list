#!/usr/bin/env bash

set -exuo pipefail
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "${SCRIPT_DIR}"

if [[ $# -lt 1 ]] ; then
    echo "Syntax: $0 <ingress ip>"
    exit 1
fi

INGRESS_IP=$1

echo " > Project ID: ${GCP_PROJECT_ID} ..."

echo " > Installing Nginx Ingress Controller in cluster via Helm ..."
# controller.publishService.enabled=true will set the endpoint records on the
# ingress objects to those on the GCLB.
helm upgrade \
    --install \
    --set rbac.create=true \
    --set controller.kind=DaemonSet \
    --set controller.service.loadBalancerIP="${INGRESS_IP}" \
    --set controller.service.externalTrafficPolicy="Local" \
    --set controller.publishService.enabled=true \
    nginx-ingress \
    stable/nginx-ingress

# Apply custom config to the controller.
kubectl -n default apply -f ./nginx-ingress/configmap.yaml

echo " > Done."


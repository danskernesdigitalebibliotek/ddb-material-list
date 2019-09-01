#!/usr/bin/env bash
set -euxo pipefail
 
# See https://docs.cert-manager.io/en/latest/getting-started/install/kubernetes.html#installing-with-helm
# Install the CustomResourceDefinition resources separately
kubectl apply -f https://raw.githubusercontent.com/jetstack/cert-manager/release-0.9/deploy/manifests/00-crds.yaml

# Create the namespace for cert-manager
kubectl create namespace cert-manager

# Label the cert-manager namespace to disable resource validation
kubectl label namespace cert-manager certmanager.k8s.io/disable-validation=true

# Add the Jetstack Helm repository
helm repo add jetstack https://charts.jetstack.io

# Update your local Helm chart repository cache
helm repo update

# Install the cert-manager Helm chart
helm install \
  --name cert-manager \
  --namespace cert-manager \
  --version v0.9.1 \
  jetstack/cert-manager

# Wait for cert-manage pods to come online before proceeding.
echo " > Waiting for clusterissuers resource to be available in cluster ..."
sleep 20

# Install the ClusterIssuer for Let's Encrypt (prod).
kubectl apply -f ./certmanager/clusterissuer.yaml

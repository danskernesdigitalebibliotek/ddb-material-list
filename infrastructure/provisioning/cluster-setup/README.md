# Kubernetes final configuration
The cluster setup assumes you've either installed helm in the cluster, or are using Tillerless (https://github.com/rimusz/helm-tiller). 

Also you're local kubectl must be configured with a valid context for the project.

For google cloud this means eg.
```shell
# Reference terraform.tfvars and variables.tf for the project and region.
gcloud --project <my project> --region <my region> container clusters get-credentials primary-cluster 
```

## Provisioning
```shell

# Provision an external IP for the kubernetes cluster, then setup the ingress controller
./01-setup-ingress-controller.sh <external ip>

# Then add certmanager to the cluster for certificate provisioning.
# Make sure to update certmanager/clusterissuer.yaml with your client email
./02-install-cert-manager.sh

```

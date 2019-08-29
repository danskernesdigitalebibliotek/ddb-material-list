# Material-list hosting infrastructure
Sample google cloud-based hosting setup.


## Requirements
Material-list requires a host capable of hosting a php7 project, and a backend mysql 5.7+ compatible database.

The rest of this document describes how to setup a complete hosting-environment based on Google Cloud Platform.

Be aware this configuration is "live", ie. if you want to do your own hosting in a fork, you will need to change configuration of eg. the enabled environments and the terraform state storage.

## Provisioning via Google Cloud Platform.
The Google cloud project is provisioned via Terraform.

You provision a project by applying the terraform configuration in the provisioning/terraform directory. You will need to modify backend.tf to point at a remote backend-store of your choosing, and terraform.tfvars to suit your project.

After the project is created, use provisioning/cluster-setup to perform the last configuration of the Kubernetes cluster prior to doing any deployments.

## Creating environments
TODO

## Building and deploying
TODO


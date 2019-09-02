# Material-list hosting infrastructure
Sample google cloud-based hosting setup.


## Requirements
Material-list requires a host capable of hosting a php7 project, and a backend mysql 5.7+ compatible database.

The rest of this document describes how to setup a complete hosting-environment based on Google Cloud Platform.

Be aware this configuration is "live", ie. if you want to do your own hosting in a fork, you will need to change configuration of eg. the enabled environments and the terraform state storage.

## Provisioning via Google Cloud Platform.
The Google cloud project is provisioned via Terraform.

You provision a project by applying the terraform configuration in the provisioning/terraform directory. You will need to modify backend.tf to point at a remote backend-store of your choosing, and terraform.tfvars to suit your project.

After the project is created, use provisioning/cluster-setup to perform the last configuration of the Kubernetes cluster prior to doing any deployments. Also, see cluster-setup/README.md for deltails.

## Material-list hosting docker images
The solution consists of 3 docker-images
* nginx: services all static requests for assets, and passes all php-requests on to php-fpm.
* php-fpm: serves php-requests via a number of worker-processes
* release: contains a build of the project, on boot-up a copy of the release-code is passed on to nginx and php-fpm, the release-container then exists.

All docker-related files can be found in infrastructure/docker.

### Building services-images
Service-images (nginx, php-fpm) are build manually (ie. not via continuous integration). You build the service-images by first bumping the relevant service-image tags in infrastructure/.env, and then running `make build-service-images` in the docker directory.

You can then test your modifications via `make reset-release`, and when everything is as you want it, publish the images via `make publish-service-images`.

### Building release-images
Release-images are build automatically during CI.
TODO

## Creating environments
TODO

## Building and deploying
TODO


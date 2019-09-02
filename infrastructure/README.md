# Material-list hosting infrastructure
This directory contains configuration, scripts and tools for hosting Material List. The current example is based on cloud hosting, but as it is based on  standard Open Source components it can be adapted to any cloud or traditional hosting.

## Requirements
Material-list requires a host with the following software:
- Nginx 1.17
- PHP 7.2
- Mysql 5.7+ compatible database
Individual components can be swapped for relevant alternatives. Apache can be used instead of Nginx. Any [database supported by Laravel](https://laravel.com/docs/5.8/database) such as PostgresSQL, SQLite and SQL Server can replace MariaDB.

The rest of this document describes how to setup a complete hosting-environment via Google Kubernetes Engine (GKE) on Google Cloud Platform (GCP).

Be aware this configuration is "live", ie. if you want to do your own hosting in a fork, you will need to change configuration of eg. the enabled environments and the terraform state storage.

## Provisioning via Google Cloud Platform.
The Google Cloud project is provisioned via [Terraform](https://www.terraform.io/).

You provision a project by applying the terraform configuration in the provisioning/terraform directory. You will need to modify backend.tf to point at a remote backend-store of your choosing, and terraform.tfvars to suit your project.

After the project is created, use provisioning/cluster-setup to perform the last configuration of the Kubernetes cluster prior to doing any deployments. Also, see cluster-setup/README.md for deltails.

## Material-list hosting docker images
The solution consists of 3 docker-images
* nginx: services all static requests for assets, and passes all php-requests on to php-fpm.
* php-fpm: serves php-requests via a number of worker-processes
* release: contains a build of the project, on boot-up a copy of the release-code is passed on to nginx and php-fpm, the release-container then exists.

All docker-related files can be found in infrastructure/docker.

### Building services-images
Service-images (nginx, php-fpm) are build manually (ie. not via continuous integration). This ensures a typical deployment only update the release-imagge and thus reduces the amount of thing that can break. You build the service-images by first bumping the relevant service-image tags in infrastructure/.env, and then running `make build-service-images` in the docker directory.

You can then test your modifications via `make reset-release`, and when everything is as you want it, publish the images via `make publish-service-images`.

## Building and deploying
Release-images are build automatically during CI, deployments to production happens automatically for each merge to master. See the `deployer` github action included in this project under `.github/actions`.


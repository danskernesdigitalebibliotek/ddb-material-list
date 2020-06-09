#!/bin/sh

APP_VERSION=develop
VERSION=latest

#docker build --no-cache --build-arg APP_VERSION=${APP_VERSION} --tag=danskernesdigitalebibliotek/material-list:${VERSION} --file="material-list/Dockerfile" material-list
#docker build --no-cache --build-arg VERSION=${VERSION} --tag=danskernesdigitalebibliotek/material-list-nginx:${VERSION} --file="nginx/Dockerfile" nginx

docker push danskernesdigitalebibliotek/material-list:${VERSION}
docker push danskernesdigitalebibliotek/material-list-nginx:${VERSION}

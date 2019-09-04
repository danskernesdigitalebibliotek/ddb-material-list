ARG CLOUD_SDK_TAG=257.0.0-slim
# We know that we're going to be based on gcp, so might as well start off
# with their sdk. The sdk image is based on debian, see
# https://github.com/GoogleCloudPlatform/cloud-sdk-docker/blob/master/Dockerfile
FROM google/cloud-sdk:$CLOUD_SDK_TAG

RUN apt-get update -qqy && apt-get install -qqy \
  gettext-base \
  kubectl

RUN gcloud config set core/disable_usage_reporting true && \
    gcloud config set component_manager/disable_update_check true
    
COPY deploy.sh /usr/local/bin/deploy
RUN chmod +x /usr/local/bin/deploy

ENTRYPOINT [ "deploy" ]

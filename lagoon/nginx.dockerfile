ARG CLI_IMAGE
FROM ${CLI_IMAGE} AS cli

FROM uselagoon/nginx:latest

RUN mkdir -p /etc/nginx/conf.d/app

COPY lagoon/nginx/app /etc/nginx/conf.d/app/
COPY lagoon/nginx/app.conf /etc/nginx/conf.d/app.conf

RUN fix-permissions /etc/nginx

COPY --from=cli /app /app

ENV WEBROOT=public

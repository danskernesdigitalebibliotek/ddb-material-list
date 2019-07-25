FROM php:7.2-alpine

RUN apk add --update --no-cache --virtual .build-deps \
        nodejs npm

RUN npm install dredd --global --unsafe-perm=true

ENTRYPOINT ["dredd"]

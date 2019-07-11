FROM alpine:3.10

# codecov uses git and GNU find.
RUN apk add --no-cache bash curl git findutils

COPY entrypoint.sh /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD [""]

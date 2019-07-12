#!/bin/bash

export APP_ENV=testing
export APP_DEBUG=true
export APP_TOKENCHECKER=test
export DB_CONNECTION=sqlite
export DB_DATABASE=/tmp/material-list-test.sqlite

# PHP doesn't exit when the script ends, so we'll kill it explicitly.
function cleanup {
        local pids=$(jobs -pr)
        [ -n "$pids" ] && kill $pids
        rm -f /tmp/assessor-dredd.sqlite
}

trap cleanup INT TERM ERR
trap cleanup EXIT

./artisan migrate:fresh

php -S 0.0.0.0:8080 -t public

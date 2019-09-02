workflow "Run tests" {
  on = "push"
  resolves = ["Behaviour Codecov", "Specification tests", "Unit Codecov", "Check codestyle", "Static code analysis", "Lint specification", "Build", "Deploy to Test"]
}

action "Composer install" {
  uses = "MilesChou/composer-action@master"
  args = "install"
}

action "Behaviour tests" {
  needs = ["Composer install"]
  uses = "docker://php:7.2-alpine"
  runs = "phpdbg -qrr vendor/bin/behat --strict"
}

action "Behaviour test coverage" {
  needs = ["Behaviour tests"]
  uses = "docker://php:7.2-alpine"
  runs = "phpdbg -qrr vendor/bin/phpcov merge --clover=coverage/behat.xml coverage/default.cov"
}

action "Behaviour Codecov" {
  needs = ["Behaviour test coverage"]
  uses = "./.github/actions/codecov"
  args = "-F Behaviour -f coverage/behat.xml"
  secrets = ["CODECOV_TOKEN"]
}

action "Specification tests" {
  needs = ["Composer install"]
  uses = "./.github/actions/spec-test"
  runs = "dredd --loglevel=error"
  env = {
    # Ensure that we get as much information as possible if tests fail.
    APP_DEBUG = "true"
    # In non-production environments we can recreate the database before testing
    APP_ENV="testing"
    # Do not contact the OAuth endpoint during testing.
    ADGANGSPLATFORMEN_DRIVER="testing"
    # Use SQLite for testing. Use a file in a directory we know we can write to
    DB_CONNECTION = "sqlite"
    DB_DATABASE = "/tmp/db.sqlite"
  }
}

action "Unit tests" {
  needs = ["Composer install"]
  uses = "docker://php:7.2-alpine"
  runs = "phpdbg -qrr ./vendor/bin/phpunit --coverage-clover=coverage/unit.xml"
}

action "Unit Codecov" {
  needs = ["Unit tests"]
  uses = "./.github/actions/codecov"
  args = "-F Unit -f coverage/unit.xml"
  secrets = ["CODECOV_TOKEN"]
}

action "Check codestyle" {
  needs = ["Composer install"]
  uses = "docker://php:7.2-alpine"
  runs = "vendor/bin/phpcs"
}

action "Static code analysis" {
  needs = ["Composer install"]
  uses = "docker://php:7.2-alpine"
  runs = "vendor/bin/phpstan analyse ."
}

action "Lint specification" {
  uses = "docker://wework/speccy"
  args = "lint material-list.yaml"
}

# TODO - use the build we've already done in "Composer install"
action "Build" {
  uses = "actions/docker/cli@master"
  args = "build --build-arg=BUILDER_IMAGE=\"eu.gcr.io/reload-material-list-3/php-fpm:0.2.0\" -t \"eu.gcr.io/reload-material-list-3/material-list-release:${GITHUB_SHA}\" -f infrastructure/docker/release/Dockerfile  ."
  needs = ["Specification tests"]
}

action "Test env filter" {
  needs = "Build"
  uses = "actions/bin/filter@master"
  args = "branch feature/hosting"
}

action "Setup Google Cloud" {
  uses = "actions/gcloud/auth@master"
  secrets = ["GCLOUD_AUTH"]
}

action "Set Credential Helper for Docker" {
  needs = ["Setup Google Cloud"]
  uses = "actions/gcloud/cli@master"
  args = ["auth", "configure-docker", "--quiet"]
}

action "Push image to GCR" {
  needs = ["Setup Google Cloud", "Set Credential Helper for Docker", "Test env filter"]
  uses = "actions/gcloud/cli@master"
  runs = "sh -c"
  args = ["docker push eu.gcr.io/reload-material-list-3/material-list-release:${GITHUB_SHA}"]
}

action "Deploy to Test" {
  needs = ["Push image to GCR"]
  uses = "./.github/actions/deployer"
  secrets = ["TEST_DB_PASSWORD", "TEST_APP_KEY", "GCLOUD_AUTH"]
  args = "test"
}

workflow "Run tests" {
  on = "push"
  resolves = ["Behaviour Codecov", "Specification tests", "Unit Codecov", "Check codestyle", "Static code analysis"]
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
  runs = "phpdbg -qrr vendor/bin/phpcov merge --clover=behat.xml coverage/default.cov"
}

action "Behaviour Codecov" {
  needs = ["Behaviour test coverage"]
  uses = "./.github/actions/codecov"
  args = "-F Behaviour -f behat.xml"
  secrets = ["CODECOV_TOKEN"]
}

action "Specification tests" {
  needs = ["Composer install"]
  uses = "./.github/actions/spec-test"
  runs = "dredd"
  env = {
    # Disable the default OAuth token check
    APP_TOKENCHECKER = "test"
    # Ensure that we get as much information as possible if tests fail.
    APP_DEBUG = "true"
    # In non-production environments we can recreate the database before testing
    APP_ENV="testing"
    # Use SQLite for testing. Use a file in a directory we know we can write to
    DB_CONNECTION = "sqlite"
    DB_DATABASE = "/tmp/db.sqlite"
  }
}

action "Unit tests" {
  needs = ["Composer install"]
  uses = "docker://php:7.2-alpine"
  runs = "phpdbg -qrr ./vendor/bin/phpunit --coverage-php=coverage/unit.cov"
}

action "Unit test coverage" {
  needs = ["Unit tests"]
  uses = "docker://php:7.2-alpine"
  runs = "phpdbg -qrr vendor/bin/phpcov merge --clover=unit.xml coverage/unit.cov"
}

action "Unit Codecov" {
  needs = ["Unit test coverage"]
  uses = "./.github/actions/codecov"
  args = "-F Unit -f unit.xml"
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

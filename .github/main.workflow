workflow "Run tests" {
  on = "push"
  resolves = ["Behaviour Codecov"]
}

action "Composer install" {
  uses = "MilesChou/composer-action@master"
  args = "install"
}

action "Behaviour tests" {
  needs = ["Composer install"]
  uses = "./.github/actions/php-action"
  runs = "vendor/bin/behat"
}

action "Behaviour test coverage" {
  needs = ["Behaviour tests"]
  uses = "./.github/actions/php-action"
  runs = "vendor/bin/phpcov merge --clover=behat.xml coverage/default.cov"
}

action "Behaviour Codecov" {
  needs = ["Behaviour test coverage"]
  uses = "./.github/actions/codecov"
  args = "-F Behaviour -f behat.xml"
  secrets = ["CODECOV_TOKEN"]
}

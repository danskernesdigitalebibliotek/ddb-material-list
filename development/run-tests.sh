#!/bin/sh

./vendor/bin/phpunit --testdox
./vendor/bin/behat
dredd

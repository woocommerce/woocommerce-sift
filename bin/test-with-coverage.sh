#!/usr/bin/env bash

# Run tests with coverage
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-cobertura=coverage.xml

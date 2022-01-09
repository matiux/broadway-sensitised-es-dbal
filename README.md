Sensitization support for [broadway/broadway](https://github.com/broadway/broadway)
===

![check dependencies](https://github.com/matiux/broadway-sensitive-serializer/actions/workflows/check-dependencies.yml/badge.svg)
![test](https://github.com/matiux/broadway-sensitive-serializer/actions/workflows/tests.yml/badge.svg)
[![codecov](https://codecov.io/gh/matiux/broadway-sensitive-serializer/branch/master/graph/badge.svg)](https://codecov.io/gh/matiux/broadway-sensitive-serializer)
[![type coverage](https://shepherd.dev/github/matiux/broadway-sensitive-serializer/coverage.svg)](https://shepherd.dev/github/matiux/broadway-sensitive-serializer)
[![psalm level](https://shepherd.dev/github/matiux/broadway-sensitive-serializer/level.svg)](https://shepherd.dev/github/matiux/broadway-sensitive-serializer)
![security analysis status](https://github.com/matiux/broadway-sensitive-serializer/actions/workflows/security-analysis.yml/badge.svg)
![coding standards status](https://github.com/matiux/broadway-sensitive-serializer/actions/workflows/coding-standards.yml/badge.svg)

The idea behind this project is to make a CQRS + ES system compliant, specifically implemented through
the [Broadway](https://github.com/broadway/broadway) library, with the General Data Protection Regulation (GDPR),
in particular with the right to be forgotten.

Read the [wiki](https://github.com/matiux/broadway-sensitive-serializer/wiki) for more information.

### Setup for development

```shell
git clone https://github.com/matiux/broadway-sensitive-serializer.git && cd broadway-sensitive-serializer
cp docker/docker-compose.override.dist.yml docker/docker-compose.override.yml
rm -rf .git/hooks && ln -s ../scripts/git-hooks .git/hooks
```

### Install dependencies to run test or execute examples
```shell
./dc up -d
./dc enter
composer install
```

### Run test
```shell
./dc up -d
./dc enter
project phpunit
```

### Example code
In this repository you can find two example, one for
the [Partial Sensitization Strategy](https://github.com/matiux/broadway-sensitive-serializer/wiki/%5BIT%5D-3.Moduli#partial-strategy) and one
for [Whole Sensitization Strategy](https://github.com/matiux/broadway-sensitive-serializer/wiki/%5BIT%5D-3.Moduli#whole-strategy).
Of course, you will also find many ideas in the tests.

#### Run Partial Sensitization Strategy example
[example/PartialPayloadStrategy](example/PartialPayloadStrategy)
```shell
./dc up -d
./dc enter
php example/PartialPayloadStrategy/example.php
```

#### Run Whole Sensitization Strategy example
[example/WholePayloadStrategy](example/WholePayloadStrategy)
```shell
./dc up -d
./dc enter
php example/WholePayloadStrategy/example.php
```
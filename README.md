# PHP File Library

[![License](https://img.shields.io/github/license/phootwork/file.svg?style=flat-square)](https://packagist.org/packages/phootwork/file)
[![Latest Stable Version](https://img.shields.io/packagist/v/phootwork/file.svg?style=flat-square)](https://packagist.org/packages/phootwork/file)
[![Total Downloads](https://img.shields.io/packagist/dt/phootwork/file.svg?style=flat-square&colorB=007ec6)](https://packagist.org/packages/phootwork/file)<br>
[![Build Status](https://img.shields.io/scrutinizer/build/g/phootwork/file.svg?style=flat-square)](https://travis-ci.org/phootwork/file)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/phootwork/file.svg?style=flat-square)](https://scrutinizer-ci.com/g/phootwork/file)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/phootwork/file.svg?style=flat-square)](https://scrutinizer-ci.com/g/phootwork/file)

PHP File library for the local filesystem.

## Goals

- Provide abstractions to the local file system
- Inspired by java `java.io.File`
- Inspired by eclipse `org.eclipse.core.runtime.IPath`

## Installation

Install via Composer:

```
composer require phootwork/file
```

## Running tests

This package is a part of the Phootwork library. In order to run the test suite, you have to download the full library.

```
git clone https://github.com/phootwork/phootwork
```
Then install the dependencies via composer:

```
composer install
```
Now, run the *file* test suite:

```
vendor/bin/phpunit --testsuite file
```
If you want to run the whole library tests, simply run:

```
vendor/bin/phpunit
```


## Contact

Report issues at the github [Issue Tracker](https://github.com/phootwork/file/issues).

## Changelog

Refer to [Releases](https://github.com/phootwork/file/releases)

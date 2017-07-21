# phpList 4 base distribution

[![Build Status](https://travis-ci.org/phpList/base-distribution.svg?branch=master)](https://travis-ci.org/phpList/base-distribution)
[![Latest Stable Version](https://poser.pugx.org/phplist/base-distribution/v/stable.svg)](https://packagist.org/packages/phpList/base-distribution)
[![Total Downloads](https://poser.pugx.org/phplist/base-distribution/downloads.svg)](https://packagist.org/packages/phpList/base-distribution)
[![Latest Unstable Version](https://poser.pugx.org/phplist/base-distribution/v/unstable.svg)](https://packagist.org/packages/phpList/base-distribution)
[![License](https://poser.pugx.org/phplist/base-distribution/license.svg)](https://packagist.org/packages/phpList/base-distribution)

## About phpList

phpList is an open source newsletter manager. This project is a rewrite of the
[original phpList](https://github.com/phpList/phplist3).


## About this package

This module is the basis for a phpList 4 installation. It will pull in the
`phplist/phplist4-core` module (the phpList 4 core) and by default also the
`phplist/rest-api` module (the new REST API package). The rest-api dependency
is optional.

This package is intended to be cloned and then modified. It is not intended to
be updated via Composer after the initial install (although updating the
dependencies via composer update is fine).

This (i.e., the downloaded package) is also the place where configuration files
will be stored (or symlinked to).


## Installation

1. Download and install [composer](https://getcomposer.org/download/).
2. Run `composer create-project -s dev phplist/base-distribution your-project`
   (use any name you like for the `your-project` directory).
3. Switch to the `your-project` directory.
4. If you would like to not have the REST API, edit the `composer.json`,
   remove the corresponding `phplist/rest-api` requirement, and run
   `composer update`.


## Contributing to this package

Please read the [contribution guide](.github/CONTRIBUTING.md) on how to
contribute and how to run the unit tests and style checks locally.

### Code of Conduct

This project adheres to a [Contributor Code of Conduct](CODE_OF_CONDUCT.md).
By participating in this project and its community, you are expected to uphold
this code.

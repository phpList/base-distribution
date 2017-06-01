# phpList 4 base distribution

[![Build Status](https://travis-ci.org/phpList/base-distribution.svg?branch=master)](https://travis-ci.org/phpList/base-distribution)
[![Latest Stable Version](https://poser.pugx.org/phplist/base-distribution/v/stable.svg)](https://packagist.org/packages/phpList/base-distribution)
[![Total Downloads](https://poser.pugx.org/phplist/base-distribution/downloads.svg)](https://packagist.org/packages/phpList/base-distribution)
[![Latest Unstable Version](https://poser.pugx.org/phplist/base-distribution/v/unstable.svg)](https://packagist.org/packages/phpList/base-distribution)
[![License](https://poser.pugx.org/phplist/base-distribution/license.svg)](https://packagist.org/packages/phpList/base-distribution)

This module is the basis for a phpList 4 installation. It will pull in the
`phplist/phplist4-core` module (the phpList 4 core), by default also the
`phplist/rest-api` module (the new REST API package) and (in the future) the
`phplist/web-frontend` module (all via Composer). Both dependencies are
optional.

This package is intended to be cloned and then modified. It is not intended to
be updated via Composer after the initial install (although updating the
dependencies via composer update is fine).

This (i.e., the downloaded package) is also the place where configuration files
will be stored (or symlinked to).

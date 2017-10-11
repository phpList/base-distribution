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
2. Run `composer create-project -s dev --no-dev phplist/base-distribution your-project`
   (use any name you like for the `your-project` directory).
3. Switch to the `your-project` directory.
4. If you would like to not have the REST API, edit the `composer.json`,
   remove the corresponding `phplist/rest-api` requirement, and run
   `composer update`.


## Configuring and running phpList on a web server

The phpList application is configured so that the built-in PHP web server can
run in development and testing mode, while Apache can run in production mode.

### Production on Apache

1. In the Apache virtual host configuration, set the directory `web/` as the
   document root.
2. Set the phpList database credentials as environment variables in the Apache
   virtual host configuration. Your Apache 2.4 configuration then could look
   like this:

```
<VirtualHost *:80>
    ServerName domain.tld
    ServerAlias www.domain.tld

    DocumentRoot /var/www/project/web
    <Directory /var/www/project/web>
        AllowOverride All
        Require all granted
    </Directory>

    # uncomment the following lines if you install assets as symlinks
    # or run into problems when compiling LESS/Sass/CoffeeScript assets
    # <Directory /var/www/project>
    #     Options FollowSymlinks
    # </Directory>

    SetEnv PHPLIST_DATABASE_NAME phplist
    SetEnv PHPLIST_DATABASE_USER phplist
    SetEnv PHPLIST_DATABASE_PASSWORD correctHorseBatteryStaple

    ErrorLog /var/log/apache2/project_error.log
    CustomLog /var/log/apache2/project_access.log combined
</VirtualHost>
```

Then reload or restart Apache to activate the configuration changes.

Use the following optimized configuration to disable .htaccess support and increase web server performance:

```
<VirtualHost *:80>
    ServerName domain.tld
    ServerAlias www.domain.tld

    DocumentRoot /var/www/project/web
    <Directory /var/www/project/web>
        AllowOverride None
        Order Allow,Deny
        Allow from All

        <IfModule mod_rewrite.c>
            Options -MultiViews
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^(.*)$ app.php [QSA,L]
        </IfModule>
    </Directory>

    SetEnv PHPLIST_DATABASE_NAME phplist
    SetEnv PHPLIST_DATABASE_USER phplist
    SetEnv PHPLIST_DATABASE_PASSWORD correctHorseBatteryStaple

    ErrorLog /var/log/apache2/project_error.log
    CustomLog /var/log/apache2/project_access.log combined
</VirtualHost>
```

### Development

For running the application in development mode using the built-in PHP server,
use this command:

    bin/console server:run -d web/

The server will then listen on `http://127.0.0.1:8000` (or, if port 8000 is
already in use, on the next free port after 8000).

You can stop the server with CTRL + C.

### Testing

To run the server in testing mode (which normally will only be needed for the
automated tests, provide the `--env` option:

    bin/console server:run -d web/ --env=test


## Useful Composer scripts

You can get a list of all installed phpList modules with this command:

    composer run-script list-modules


## Contributing to this package

Please read the [contribution guide](.github/CONTRIBUTING.md) on how to
contribute and how to run the unit tests and style checks locally.

### Code of Conduct

This project adheres to a [Contributor Code of Conduct](CODE_OF_CONDUCT.md).
By participating in this project and its community, you are expected to uphold
this code.

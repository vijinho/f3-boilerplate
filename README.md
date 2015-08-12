# f3-boilerplate

![f3-boilerplate homepage screenshot](tmp/screenshot.png)

Skeleton PHP Fatfree-framework MVC website codebase.

## Setup

### Composer and Webserver

- [Get Composer](https://getcomposer.org/) - `curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin -filename=composer`
- Run `composer update`
- Setup webserver config from [app/config/webserver](app/config/webserver)
- OR run with php in-built webserver from [www](www): `php -S http://127.0.0.1:8080` and browse to [http://127.0.0.1:8080](http://127.0.0.1:8080)

### Configuration
  - Copy `app/config/config.ini.example` to `config.ini`
  - Edit `app/config/config.ini `and add anything extra from `default.ini` for overrides
  - In the top level folder `run composer install`

### Folders & Permissions
Setup empty website folders as follows:

```
mkdir -p tmp/cache tmp/sessions tmp/uploads tmp/logs data
sudo chown -fR www-data:www-data tmp data
sudo chmod -fR 777 tmp data
```

## Description of Project Layout

 * `www` - website and public doc root (aka `public_html` or `htdocs` etc)
 * `www/index.php` - start website application here
 * `app/lib/` - all library files/classes
 * `app/lib/bcosca/fatfree-core` - [fatfree framework (core)](https://github.com/bcosca/fatfree-core) lives here
 * `tmp/cache` `tmp/sessions` `tmp/uploads` - temporary files
 * `tmp/logs` - application logfiles
 * `data` - website data storage
 * `app` - the website application lives outside the webroot for security `www/index.php` is the default file used by `.htaccess` for routing
 * `app/doc` - application documentation (markdown files)
 * `app/config` - application configuration files
 * `app/config/vhost` - application virtual host configuration files (apache and nginx supported)
 * `app/app.php` - start fatfree by including this file and running FFMVC\App\Run();
 * `app/cli.php` - command-line specific bootstrap instructions
 * `app/lib/FFMVC/App` - Base Application Classes
 * `app/lib/FFMVC/Models` - MVC Models
 * `app/lib/FFMVC/Controllers` - MVC Controllers
 * `app/lib/FFMVC/Controllers/Api` - MVC Rest API Controllers
 * `app/lib/FFMVC/Helpers` - Useful static helper functions and utility libraries specific to the project
 * `app/lib/FFMVC/CLI` - Command line script controller lib
 * `app/templates/www/error/` - these files are standard php includes, not f3 templates, used by the error handler function
 * `app/templates/www/error/debug.phtml` - debug error page (if DEBUG=3)
 * `app/templates/www/error/404.phtml` - 'friendly' file not found page
 * `app/templates/www/error/error.phtml` - 'friendly' error page

#### SSL (Optional)
Runs by default on [api.local](http://api.local/)

`openssl req -new -newkey rsa:4096 -days 365 -nodes -x509 -subj "/C=GB/ST=STATE/L=TOWN/O=Office/CN=api.local" -keyout api.key -out api.crt`

Add to apache virtual host (and also see the api-ssl.local files in [app/config/webserver/](app/config/webserver/)

```
    SSLCertificateFile ssl/api.crt
    SSLCertificateKeyFile ssl/api.key
```
[MAMP](https://www.mamp.info/) lets you add the SSL file in the Hosts/SSL tab.

--
http://about.me/vijay.mahrra

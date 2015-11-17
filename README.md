# f3-boilerplate

![f3-boilerplate homepage screenshot](tmp/screenshot.png)

Skeleton PHP Fatfree-framework MVC website codebase.

**Project Goal:** Be a good and extremely flexible starting-point for implementing any kind of PHP project in F3.


## Is this project for you?

I wrote this project for myself, but if you are thinking to use it, thinking on the points below will help you decide.

* You want to implement a website using the Fat-Free Framework
* You need a stable project that can be easily adapted and altered to suit whatever your web development needs are.
* You need to quickly and easily integrate composer classes into a project structure to get up and running ASAP.
* You need to write some boilerplate code to add project structure and initialise some commons tasks like config, logging, database connections, set up environments for production and development etc
* You may want the ability to setup your database connections in the http format - dbms://user:host@server:port/databasename
* You may want to easily have a way to attach multiple sqlite databases together to use with f3.
* You are thinking to run f3 on the command-line and want to see how it could be done.
* You are thinking to write an API based on REST/XML responses and would like a starting point for to how to implement it in f3.
* You would like to see a real-life example of f3 features for render markdown, display geo-location, database connectivity.
* You want to use f3 features like minify css and js and have these routes set up and ready-to-use.
* You want to have your project configuration split up into different files for the main configuration as have a local override file.
* You would like to have your script log how long it took to run and how much memory it used after executing when in 'development' mode.
* You need to make sure that ALL script input is normalised and cleaned by default.
* You want to use namespaces in your project

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

*Note:* The files that were in `app/lib/FFMVC` have now been split-out into their own repository https://github.com/vijinho/FFMVC 
They can then be included in your own project by adding the same lines in your `composer.json` as used in mine here.

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
 * `app/lib/FFMVC/Helpers` - Auxillary helper functions and utility libraries specific to the project
 * `app/lib/FFMVC/CLI` - Controllers for when executing in a command-line environemnt
 * `app/templates/www/error/` - these files are standard php includes, not f3 templates, used by the error handler function
 * `app/templates/www/error/debug.phtml` - debug error page (if DEBUG=3)
 * `app/templates/www/error/404.phtml` - 'friendly' file not found page
 * `app/templates/www/error/error.phtml` - 'friendly' error page

### External Libraries
 * [Climate](http://climate.thephpleague.com/) is used for the CLI utility methods.

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

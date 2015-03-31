# f3-boilerplate

Empty PHP Fatfree-framework MVC website code 

## Setup

### Configuration
 - Copy `app/config/config.ini.example` to `config.ini`
 - Edit `app/config/config.ini `and add anything extra from `default.ini` for overrides

### Folders & Permissions
Setup empty website folders as follows:

```
mkdir -p tmp/cache tmp/sessions tmp/uploads logs data
sudo chown -fR www-data:www-data tmp logs data
sudo chmod -fR 777 tmp logs data
```

## Description of Project Layout

 * `www` - website and public doc root (aka `public_html` or `htdocs` etc)
 * `www/index.php` - start website application here
 * `vendor/fatfree` - fatfree framework lives here
 * `tmp/cache` `tmp/sessions` `tmp/uploads` - temporary files
 * `logs` - webserver and application logfiles
 * `data` - website data storage
 * `vendor` - other external include files/classes
 * `src` - src code for projects used on the site
 * `app` - the website application lives outside the webroot for security `www/index.php` is the default file used by `.htaccess` for routing
 * `app/doc` - application documentation (markdown files)
 * `app/config` - application configuration files
 * `app/config/vhost` - application virtual host configuration files (apache and nginx supported)
 * `app/app.php` - start fatfree by including this file and running FFMVC\App\Run();
 * `app/cli.php` - command-line specific bootstrap instructions
 * `app/lib/FFMVC/App` - Base Application Classes
 * `app/lib/FFMVC/Models` - MVC Models
 * `app/lib/FFMVC/Controllers` - MVC Controllers
 * `app/lib/FFMVC/Helpers` - Useful static helper functions and utility libraries specific to the project
 * `app/lib/FFMVC/CLI` - Command line script controller lib
 * `app/ui/views/error/` - these files are standard php includes, not f3 templates, used by the error handler function
 * `app/ui/views/error/debug.phtml` - debug error page (if DEBUG=3)
 * `app/ui/views/error/404.phtml` - 'friendly' file not found page
 * `app/ui/views/error/error.phtml` - 'friendly' error page

--
http://about.me/vijay.mahrra

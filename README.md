f3-boilerplate
==============

My fatfree framework boilerplate MVC website code using html5boilerplate

Setup:
# Copy app/config/config.ini.example to config.ini
# Edit app/config/config.ini and add anything extra from default.ini for overrides

Layout:

* vendor - external include files/classes
* vendor/fatfree - fatfree framework code
* src - src code for projects used on the site, e.g. html5boilerplate
* www - website and public doc root (aka public_html or htdocs)
* app - website application lives outside the webroot for security www/index.php is the default file used by .htaccess for routing
* app/bootstrap.php - start fatfree by including this file from the webroot or running command-line php against it
* app/doc - application documentation
* app/config - application configuration files
* app/config/vhost - application virtual host configuration files
* app/classes/models - models
* app/classes/controllers - controllers
* app/classes/helpers - useful static helper functions
* app/classes/cli - command line script classes
* app/ui/views/error/ - these files are standard php includes, not f3 templates, used by the error handler function
* app/ui/views/error/debug.phtml - debug error page (if DEBUG=3)
* app/ui/views/error/404.phtml - 'friendly' file not found page
* app/ui/views/error/error.phtml - 'friendly' error page
* data - website data storage
* logs - webserver and application logfiles
* tmp/cache tmp/sessions tmp/uploads - temporary files

vijay.mahrra@gmail.com

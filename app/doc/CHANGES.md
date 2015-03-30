# Application Releases #

### Version 1.1 ###

* Use the f3 minifier on the CSS and JS files
* Add a redirect example to the URL routes
* Show useragent on homepage to demonstrate f3 UA detection
* Show geolocation data on homepage to demonstrate f3 GEO-IP detection
* Add caching for documentation URLs
* Model example altered to allow $db and $logger to be injected dependencies
* Display extra error information if in development mode
* BUGFIX: Homepage will display OK without error even if app not configured
* BUGFIX: Prevent error 500 from .htaccess when running under CGI

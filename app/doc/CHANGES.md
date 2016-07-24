# Application Releases

# Version 2.1.0

- Added self-signed local SSL certificates in [app/config/webserver/ssl](app/config/webserver/ssl)
- Using DEBUG=4 will use [Whoops](https://github.com/filp/whoops) for nicer error handling
- Added code for CSRF check
- Renamed Messages class to Notifications
- Removed XML option in API 
- Now using [Named Routes](https://fatfreeframework.com/base#NamedRoutes)
- Now using [PHPMailer](https://github.com/PHPMailer/PHPMailer) as replacement for f3's SMTP mailer with extra config settings
- Now using [Wixel GUMP](https://github.com/Wixel/GUMP) for data validation

# Version 2.0.0

- Switched to [Semantic Versioning 2.0.0](http://semver.org)
- CSS [Bootstrap](http://getbootstrap.com) by default now

## Version 1.6

- Changes for PHP7
- Moved [app/lib/FFMVC](https://github.com/vijinho/FFMVC) file into their own repository now to include vio composer
- Added messages helper to store/manage messages for displaying to the end-user
- Output buffering enabled by default to display errors better
- Added a string helper class for generating random strings and hashing
- Log php errors to log file
- Api controller example can perform a basic authentication against a db
- Api controller can fetch any Bearer access token in the incoming request (e.g for OAuth)
- Show /api/ HTTP errors as JSON/XML
- Set 'PageHash' as a unique HASH for each page
- Allow f3 to reference $_GET params on a command-line URL
- Make sure f3 environment variable paths in config are full filepaths, not relative
- [Climate](http://climate.thephpleague.com/) is used for the CLI utility methods.
- Do not auto-start sessions for /api URLs

## Version 1.5

- Load in fatfree-core as a library
- Use composer autoloader
- All user GET/POST input is now filtered
- Removed html5boilerplate files - now uses [skeleton](http://getskeleton.com) instead
- Includes a simple, REST API example
- Start page checks for writable file locations and if optional db is connected and works
- PSR2-compliant coding style
- Removed broken language files
- Default model expanded, supports dependency injection of logger and db

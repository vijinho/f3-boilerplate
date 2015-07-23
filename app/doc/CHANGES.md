# Application Releases #

### Version 1.6 ###

- Added messages helper to store/manage messages for displaying to the end-user
- Output buffering enabled by default to display errors better
- Added a string helper class for generating random strings and hashing

### Version 1.5 ###

- Load in fatfree-core as a library
- Use composer autoloader
- All user GET/POST input is now filtered
- Removed html5boilerplate files - now uses [skeleton](http://getskeleton.com) instead
- Includes a simple, REST API example
- Start page checks for writable file locations and if optional db is connected and works
- PSR2-compliant coding style
- Removed broken language files
- Default model expanded, supports dependency injection of logger and db

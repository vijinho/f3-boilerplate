 # Test Environment

## F3 Unit Tests

 Require the file `tests/setup.php` which contains the function `setup()` which will do the following:

 - Setup the base application environment
 - Read the `dsn_test` database DSN value from the config.ini
 - Delete all existing tables in the test database
 - Execute Setup::database from `app/lib/App/Setup.php` which will import the sql dump from `data/db/sql/create.sql` and create a new root user and api app for that user.
 - It then returns an instance of $f3 which can be used by the test suite

This environment can then be used for safe testing which doesn't interfere with the running website.

### Run the test database setup and checks

```
cd tests
php setuptest.php
```

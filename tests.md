How to run tests
====

```
# install php-cs-fixer
composer global require fabpot/php-cs-fixer dev-master

# go to the project's root directory, but NOT the tests subdirectory 
cd <project_dir>

# install dependencies
composer update

# check coding style
php-cs-fixer fix --dry-run

# fix coding style
php-cs-fixer fix

# run tests
sh ./tests/run.sh
```

Advanced usage
----

You can use these commands to do more specific tasks.

```
# generate necessary files to run the tests
./vendor/bin/codecept build

# run the unit suite
./vendor/bin/codecept run unit

# run the integration suite
./vendor/bin/codecept run integration

# run specific test
./vendor/bin/codecept run tests/unit/src/FooTest.php
```

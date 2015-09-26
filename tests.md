How to run tests
====

```
# go to the project's root directory, but NOT the tests subdirectory 
cd <project_dir>

# install dependencies
composer update

# run the coding style checker and all tests
sh ./tests/run.sh

# fix coding style problems automatically
sh ./tests/fix.sh
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

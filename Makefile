PHP_BIN := php

test:
	$(PHP_BIN) vendor/bin/phpunit

coverage:
	$(PHP_BIN) vendor/bin/phpunit --coverage-html html

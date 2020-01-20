install:
		composer install
start:
		php -S localhost:8000 -t public public/index.php
lint:
		composer run-script phpcs -- --standard=PSR2,PSR12 public bin src templates
getData: 
	php bin/getData
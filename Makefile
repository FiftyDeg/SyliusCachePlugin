phpunit:
	vendor/bin/phpunit

phpspec:
	vendor/bin/phpspec run --ansi --no-interaction -f dot

phpstan:
	vendor/bin/phpstan analyse

psalm:
	vendor/bin/psalm

behat-js:
	APP_ENV=test vendor/bin/behat --colors --strict --no-interaction -vvv -f progress

install:
	composer install --no-interaction --no-scripts

backend:
	tests/Application/bin/console sylius:install --no-interaction
	tests/Application/bin/console sylius:fixtures:load default --no-interaction --verbose

frontend:
	(cd tests/Application && yarn install --pure-lockfile)
	(cd tests/Application && GULP_ENV=prod yarn build)

backend-test:
	tests/Application/bin/console sylius:install --no-interaction --env test
	tests/Application/bin/console sylius:fixtures:load default --no-interaction --verbose

frontend-test:
	(cd tests/Application && yarn install --pure-lockfile)
	(cd tests/Application && GULP_ENV=prod yarn build)

init: install backend frontend

init-test: install backend-test frontend-test

behat:
	APP_ENV=test vendor/bin/behat --colors --strict --no-interaction -vvv -f progress

ci: init phpstan psalm phpunit phpspec behat

integration: init phpunit behat

static: install phpspec phpstan psalm

#!/bin/bash
export $(grep -v '^#' .env.test | xargs)

RED='\033[0;31m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

printf "${CYAN}\r\n********************\r\n********************\r\n** 💡 STANDARDS ****\r\n********************\r\n********************\r\n\r\n${NC}"
docker exec -u root -it "fifty_deg_sylius_cache_plugin_php" bash -c "XDEBUG_MODE=off php vendor/bin/ecs check"

printf "${CYAN}\r\n********************\r\n********************\r\n** 💡 PSALM ********\r\n********************\r\n********************\r\n\r\n${NC}"
docker exec -u root -it "fifty_deg_sylius_cache_plugin_php" bash -c "XDEBUG_MODE=off php vendor/bin/psalm --no-cache"

printf "${CYAN}\r\n********************\r\n********************\r\n** 💡 PHPSTAN ******\r\n********************\r\n********************\r\n\r\n${NC}"
docker exec -u root -it "fifty_deg_sylius_cache_plugin_php" bash -c "XDEBUG_MODE=off php vendor/bin/phpstan analyse -c phpstan.neon -l max src/"

printf "${CYAN}\r\n********************\r\n********************\r\n** 💡 PHPMD ********\r\n********************\r\n********************\r\n\r\n${NC}"
docker exec -u root -it "fifty_deg_sylius_cache_plugin_php" bash -c "XDEBUG_MODE=off php vendor/bin/phpmd src,tests/Behat github phpmd.ruleset.xml"

printf "${CYAN}\r\n********************\r\n********************\r\n** 💡 CONTAINER ****\r\n********************\r\n********************\r\n\r\n${NC}"
docker exec -u root -it "fifty_deg_sylius_cache_plugin_php" bash -c "cd tests/Application && XDEBUG_MODE=off php bin/console lint:container"

printf "${CYAN}\r\n********************\r\n********************\r\n** 💡 TWIG *********\r\n********************\r\n********************\r\n\r\n${NC}"
docker exec -u root -it "fifty_deg_sylius_cache_plugin_php" bash -c "cd tests/Application && XDEBUG_MODE=off php bin/console lint:twig ../../src/* ./* ../Behat/*"

printf "${CYAN}\r\n********************\r\n********************\r\n** 💡 YAML *********\r\n********************\r\n********************\r\n\r\n${NC}"
docker exec -u root -it "fifty_deg_sylius_cache_plugin_php" bash -c "cd tests/Application && XDEBUG_MODE=off php bin/console lint:yaml --parse-tags  ../../src/ ./config ../Behat"

printf "${CYAN}\r\n********************\r\n********************\r\n** 💡 BEHAT ********\r\n********************\r\n********************\r\n\r\n${NC}"
docker exec -u root -it "fifty_deg_sylius_cache_plugin_php" bash -c "vendor/bin/behat --strict --tags='~@javascript'"

exit 0

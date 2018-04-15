#!/bin/bash

echo "SUPPRESSION VENDOR CACHE COMPOSER.LOCK"
docker-compose exec --user encrypt container_encrypt bash -c 'php -v'
#docker-compose exec --user encrypt container_encrypt bash -c 'rm -rf /var/www/html/encrypt/composer.lock'
#docker-compose exec --user encrypt container_encrypt bash -c 'rm -rf /var/www/html/encrypt/vendor'
docker-compose exec --user encrypt container_encrypt bash -c 'rm -rf /var/www/html/encrypt/var/cache'
docker-compose exec --user encrypt container_encrypt bash -c 'rm -rf /var/www/html/encrypt/var/logs'
docker-compose exec --user encrypt container_encrypt bash -c 'rm -rf /var/www/html/encrypt/var/sessions'

echo "COMPOSER INSTALL"
docker-compose exec  --user encrypt container_encrypt bash -c '/usr/local/bin/composer install'

echo "DOCTRINE : CREATE DB"
docker-compose exec  --user encrypt container_encrypt bash -c 'php /var/www/html/encrypt/bin/console doctrine:database:create'
echo "DOCTRINE : CREATE SCHEMA"
docker-compose exec  --user encrypt container_encrypt bash -c 'php /var/www/html/encrypt/bin/console doctrine:schema:update --dump-sql'
docker-compose exec  --user encrypt container_encrypt bash -c 'php /var/www/html/encrypt/bin/console doctrine:schema:update --force'
echo "DOCTRINE : FIXTURE"
docker-compose exec  --user encrypt container_encrypt bash -c 'php /var/www/html/encrypt/bin/console doctrine:fixtures:load --no-interaction'

#echo "XDEBUG"
#docker-compose exec  --user encrypt container_encrypt bash -c 'export XDEBUG_CONFIG="remote_host=192.168.0.15 idekey=PHPSTORM"'
#docker-compose exec  --user encrypt container_encrypt bash -c 'export PHP_IDE_CONFIG="serverName=192.168.0.26"'

#echo "RABBITMQ : WORKER"
#docker-compose exec  --user encrypt container_encrypt bash -c 'php /var/www/html/encrypt/bin/console rabbitmq:consumer -m 3 encrypt_worker'

echo "SUPERVISOR : RABBITMQ"
docker-compose exec --user encrypt container_encrypt bash -c 'rm -rf /var/www/html/encrypt/app/supervisor'
docker-compose exec  --user encrypt container_encrypt bash -c 'php /var/www/html/encrypt/bin/console rabbitmq-supervisor:rebuild'

exit 0

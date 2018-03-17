#!/bin/bash

sudo service mysql stop
sudo service apache2 stop
sudo service redis stop
sudo service zend-server stop
sudo service rabbitmq-server stop

sudo service memcached status > /dev/null 2>&1
if [ 3 -ne $? ]; then
    sudo service memcached stop
fi

<?php

$envVar = new \Symfony\Component\HttpFoundation\ParameterBag(getenv() ?: []);

// db
$container->setParameter('locahost', 'container_encrypt');
$container->setParameter('database_host', $envVar->get('CONTAINER_MYSQL_HOST', 'container_mysql'));
$container->setParameter('database_port', $envVar->get('CONTAINER_MYSQL_PORT', 3306));
$container->setParameter('database_user', $envVar->get('CONTAINER_MYSQL_USER', 'root'));
$container->setParameter('database_password', $envVar->get('CONTAINER_MYSQL_PASSWORD', 'secret'));
$container->setParameter('database_name', 'encrypt');
$container->setParameter('database_path', '%kernel.project_dir%/var/data/data.sqlite%');

// Memcached
$container->setParameter('memcached_host', $envVar->get('CONTAINER_MEMCACHED_HOST', $container->getParameter('locahost')));
$container->setParameter('memcached_port', $envVar->get('CONTAINER_MEMCACHED_PORT', 11211));

// RabbitMq
$container->setParameter('rabbitmq_host', $envVar->get('CONTAINER_RABBITMQ_HOST', $container->getParameter('locahost')));
$container->setParameter('rabbitmq_user', $envVar->get('CONTAINER_RABBITMQ_USER', 'encrypt'));
$container->setParameter('rabbitmq_pass', $envVar->get('CONTAINER_RABBITMQ_PASS', 'secret'));
$container->setParameter('rabbitmq_vhost', $envVar->get('CONTAINER_RABBITMQ_VHOST', 'encrypt'));

// Redis
$container->setParameter('redis_host', $envVar->get('CONTAINER_REDIS_HOST', $container->getParameter('locahost')));
$container->setParameter('redis_user', $envVar->get('CONTAINER_REDIS_USER', 'root'));

// swiftmailer
$container->setParameter('mailer_transport', 'smtp');
$container->setParameter('mailer_host', $container->getParameter('locahost'));
$container->setParameter('mailer_user', null);
$container->setParameter('mailer_password', null);
$container->setParameter('secret', 'ThisTokenIsNotSoSecretChangeIt');

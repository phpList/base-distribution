<?php
/**
 * Using old config file
 */

include_once __DIR__.'/../../config/config.php';

$container->setParameter('database_host', $database_host);
$container->setParameter('database_port', $database_port);
$container->setParameter('database_name', $database_name);
$container->setParameter('database_user', $database_user);
$container->setParameter('database_password', $database_password);
$container->setParameter('database_path', null);
$container->setParameter('database_driver','pdo_mysql');
$container->setParameter('secret', 'be0652de9881338f9e597fd8fa459ad09b422dae');
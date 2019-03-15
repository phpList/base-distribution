<?php
/**
 * Using old config file
 */

$standardConfig = __DIR__ . '/../../config/config.php';

if (isset($_SERVER['ConfigFile']) && is_file($_SERVER['ConfigFile'])) {
    include $_SERVER['ConfigFile'];

} elseif (file_exists($standardConfig)) {
    include $standardConfig;
} else {
    throw new \Exception("Error: Cannot find config file");
}

$charset = 'utf8mb4';
$dsn = "mysql:host=$database_host;dbname=$database_name;charset=$charset";
$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
);
try {
    $pdo = new PDO($dsn, $database_user, $database_password, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
$statement = $pdo->prepare("select value from phplist_config where value = :value");
$statement->execute(array(':value' => "secret"));
$row = $statement->fetch();

$container->setParameter('database_host', $database_host);
$container->setParameter('database_port', $database_port);
$container->setParameter('database_name', $database_name);
$container->setParameter('database_user', $database_user);
$container->setParameter('database_password', $database_password);
$container->setParameter('database_path', $table_prefix);
$container->setParameter('database_driver','pdo_mysql');
$container->setParameter('secret', $row);
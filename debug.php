<?php
header('Content-Type: text/plain');

echo "DB_HOST: " . getenv('DB_HOST') . "\n";
echo "DB_PORT: " . getenv('DB_PORT') . "\n";
echo "DB_NAME: " . getenv('DB_NAME') . "\n";
echo "DB_USER: " . getenv('DB_USER') . "\n";
echo "DB_SSL_CA: " . getenv('DB_SSL_CA') . "\n";
echo "Cert file exists: " . (file_exists(getenv('DB_SSL_CA')) ? 'YES' : 'NO') . "\n";
if (file_exists(getenv('DB_SSL_CA'))) {
    echo "Cert file size: " . filesize(getenv('DB_SSL_CA')) . " bytes\n";
    echo "Cert starts with: " . substr(file_get_contents(getenv('DB_SSL_CA')), 0, 30) . "\n";
}

echo "\n--- Connection attempt ---\n";
try {
    $pdo = new PDO(
        "mysql:host=" . getenv('DB_HOST') . ";port=" . getenv('DB_PORT') . ";dbname=" . getenv('DB_NAME'),
        getenv('DB_USER'),
        getenv('DB_PASS'),
        [
            PDO::MYSQL_ATTR_SSL_CA => getenv('DB_SSL_CA'),
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
        ]
    );
    echo "SUCCESS: Connected!\n";
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
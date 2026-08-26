<?php

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'nexar';

$connection = new mysqli($host, $user, $password);

if ($connection->connect_error) {
    die('Erro de conexão: ' . $connection->connect_error);
}

$connection->set_charset('utf8mb4');

$sql_db = "CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
$connection->query($sql_db);

$connection->select_db($database);

$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL UNIQUE,
    account_type ENUM('entrepreneur','supplier','admin') NOT NULL DEFAULT 'entrepreneur',
    role ENUM('client','provider','admin') NOT NULL DEFAULT 'client',
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    avatar VARCHAR(255) NULL DEFAULT NULL,
    phone VARCHAR(20) NULL DEFAULT NULL,
    country CHAR(2) NULL DEFAULT NULL,
    city VARCHAR(100) NULL DEFAULT NULL,
    state VARCHAR(2) NULL DEFAULT NULL,
    timezone VARCHAR(50) NULL DEFAULT 'UTC',
    locale VARCHAR(10) NULL DEFAULT 'en',
    status ENUM('active','inactive','banned','pending') NOT NULL DEFAULT 'active',
    last_login_at TIMESTAMP NULL DEFAULT NULL,
    last_login_ip VARCHAR(45) NULL DEFAULT NULL,
    settings JSON NULL,
    metadata JSON NULL,
    remember_token VARCHAR(100) NULL DEFAULT NULL,
    email_verification_token VARCHAR(100) NULL DEFAULT NULL,
    password_reset_token VARCHAR(100) NULL DEFAULT NULL,
    password_reset_expires TIMESTAMP NULL DEFAULT NULL,
    failed_login_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$connection->query($sql_users);

$sql_entrepreneurs = "CREATE TABLE IF NOT EXISTS entrepreneurs (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    cnpj VARCHAR(18) NULL DEFAULT NULL,
    legal_name VARCHAR(255) NULL DEFAULT NULL,
    trade_name VARCHAR(255) NULL DEFAULT NULL,
    phone VARCHAR(20) NULL DEFAULT NULL,
    business_segment VARCHAR(150) NULL DEFAULT NULL,
    city VARCHAR(100) NULL DEFAULT NULL,
    state VARCHAR(2) NULL DEFAULT NULL,
    company_photo LONGBLOB NULL,
    employees_range VARCHAR(50) NULL DEFAULT NULL,
    revenue_range VARCHAR(50) NULL DEFAULT NULL,
    interested_categories TEXT NULL,
    products_purchased TEXT NULL,
    purchase_frequency VARCHAR(100) NULL DEFAULT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$connection->query($sql_entrepreneurs);

$sql_suppliers = "CREATE TABLE IF NOT EXISTS suppliers (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    cnpj VARCHAR(18) NULL DEFAULT NULL,
    legal_name VARCHAR(255) NULL DEFAULT NULL,
    trade_name VARCHAR(255) NULL DEFAULT NULL,
    phone VARCHAR(20) NULL DEFAULT NULL,
    city VARCHAR(100) NULL DEFAULT NULL,
    state VARCHAR(2) NULL DEFAULT NULL,
    business_segment VARCHAR(150) NULL DEFAULT NULL,
    logo LONGBLOB NULL,
    cover_image LONGBLOB NULL,
    description TEXT NULL,
    category VARCHAR(150) NULL DEFAULT NULL,
    main_products TEXT NULL,
    service_region VARCHAR(150) NULL DEFAULT NULL,
    website VARCHAR(255) NULL DEFAULT NULL,
    whatsapp VARCHAR(50) NULL DEFAULT NULL,
    plan ENUM('basic','professional','premium') NOT NULL DEFAULT 'basic',
    metadata JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$connection->query($sql_suppliers);

return $connection;


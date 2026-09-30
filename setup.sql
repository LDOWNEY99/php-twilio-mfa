-- PHP Twilio MFA demo database

CREATE DATABASE IF NOT EXISTS twilio_mfa
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE twilio_mfa;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL
);

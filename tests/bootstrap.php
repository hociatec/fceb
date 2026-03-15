<?php

require dirname(__DIR__).'/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

if (class_exists(Dotenv::class) && !isset($_SERVER['DATABASE_URL'])) {
    (new Dotenv())->loadEnv(dirname(__DIR__).'/.env');
}

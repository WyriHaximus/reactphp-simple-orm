<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->load();

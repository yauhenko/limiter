#!/usr/bin/php
<?php

use App\Command\Install;
use App\Command\Run;
use App\Command\Uninstall;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application;
$app->add(new Run());
$app->add(new Install());
$app->add(new Uninstall());
$app->run();
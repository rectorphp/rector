#!/usr/bin/env php
<?php declare(strict_types=1);

use Nette\Utils\Json;

require_once __DIR__ . '/../bootstrap.php';

// load
$composerJsonPath = __DIR__ . '/../build/composer.json';
$composerContent = Json::decode(file_get_contents($composerJsonPath), Json::FORCE_ARRAY);

// remove unused sections
unset($composerContent['require-dev'], $composerContent['scripts'], $composerContent['config'], $composerContent['autoload-dev'], $composerContent['authors']);

// change name
$composerContent['name'] = 'rector/rector-prefixed';

// keep only requirements on PHP 7.1+
$composerContent['require'] = [
    'php' => '^7.1',
];

// save
file_put_contents($composerJsonPath, Json::encode($composerContent, Json::PRETTY));

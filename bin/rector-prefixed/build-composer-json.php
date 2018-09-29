#!/usr/bin/env php
<?php declare(strict_types=1);

use Nette\Utils\FileSystem;
use Nette\Utils\Json;

require_once __DIR__ . '/../bootstrap.php';

$buildDestination = getenv('BUILD_DESTINATION');

// load
$composerJsonPath =  $buildDestination . '/composer.json';
$composerContent = Json::decode(FileSystem::read($composerJsonPath), Json::FORCE_ARRAY);

// remove unused sections
unset($composerContent['require-dev'], $composerContent['scripts'], $composerContent['config'], $composerContent['autoload-dev'], $composerContent['authors']);

// change name
$composerContent['name'] = 'rector/rector-prefixed';

// keep only requirements on PHP 7.1+
$composerContent['require'] = [
    'php' => '^7.1',
];

// save
FileSystem::write($composerJsonPath, Json::encode($composerContent, Json::PRETTY));

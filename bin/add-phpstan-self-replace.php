<?php

// this is part of downgrade build
declare (strict_types=1);
namespace RectorPrefix202403;

use RectorPrefix202403\Nette\Utils\FileSystem;
use RectorPrefix202403\Nette\Utils\Json;
require __DIR__ . '/../vendor/autoload.php';
$composerJsonFileContents = FileSystem::read(__DIR__ . '/../composer.json');
$composerJson = Json::decode($composerJsonFileContents, Json::FORCE_ARRAY);
$composerJson['replace']['phpstan/phpstan'] = $composerJson['require']['phpstan/phpstan'];
$modifiedComposerJsonFileContents = Json::encode($composerJson, Json::PRETTY);
FileSystem::write(__DIR__ . '/../composer.json', $modifiedComposerJsonFileContents, null);
echo 'Done!' . \PHP_EOL;

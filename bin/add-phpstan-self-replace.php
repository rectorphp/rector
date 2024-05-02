<?php

// this is part of downgrade build
declare (strict_types=1);
namespace RectorPrefix202405;

use RectorPrefix202405\Nette\Utils\FileSystem;
use RectorPrefix202405\Nette\Utils\Json;
require __DIR__ . '/../vendor/autoload.php';
$composerJsonFileContents = FileSystem::read(__DIR__ . '/../composer.json');
$composerJson = Json::decode($composerJsonFileContents, \true);
$composerJson['replace']['phpstan/phpstan'] = $composerJson['require']['phpstan/phpstan'];
$modifiedComposerJsonFileContents = Json::encode($composerJson, \true);
FileSystem::write(__DIR__ . '/../composer.json', $modifiedComposerJsonFileContents, null);
echo 'Done!' . \PHP_EOL;

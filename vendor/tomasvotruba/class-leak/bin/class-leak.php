<?php

declare (strict_types=1);
namespace RectorPrefix202609;

use RectorPrefix202609\Entropy\Console\ConsoleApplication;
use RectorPrefix202609\TomasVotruba\ClassLeak\DependencyInjection\ContainerFactory;
if (\file_exists(__DIR__ . '/../../../../vendor/autoload.php')) {
    // project's autoload
    require_once __DIR__ . '/../../../../vendor/autoload.php';
}
if (\file_exists(__DIR__ . '/../vendor/scoper-autoload.php')) {
    // A. build downgraded package
    require_once __DIR__ . '/../vendor/scoper-autoload.php';
} elseif (\file_exists(__DIR__ . '/../vendor/autoload.php')) {
    // B. local repository
    require_once __DIR__ . '/../vendor/autoload.php';
}
$containerFactory = new ContainerFactory();
$container = $containerFactory->create();
/** @var ConsoleApplication $consoleApplication */
$consoleApplication = $container->make(ConsoleApplication::class);
$exitCode = $consoleApplication->run($argv);
exit($exitCode);

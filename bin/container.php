<?php declare(strict_types=1);

use Rector\DependencyInjection\ContainerFactory;
use Symfony\Component\Console\Input\ArgvInput;
use Symplify\PackageBuilder\Configuration\ConfigFileFinder;
use Symplify\PackageBuilder\Configuration\LevelFileFinder;

$configFiles = [];

// Detect configuration from --level
$configFiles[] = (new LevelFileFinder())->detectFromInputAndDirectory(new ArgvInput(), __DIR__ . '/../config/level');

// And from --config or default one
ConfigFileFinder::detectFromInput('rector', new ArgvInput());
$configFiles[] = ConfigFileFinder::provide('rector', ['rector.yml', 'rector.yaml']);

// remove empty values
$configFiles = array_filter($configFiles);

// 3. Build DI container
$containerFactory = new ContainerFactory();
if ($configFiles) {
    return $containerFactory->createWithConfigFiles($configFiles);
}

return $containerFactory->create();

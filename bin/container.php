<?php declare(strict_types=1);

use Rector\DependencyInjection\ContainerFactory;
use Symfony\Component\Console\Input\ArgvInput;
use Symplify\PackageBuilder\Configuration\ConfigFileFinder;
use Symplify\PackageBuilder\Configuration\LevelFileFinder;

// 1. Detect configuration from --level
$configFile = (new LevelFileFinder())->detectFromInputAndDirectory(new ArgvInput(), __DIR__ . '/../config/level');

// 2. Or from --config
if ($configFile === null) {
    ConfigFileFinder::detectFromInput('rector', new ArgvInput());
    $configFile = ConfigFileFinder::provide('rector', ['rector.yml', 'rector.yaml']);
}

// 3. Build DI container
$containerFactory = new ContainerFactory();
if ($configFile) {
    return $containerFactory->createWithConfig($configFile);
}

return $containerFactory->create();

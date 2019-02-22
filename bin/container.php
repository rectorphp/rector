<?php declare(strict_types=1);

use Rector\HttpKernel\RectorKernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symplify\PackageBuilder\Configuration\ConfigFileFinder;
use Symplify\PackageBuilder\Configuration\LevelFileFinder;
use Symplify\PackageBuilder\Console\Input\InputDetector;

$configFiles = [];

// Detect configuration from --level
$configFiles[] = (new LevelFileFinder())->detectFromInputAndDirectory(new ArgvInput(), __DIR__ . '/../config/level');

// And from --config or default one
ConfigFileFinder::detectFromInput('rector', new ArgvInput());
$configFiles[] = ConfigFileFinder::provide('rector', ['rector.yml', 'rector.yaml']);

// remove empty values
$configFiles = array_filter($configFiles);

// 3. Build DI container

// to override the configs without clearing cache
$environment = 'prod' . random_int(1, 10000000);
$rectorKernel = new RectorKernel($environment, InputDetector::isDebug());
if ($configFiles) {
    $rectorKernel->setConfigs($configFiles);
}
$rectorKernel->boot();

return $rectorKernel->getContainer();

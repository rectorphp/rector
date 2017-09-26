<?php declare(strict_types=1);

use Rector\Console\Application;
use Rector\DependencyInjection\ContainerFactory;
use Symfony\Component\Console\Input\ArgvInput;
use Symplify\PackageBuilder\Configuration\ConfigFilePathHelper;
use Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;

// Performance boost
gc_disable();

// Require Composer autoload.php
require_once __DIR__ . '/bootstrap.php';


try {
    // 1. Detect configuration
    ConfigFilePathHelper::detectFromInput('rector', new ArgvInput);

    // 2. Build DI container
    $containerFactory = new ContainerFactory;
    $configFile = ConfigFilePathHelper::provide('rector', 'rector.yml');

    if ($configFile) {
        $container = $containerFactory->createWithConfig($configFile);
    } else {
        $container = $containerFactory->create();
    }

    // 3. Run Console Application
    /** @var Application $application */
    $application = $container->get(Application::class);
    $statusCode = $application->run();
    exit($statusCode);

} catch (Throwable $throwable) {
    $symfonyStyle = SymfonyStyleFactory::create();
    $symfonyStyle->error($throwable->getMessage());
    exit(1);
}

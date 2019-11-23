<?php

declare(strict_types=1);

use Rector\Configuration\Configuration;
use Rector\DependencyInjection\RectorContainerFactory;
use Rector\Set\Set;
use Symfony\Component\Console\Input\ArgvInput;
use Symplify\EasyCodingStandard\Exception\Configuration\SetNotFoundException;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use Symplify\SetConfigResolver\ConfigResolver;

$configs = [];
$configResolver = new ConfigResolver();

// Detect configuration from --set
try {
    $input = new ArgvInput();

    $setConfig = $configResolver->resolveSetFromInputAndDirectory($input, Set::SET_DIRECTORY);
    if ($setConfig !== null) {
        $configs[] = $setConfig;
    }
} catch (SetNotFoundException $setNotFoundException) {
    $symfonyStyle = (new SymfonyStyleFactory())->create();
    $symfonyStyle->error($setNotFoundException->getMessage());
    exit(ShellCode::ERROR);
}

// And from --config or default one
$inputOrFallbackConfig = $configResolver->resolveFromInputWithFallback($input, ['rector.yml', 'rector.yaml']);
if ($inputOrFallbackConfig !== null) {
    $configs[] = $inputOrFallbackConfig;
}

// resolve: parameters > sets
$parameterSetsConfigs = $configResolver->resolveFromParameterSetsFromConfigFiles($configs, Set::SET_DIRECTORY);
if ($parameterSetsConfigs !== []) {
    $configs = array_merge($configs, $parameterSetsConfigs);
}

// Build DI container
$rectorContainerFactory = new RectorContainerFactory();
$container = $rectorContainerFactory->createFromConfigs($configs);

/** @var Configuration $configuration */
$configuration = $container->get(Configuration::class);
$configuration->setFirstResolverConfig($configResolver->getFirstResolvedConfig());

return $container;

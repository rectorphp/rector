<?php

declare(strict_types=1);

use Rector\Console\Option\SetOptionResolver;
use Rector\DependencyInjection\RectorContainerFactory;
use Rector\Exception\Configuration\SetNotFoundException;
use Rector\Set\Set;
use Symfony\Component\Console\Input\ArgvInput;
use Symplify\PackageBuilder\Configuration\ConfigFileFinder;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;

$configs = [];

// Detect configuration from --set
try {
    $input = new ArgvInput();
    $setOptionResolver = new SetOptionResolver();
    $configs[] = $setOptionResolver->detectFromInputAndDirectory($input, Set::SET_DIRECTORY);
} catch (SetNotFoundException $setNotFoundException) {
    $symfonyStyle = (new SymfonyStyleFactory())->create();
    $symfonyStyle->error($setNotFoundException->getMessage());
    exit(ShellCode::ERROR);
}

// And from --config or default one
$argInput = new ArgvInput();

// https://github.com/rectorphp/rector/issues/2205
$configOptionValue = ConfigFileFinder::getOptionValue($argInput, ['-c', '--config']);
if ($configOptionValue === '--ansi') {
    // empty --config with xdebug handler
    die('Option "-c" or "--config" cannot be empty. Provide path to config file' . PHP_EOL);
}

ConfigFileFinder::detectFromInput('rector', $argInput);
$configs[] = ConfigFileFinder::provide('rector', ['rector.yml', 'rector.yaml']);

// remove empty values
$configs = array_filter($configs);

// Build DI container
$rectorContainerFactory = new RectorContainerFactory();
return $rectorContainerFactory->createFromConfigs($configs);

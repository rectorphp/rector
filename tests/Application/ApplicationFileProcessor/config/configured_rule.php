<?php

declare(strict_types=1);

use Rector\Core\Tests\Application\ApplicationFileProcessor\Source\TextNonPhpFileProcessor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(TextNonPhpFileProcessor::class);
};

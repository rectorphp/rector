<?php

declare(strict_types=1);

use Rector\Core\Tests\Application\ApplicationFileProcessor\Source\TextFileProcessor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(TextFileProcessor::class);
};

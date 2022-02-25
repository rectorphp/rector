<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector;
use Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation;
use Rector\Renaming\Rector\Name\RenameClassRector;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
    ]);

    $services = $containerConfigurator->services();
    $services->set(RenameClassRector::class)
        ->configure(['DateTime' => 'DateTimeInterface']);
    $services->set(DowngradeAttributeToAnnotationRector::class)
        ->configure([new DowngradeAttributeToAnnotation('Symfony\Component\Routing\Annotation\Route')]);
    $services->set(RemoveUselessVarTagRector::class);
};

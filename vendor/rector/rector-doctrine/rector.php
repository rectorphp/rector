<?php

declare (strict_types=1);
namespace RectorPrefix20210827;

use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);
    $parameters->set(\Rector\Core\Configuration\Option::SKIP, [
        // for tests
        __DIR__ . '/tests/Rector/Class_/TranslationBehaviorRector/Source',
    ]);
    $services = $containerConfigurator->services();
    $services->set(\Rector\Php74\Rector\Property\TypedPropertyRector::class);
    $services->set(\Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector::class);
    $containerConfigurator->import(\Rector\Set\ValueObject\SetList::DEAD_CODE);
};

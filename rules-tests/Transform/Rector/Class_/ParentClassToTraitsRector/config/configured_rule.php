<?php

declare(strict_types=1);

use Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;
use Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;
use Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\SecondTrait;
use Rector\Tests\Transform\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
use Rector\Transform\Rector\Class_\ParentClassToTraitsRector;
use Rector\Transform\ValueObject\ParentClassToTraits;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ParentClassToTraitsRector::class)
        ->configure([
            new ParentClassToTraits(ParentObject::class, [SomeTrait::class]),
            new ParentClassToTraits(AnotherParentObject::class, [SomeTrait::class, SecondTrait::class]),
        ]);
};

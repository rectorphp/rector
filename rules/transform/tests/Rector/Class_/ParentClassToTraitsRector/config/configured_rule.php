<?php

use Rector\Transform\Rector\Class_\ParentClassToTraitsRector;
use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;
use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;
use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SecondTrait;
use Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;
use Rector\Transform\ValueObject\ParentClassToTraits;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ParentClassToTraitsRector::class)
        ->call('configure', [[
            ParentClassToTraitsRector::PARENT_CLASS_TO_TRAITS => ValueObjectInliner::inline([
                new ParentClassToTraits(ParentObject::class, [SomeTrait::class]),
                new ParentClassToTraits(AnotherParentObject::class, [SomeTrait::class, SecondTrait::class]),
            ]),
        ]]);
};

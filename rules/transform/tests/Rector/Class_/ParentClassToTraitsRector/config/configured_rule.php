<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\Class_\ParentClassToTraitsRector::class)->call('configure', [[
        \Rector\Transform\Rector\Class_\ParentClassToTraitsRector::PARENT_CLASS_TO_TRAITS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
            

















            new \Rector\Transform\ValueObject\ParentClassToTraits(
                \Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject::class,
                [\Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait::class]),
            new \Rector\Transform\ValueObject\ParentClassToTraits(
                \Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject::class,
                [
                    \Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait::class,
                    \Rector\Transform\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SecondTrait::class,

                ]),
        ]
        ),
    ]]);
};

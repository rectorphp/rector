<?php

use Rector\Core\ValueObject\Visibility;
use Rector\Visibility\Rector\ClassConst\ChangeConstantVisibilityRector;
use Rector\Visibility\Tests\Rector\ClassConst\ChangeConstantVisibilityRector\Source\ParentObject;
use Rector\Visibility\ValueObject\ChangeConstantVisibility;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ChangeConstantVisibilityRector::class)
        ->call('configure', [[
            ChangeConstantVisibilityRector::CLASS_CONSTANT_VISIBILITY_CHANGES => ValueObjectInliner::inline([
                new ChangeConstantVisibility(ParentObject::class, 'TO_BE_PUBLIC_CONSTANT', Visibility::PUBLIC),
                new ChangeConstantVisibility(
                    ParentObject::class,
                    'TO_BE_PROTECTED_CONSTANT',
                    Visibility::PROTECTED
                ),
                new ChangeConstantVisibility(ParentObject::class, 'TO_BE_PRIVATE_CONSTANT', Visibility::PRIVATE),
                new ChangeConstantVisibility(
                    'Rector\Visibility\Tests\Rector\ClassConst\ChangeConstantVisibilityRector\Fixture\Fixture2',
                    'TO_BE_PRIVATE_CONSTANT',
                    Visibility::PRIVATE
                ),
            ]),
        ]]);
};

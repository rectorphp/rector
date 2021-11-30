<?php

declare(strict_types=1);

use Rector\Core\ValueObject\Visibility;
use Rector\Tests\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector\Source\ParentObject;
use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ChangeMethodVisibilityRector::class)
        ->configure([

            new ChangeMethodVisibility(ParentObject::class, 'toBePublicMethod', Visibility::PUBLIC),
            new ChangeMethodVisibility(ParentObject::class, 'toBeProtectedMethod', Visibility::PROTECTED),
            new ChangeMethodVisibility(ParentObject::class, 'toBePrivateMethod', Visibility::PRIVATE),
            new ChangeMethodVisibility(ParentObject::class, 'toBePublicStaticMethod', Visibility::PUBLIC),

        ]);
};

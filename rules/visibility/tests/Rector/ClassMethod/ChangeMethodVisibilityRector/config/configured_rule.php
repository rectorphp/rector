<?php

use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(ChangeMethodVisibilityRector::class)->call('configure', [[
        ChangeMethodVisibilityRector::METHOD_VISIBILITIES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([

            new \Rector\Visibility\ValueObject\ChangeMethodVisibility(
                \Rector\Visibility\Tests\Rector\ClassMethod\ChangeMethodVisibilityRector\Source\ParentObject::class,
                'toBePublicMethod',
                \Rector\Core\ValueObject\Visibility::PUBLIC
            ),
            new \Rector\Visibility\ValueObject\ChangeMethodVisibility(
                \Rector\Visibility\Tests\Rector\ClassMethod\ChangeMethodVisibilityRector\Source\ParentObject::class,
                'toBeProtectedMethod',
                \Rector\Core\ValueObject\Visibility::PROTECTED
            ),
            new \Rector\Visibility\ValueObject\ChangeMethodVisibility(
                \Rector\Visibility\Tests\Rector\ClassMethod\ChangeMethodVisibilityRector\Source\ParentObject::class,
                'toBePrivateMethod',
                \Rector\Core\ValueObject\Visibility::PRIVATE
            ),
            new \Rector\Visibility\ValueObject\ChangeMethodVisibility(
                \Rector\Visibility\Tests\Rector\ClassMethod\ChangeMethodVisibilityRector\Source\ParentObject::class,
                'toBePublicStaticMethod',
                \Rector\Core\ValueObject\Visibility::PUBLIC
            ),








        ]),
    ]]);
};

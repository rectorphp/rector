<?php

namespace Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ReplaceArrayWithObjectRector\Fixture;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => [
                'PHPUnit\Framework\TestCase' => [
                    '__construct' => [
                        # https://github.com/rectorphp/rector/issues/1024
                        # no type, $dataName
                        2 => '',
                    ],
                ],
                'AnotherClass' => [
                    'yetMethod' => ['Sylius\Component\Core\Storage\CartStorageInterface'],
                ],
            ],
        ]]);
}

?>
-----
<?php

namespace Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ReplaceArrayWithObjectRector\Fixture;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => \Rector\SymfonyPhpConfig\inline_value_objects([
                new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('PHPUnit\Framework\TestCase', '__construct', 2, ''),
                new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('AnotherClass', 'yetMethod', 0, 'Sylius\Component\Core\Storage\CartStorageInterface'),
            ]),
        ]]);
}

?>

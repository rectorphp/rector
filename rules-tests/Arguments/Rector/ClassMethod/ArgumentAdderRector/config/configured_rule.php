<?php

declare(strict_types=1);

use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Rector\Arguments\NodeAnalyzer\ArgumentAddingScope;
use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\Source\SomeClass;
use Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\Source\SomeContainerBuilder;
use Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\Source\SomeParentClient;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $arrayType = new ArrayType(new MixedType(), new MixedType());

    $services->set(ArgumentAdderRector::class)
        ->configure([
            // covers https://github.com/rectorphp/rector/issues/4267
            new ArgumentAdder(
                SomeContainerBuilder::class,
                'sendResetLinkResponse',
                0,
                'request',
                null,
                new ObjectType('Illuminate\Http\Illuminate\Http')
            ),
            new ArgumentAdder(SomeContainerBuilder::class, 'compile', 0, 'isCompiled', false),
            new ArgumentAdder(SomeContainerBuilder::class, 'addCompilerPass', 2, 'priority', 0, new IntegerType()),
            // scoped
            new ArgumentAdder(
                SomeParentClient::class,
                'submit',
                2,
                'serverParameters',
                [],
                $arrayType,
                ArgumentAddingScope::SCOPE_PARENT_CALL
            ),
            new ArgumentAdder(
                SomeParentClient::class,
                'submit',
                2,
                'serverParameters',
                [],
                $arrayType,
                ArgumentAddingScope::SCOPE_CLASS_METHOD
            ),
            new ArgumentAdder(SomeClass::class, 'withoutTypeOrDefaultValue', 0, 'arguments', [], $arrayType),
        ]);
};

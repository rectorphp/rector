<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver;

use Iterator;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\Abc;
use Rector\StaticTypeMapper\TypeFactory\TypeFactoryStaticHelper;

/**
 * @see \Rector\NodeTypeResolver\NodeTypeResolver\PropertyFetchTypeResolver
 */
final class Php80Test extends AbstractNodeTypeResolverTest
{
    /**
     * @requires PHP 8.0
     * @dataProvider providePhp80Data()
     */
    public function testPhp80(string $file, int $nodePosition, Type $expectedType): void
    {
        $propertyFetchNodes = $this->getNodesForFileOfType($file, PropertyFetch::class);

        $resolvedType = $this->nodeTypeResolver->resolve($propertyFetchNodes[$nodePosition]);

        $expectedTypeClass = get_class($expectedType);
        $this->assertInstanceOf($expectedTypeClass, $resolvedType);

        $expectedTypeAsString = $this->getStringFromType($expectedType);
        $resolvedTypeAsString = $this->getStringFromType($resolvedType);

        $this->assertEquals($expectedTypeAsString, $resolvedTypeAsString);
    }

    public function providePhp80Data(): Iterator
    {
        foreach ([
            __DIR__ . '/Source/nativePropertyFetchOnTypedVarPhp80.php',
            __DIR__ . '/Source/nativePropertyFetchOnVarInScopePhp80.php',
        ] as $file) {
            yield [$file, 0, new MixedType()];
            yield [$file, 1, TypeFactoryStaticHelper::createUnionObjectType([Abc::class, new StringType()])];
        }
    }

    private function getStringFromType(Type $type): string
    {
        return $type->describe(VerbosityLevel::precise());
    }
}

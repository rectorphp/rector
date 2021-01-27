<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver;

use Iterator;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source\Abc;
use Rector\StaticTypeMapper\TypeFactory\TypeFactoryStaticHelper;

/**
 * @see \Rector\NodeTypeResolver\NodeTypeResolver\PropertyFetchTypeResolver
 */
final class Php80Test extends AbstractPropertyFetchTypeResolverTest
{
    /**
     * @requires PHP 8.0
     * @dataProvider provideData()
     */
    public function test(string $file, int $nodePosition, Type $expectedType): void
    {
        $propertyFetchNodes = $this->getNodesForFileOfType($file, PropertyFetch::class);

        $resolvedType = $this->nodeTypeResolver->resolve($propertyFetchNodes[$nodePosition]);

        $expectedTypeClass = get_class($expectedType);
        $this->assertInstanceOf($expectedTypeClass, $resolvedType);

        $expectedTypeAsString = $this->getStringFromType($expectedType);
        $resolvedTypeAsString = $this->getStringFromType($resolvedType);

        $this->assertSame($expectedTypeAsString, $resolvedTypeAsString);
    }

    public function provideData(): Iterator
    {
        foreach ([
            __DIR__ . '/Source/nativePropertyFetchOnTypedVarPhp80.php',
            __DIR__ . '/Source/nativePropertyFetchOnVarInScopePhp80.php',
        ] as $file) {
            yield [$file, 0, new MixedType()];
            yield [$file, 1, TypeFactoryStaticHelper::createUnionObjectType([Abc::class, new StringType()])];
        }
    }
}

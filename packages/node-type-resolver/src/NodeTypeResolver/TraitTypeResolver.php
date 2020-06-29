<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use ReflectionClass;

/**
 * @see \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\TraitTypeResolver\TraitTypeResolverTest
 */
final class TraitTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Trait_::class];
    }

    /**
     * @param Trait_ $traitNode
     */
    public function resolve(Node $traitNode): Type
    {
        $reflectionClass = new ReflectionClass((string) $traitNode->namespacedName);

        $types = [];
        $types[] = new ObjectType($reflectionClass->getName());

        foreach ($reflectionClass->getTraits() as $usedTraitReflection) {
            $types[] = new ObjectType($usedTraitReflection->getName());
        }

        if (count($types) === 1) {
            return $types[0];
        }

        if (count($types) > 1) {
            return new UnionType($types);
        }

        return new MixedType();
    }
}

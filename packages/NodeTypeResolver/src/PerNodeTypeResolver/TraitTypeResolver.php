<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use ReflectionClass;

/**
 * @see \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\TraitTypeResolver\TraitTypeResolverTest
 */
final class TraitTypeResolver implements PerNodeTypeResolverInterface
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
        $traitReflection = new ReflectionClass((string) $traitNode->namespacedName);

        $types = [];
        $types[] = new ObjectType($traitReflection->getName());

        foreach ($traitReflection->getTraits() as $usedTraitReflection) {
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

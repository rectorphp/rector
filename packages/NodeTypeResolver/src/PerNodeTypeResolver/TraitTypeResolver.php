<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Trait_;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Roave\BetterReflection\Reflection\ReflectionClass;

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
     * @return string[]
     */
    public function resolve(Node $traitNode): array
    {
        $traitReflection = ReflectionClass::createFromName((string) $traitNode->namespacedName);

        $types = [];
        $types[] = $traitReflection->getName();

        foreach ($traitReflection->getTraits() as $usedTraitReflection) {
            $types[] = $usedTraitReflection->getName();
        }

        return $types;
    }
}

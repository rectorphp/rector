<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

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
        /** @var Scope $traitNodeScope */
        $traitNodeScope = $traitNode->getAttribute(Attribute::SCOPE);

        /** @var ClassReflection $classReflection */
        $classReflection = $traitNodeScope->getClassReflection();

        dump($classReflection);
        die;

        $types[] = $this->resolveNameNode($traitNode);
        return array_merge($types, $this->resolveUsedTraitTypes($traitNode));
    }
}

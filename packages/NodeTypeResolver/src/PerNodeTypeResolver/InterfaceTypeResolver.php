<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class InterfaceTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Interface_::class];
    }

    /**
     * @param Interface_ $interfaceNode
     * @return string[]
     */
    public function resolve(Node $interfaceNode): array
    {
        /** @var Scope $interfaceNodeScope */
        $interfaceNodeScope = $interfaceNode->getAttribute(Attribute::SCOPE);

        /** @var ClassReflection $classReflection */
        $classReflection = $interfaceNodeScope->getClassReflection();

        $types = [];
        $types[] = $classReflection->getName();

        // interfaces
        foreach ($classReflection->getInterfaces() as $classReflection) {
            $types[] = $classReflection->getName();
        }

        return $types;
    }
}

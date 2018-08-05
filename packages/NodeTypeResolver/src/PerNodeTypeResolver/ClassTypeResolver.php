<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class ClassTypeResolver extends AbstractClassLikeTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $classNode
     * @return string[]
     */
    public function resolve(Node $classNode): array
    {
        /** @var Scope $classNodeScope */
        $classNodeScope = $classNode->getAttribute(Attribute::SCOPE);

        /** @var ClassReflection $classReflection */
        $classReflection = $classNodeScope->getClassReflection();

        $types = [];
        $types[] = $classReflection->getName();
        $types = array_merge($types, $classReflection->getParentClassesNames());
        foreach ($classReflection->getInterfaces() as $classReflection) {
            $types[] = $classReflection->getName();
        }

        return $types;
    }
}

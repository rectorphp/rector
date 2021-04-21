<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ClassLikeWithTraitsClassMethodResolver
{
    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    public function __construct(NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * @return ClassMethod[]
     */
    public function resolve(Class_ $class): array
    {
        $classMethods = $class->getMethods();

        $scope = $class->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return $classMethods;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return $classMethods;
        }

        foreach ($classReflection->getTraits() as $traitClassReflection) {
            $trait = $this->nodeRepository->findTrait($traitClassReflection->getName());
            if ($trait instanceof Trait_) {
                $classMethods = array_merge($classMethods, $trait->getMethods());
            }
        }

        return $classMethods;
    }
}

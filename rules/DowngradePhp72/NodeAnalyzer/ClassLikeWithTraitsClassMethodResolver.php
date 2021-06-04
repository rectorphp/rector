<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeCollector\NodeCollector\NodeRepository;
final class ClassLikeWithTraitsClassMethodResolver
{
    /**
     * @var \Rector\NodeCollector\NodeCollector\NodeRepository
     */
    private $nodeRepository;
    public function __construct(\Rector\NodeCollector\NodeCollector\NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }
    /**
     * @param ClassReflection[] $ancestors
     * @return ClassMethod[]
     */
    public function resolve(array $ancestors) : array
    {
        $classMethods = [];
        foreach ($ancestors as $ancestor) {
            $ancestorClassLike = $this->nodeRepository->findClassLike($ancestor->getName());
            if (!$ancestorClassLike instanceof \PhpParser\Node\Stmt\ClassLike) {
                continue;
            }
            $classMethods = \array_merge($classMethods, $ancestorClassLike->getMethods());
        }
        return $classMethods;
    }
}

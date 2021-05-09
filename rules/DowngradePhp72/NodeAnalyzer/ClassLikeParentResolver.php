<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeCollector\NodeCollector\NodeRepository;
final class ClassLikeParentResolver
{
    /**
     * @var NodeRepository
     */
    private $nodeRepository;
    public function __construct(\Rector\NodeCollector\NodeCollector\NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }
    /**
     * @return array<Class_|Interface_>
     */
    public function resolveFromClassReflection(\PHPStan\Reflection\ClassReflection $classReflection) : array
    {
        $parentClassLikes = [];
        foreach ($classReflection->getAncestors() as $ancestorClassReflectoin) {
            $parentClass = $this->nodeRepository->findClass($ancestorClassReflectoin->getName());
            if ($parentClass instanceof \PhpParser\Node\Stmt\Class_) {
                $parentClassLikes[] = $parentClass;
            }
            $parentInterface = $this->nodeRepository->findInterface($ancestorClassReflectoin->getName());
            if ($parentInterface instanceof \PhpParser\Node\Stmt\Interface_) {
                $parentClassLikes[] = $parentInterface;
            }
        }
        return $parentClassLikes;
    }
}

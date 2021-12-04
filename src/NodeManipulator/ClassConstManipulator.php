<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
final class ClassConstManipulator
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
    }
    public function hasClassConstFetch(\PhpParser\Node\Stmt\ClassConst $classConst, \PHPStan\Reflection\ClassReflection $classReflection) : bool
    {
        $class = $this->betterNodeFinder->findParentType($classConst, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            $ancestorClass = $this->astResolver->resolveClassFromClassReflection($ancestorClassReflection, $ancestorClassReflection->getName());
            if (!$ancestorClass instanceof \PhpParser\Node\Stmt\ClassLike) {
                continue;
            }
            // has in class?
            $isClassConstFetchFound = (bool) $this->betterNodeFinder->find($ancestorClass, function (\PhpParser\Node $node) use($classConst) : bool {
                // property + static fetch
                if (!$node instanceof \PhpParser\Node\Expr\ClassConstFetch) {
                    return \false;
                }
                return $this->isNameMatch($node, $classConst);
            });
            if ($isClassConstFetchFound) {
                return \true;
            }
        }
        return \false;
    }
    private function isNameMatch(\PhpParser\Node\Expr\ClassConstFetch $classConstFetch, \PhpParser\Node\Stmt\ClassConst $classConst) : bool
    {
        $selfConstantName = 'self::' . $this->nodeNameResolver->getName($classConst);
        $staticConstantName = 'static::' . $this->nodeNameResolver->getName($classConst);
        return $this->nodeNameResolver->isNames($classConstFetch, [$selfConstantName, $staticConstantName]);
    }
}

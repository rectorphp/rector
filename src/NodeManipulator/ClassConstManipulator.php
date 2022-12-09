<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Enum_;
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
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, AstResolver $astResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
    }
    public function hasClassConstFetch(ClassConst $classConst, ClassReflection $classReflection) : bool
    {
        $class = $this->betterNodeFinder->findParentByTypes($classConst, [Class_::class, Enum_::class]);
        if (!$class instanceof ClassLike) {
            return \true;
        }
        $className = (string) $this->nodeNameResolver->getName($class);
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            $ancestorClass = $this->astResolver->resolveClassFromClassReflection($ancestorClassReflection);
            if (!$ancestorClass instanceof ClassLike) {
                continue;
            }
            // has in class?
            $isClassConstFetchFound = (bool) $this->betterNodeFinder->find($ancestorClass, function (Node $node) use($classConst, $className) : bool {
                // property + static fetch
                if (!$node instanceof ClassConstFetch) {
                    return \false;
                }
                return $this->isNameMatch($node, $classConst, $className);
            });
            if ($isClassConstFetchFound) {
                return \true;
            }
        }
        return \false;
    }
    private function isNameMatch(ClassConstFetch $classConstFetch, ClassConst $classConst, string $className) : bool
    {
        $classConstName = (string) $this->nodeNameResolver->getName($classConst);
        $selfConstantName = 'self::' . $classConstName;
        $staticConstantName = 'static::' . $classConstName;
        $classNameConstantName = $className . '::' . $classConstName;
        return $this->nodeNameResolver->isNames($classConstFetch, [$selfConstantName, $staticConstantName, $classNameConstantName]);
    }
}

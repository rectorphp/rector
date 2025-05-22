<?php

declare (strict_types=1);
namespace Rector\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\AstResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
final class ClassConstManipulator
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, AstResolver $astResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function hasClassConstFetch(ClassConst $classConst, ClassReflection $classReflection) : bool
    {
        if (!$classReflection->isClass() && !$classReflection->isEnum()) {
            return \true;
        }
        $className = $classReflection->getName();
        $objectType = new ObjectType($className);
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            $ancestorClass = $this->astResolver->resolveClassFromClassReflection($ancestorClassReflection);
            if (!$ancestorClass instanceof ClassLike) {
                continue;
            }
            // has in class?
            $isClassConstFetchFound = (bool) $this->betterNodeFinder->findFirst($ancestorClass, function (Node $node) use($classConst, $className, $objectType) : bool {
                // property + static fetch
                if (!$node instanceof ClassConstFetch) {
                    return \false;
                }
                return $this->isNameMatch($node, $classConst, $className, $objectType);
            });
            if ($isClassConstFetchFound) {
                return \true;
            }
        }
        return \false;
    }
    private function isNameMatch(ClassConstFetch $classConstFetch, ClassConst $classConst, string $className, ObjectType $objectType) : bool
    {
        $classConstName = (string) $this->nodeNameResolver->getName($classConst);
        $selfConstantName = 'self::' . $classConstName;
        $staticConstantName = 'static::' . $classConstName;
        $classNameConstantName = $className . '::' . $classConstName;
        if ($this->nodeNameResolver->isNames($classConstFetch, [$selfConstantName, $staticConstantName, $classNameConstantName])) {
            return \true;
        }
        if ($this->nodeTypeResolver->isObjectType($classConstFetch->class, $objectType)) {
            if (!$classConstFetch->name instanceof Identifier) {
                return \true;
            }
            return $this->nodeNameResolver->isName($classConstFetch->name, $classConstName);
        }
        return \false;
    }
}

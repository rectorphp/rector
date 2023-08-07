<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
/**
 * @internal called from AstResolver
 */
final class ClassLikeAstResolver
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
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return \PhpParser\Node\Stmt\Trait_|\PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Stmt\Enum_|null
     */
    public function resolveClassFromClassReflection(ClassReflection $classReflection, \Rector\Core\PhpParser\AstResolver $astResolver)
    {
        if ($classReflection->isBuiltin()) {
            return null;
        }
        $fileName = $classReflection->getFileName();
        // probably internal class
        if ($fileName === null) {
            return null;
        }
        $stmts = $astResolver->parseFileNameToDecoratedNodes($fileName);
        if ($stmts === []) {
            return null;
        }
        $className = $classReflection->getName();
        /** @var Class_|Trait_|Interface_|Enum_|null $classLike */
        $classLike = $this->betterNodeFinder->findFirst($stmts, function (Node $node) use($className) : bool {
            if (!$node instanceof ClassLike) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, $className);
        });
        return $classLike;
    }
}

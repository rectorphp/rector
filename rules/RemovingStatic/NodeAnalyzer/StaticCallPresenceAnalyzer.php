<?php

declare (strict_types=1);
namespace Rector\RemovingStatic\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class StaticCallPresenceAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function hasMethodStaticCallOnType(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Type\ObjectType $objectType) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (\PhpParser\Node $node) use($objectType) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\StaticCall) {
                return \false;
            }
            return $this->nodeTypeResolver->isObjectType($node->class, $objectType);
        });
    }
    public function hasClassAnyMethodWithStaticCallOnType(\PhpParser\Node\Stmt\Class_ $class, \PHPStan\Type\ObjectType $objectType) : bool
    {
        foreach ($class->getMethods() as $classMethod) {
            // handled else where
            if ((string) $classMethod->name === \Rector\Core\ValueObject\MethodName::CONSTRUCT) {
                continue;
            }
            if ($this->hasMethodStaticCallOnType($classMethod, $objectType)) {
                return \true;
            }
        }
        return \false;
    }
}

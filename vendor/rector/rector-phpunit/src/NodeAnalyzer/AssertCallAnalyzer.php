<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class AssertCallAnalyzer
{
    /**
     * @var int
     */
    private const MAX_NESTED_METHOD_CALL_LEVEL = 3;
    /**
     * @var array<string, bool>
     */
    private $containsAssertCallByClassMethod = [];
    /**
     * This should prevent segfaults while going too deep into to parsed code. Without it, it might end-up with segfault
     * @var int
     */
    private $classMethodNestingLevel = 0;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \PhpParser\PrettyPrinter\Standard
     */
    private $printerStandard;
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
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\Core\PhpParser\AstResolver $astResolver, \PhpParser\PrettyPrinter\Standard $printerStandard, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->astResolver = $astResolver;
        $this->printerStandard = $printerStandard;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function resetNesting() : void
    {
        $this->classMethodNestingLevel = 0;
    }
    public function containsAssertCall(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        ++$this->classMethodNestingLevel;
        // probably no assert method in the end
        if ($this->classMethodNestingLevel > self::MAX_NESTED_METHOD_CALL_LEVEL) {
            return \false;
        }
        $cacheHash = \md5($this->printerStandard->prettyPrint([$classMethod]));
        if (isset($this->containsAssertCallByClassMethod[$cacheHash])) {
            return $this->containsAssertCallByClassMethod[$cacheHash];
        }
        // A. try "->assert" shallow search first for performance
        $hasDirectAssertCall = $this->hasDirectAssertCall($classMethod);
        if ($hasDirectAssertCall) {
            $this->containsAssertCallByClassMethod[$cacheHash] = $hasDirectAssertCall;
            return \true;
        }
        // B. look for nested calls
        $hasNestedAssertCall = $this->hasNestedAssertCall($classMethod);
        $this->containsAssertCallByClassMethod[$cacheHash] = $hasNestedAssertCall;
        return $hasNestedAssertCall;
    }
    private function hasDirectAssertCall(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (\PhpParser\Node $node) : bool {
            if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
                $type = $this->nodeTypeResolver->getType($node->var);
                if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType && \in_array($type->getClassName(), ['PHPUnit\\Framework\\MockObject\\MockBuilder', 'Prophecy\\Prophet'], \true)) {
                    return \true;
                }
                return $this->isAssertMethodName($node);
            }
            if ($node instanceof \PhpParser\Node\Expr\StaticCall) {
                return $this->isAssertMethodName($node);
            }
            return \false;
        });
    }
    private function hasNestedAssertCall(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $currentClassMethod = $classMethod;
        // over and over the same method :/
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (\PhpParser\Node $node) use($currentClassMethod) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall && !$node instanceof \PhpParser\Node\Expr\StaticCall) {
                return \false;
            }
            $classMethod = $this->resolveClassMethodFromCall($node);
            // skip circular self calls
            if ($currentClassMethod === $classMethod) {
                return \false;
            }
            if ($classMethod !== null) {
                return $this->containsAssertCall($classMethod);
            }
            return \false;
        });
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    private function resolveClassMethodFromCall($call) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if ($call instanceof \PhpParser\Node\Expr\MethodCall) {
            $objectType = $this->nodeTypeResolver->getType($call->var);
        } else {
            // StaticCall
            $objectType = $this->nodeTypeResolver->getType($call->class);
        }
        if (!$objectType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $methodName = $this->nodeNameResolver->getName($call->name);
        if ($methodName === null) {
            return null;
        }
        return $this->astResolver->resolveClassMethod($objectType->getClassName(), $methodName);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    private function isAssertMethodName($call) : bool
    {
        return $this->nodeNameResolver->isNames($call->name, [
            // phpunit
            '*assert',
            'assert*',
            'expectException*',
            'setExpectedException*',
            'expectOutput*',
            'should*',
        ]);
    }
}

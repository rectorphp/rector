<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\AstResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class AssertCallAnalyzer
{
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private BetterStandardPrinter $betterStandardPrinter;
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
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @var int
     */
    private const MAX_NESTED_METHOD_CALL_LEVEL = 5;
    /**
     * @var string[]
     */
    private const ASSERT_METHOD_NAME_PREFIXES = ['expectNotToPerformAssertions', 'assert', 'expectException', 'setExpectedException', 'expectOutput', 'should'];
    /**
     * @var array<string, bool>
     */
    private array $containsAssertCallByClassMethod = [];
    /**
     * This should prevent segfaults while going too deep into to parsed code. Without it, it might end-up with segfault
     */
    private int $classMethodNestingLevel = 0;
    public function __construct(AstResolver $astResolver, BetterStandardPrinter $betterStandardPrinter, BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->astResolver = $astResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function resetNesting() : void
    {
        $this->classMethodNestingLevel = 0;
    }
    public function containsAssertCall(ClassMethod $classMethod) : bool
    {
        ++$this->classMethodNestingLevel;
        // probably no assert method in the end
        if ($this->classMethodNestingLevel > self::MAX_NESTED_METHOD_CALL_LEVEL) {
            return \false;
        }
        $cacheHash = \md5($this->betterStandardPrinter->prettyPrint([$classMethod]));
        if (isset($this->containsAssertCallByClassMethod[$cacheHash])) {
            return $this->containsAssertCallByClassMethod[$cacheHash];
        }
        // A. try "->assert" shallow search first for performance
        $hasDirectAssertOrMockCall = $this->hasDirectAssertOrMockCall($classMethod);
        if ($hasDirectAssertOrMockCall) {
            $this->containsAssertCallByClassMethod[$cacheHash] = $hasDirectAssertOrMockCall;
            return \true;
        }
        // B. look for nested calls
        $hasNestedAssertOrMockCall = $this->hasNestedAssertCall($classMethod);
        $this->containsAssertCallByClassMethod[$cacheHash] = $hasNestedAssertOrMockCall;
        return $hasNestedAssertOrMockCall;
    }
    private function hasDirectAssertOrMockCall(ClassMethod $classMethod) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node) : bool {
            if ($node instanceof MethodCall) {
                // probably a mock
                if ($this->nodeNameResolver->isName($node->name, 'expects')) {
                    return \true;
                }
                $type = $this->nodeTypeResolver->getType($node->var);
                if ($type instanceof FullyQualifiedObjectType && \in_array($type->getClassName(), ['PHPUnit\\Framework\\MockObject\\MockBuilder', 'Prophecy\\Prophet'], \true)) {
                    return \true;
                }
                return $this->isAssertMethodName($node);
            }
            if ($node instanceof StaticCall) {
                return $this->isAssertMethodName($node);
            }
            return \false;
        });
    }
    private function hasNestedAssertCall(ClassMethod $classMethod) : bool
    {
        $currentClassMethod = $classMethod;
        // over and over the same method :/
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node) use($currentClassMethod) : bool {
            if (!$node instanceof MethodCall && !$node instanceof StaticCall) {
                return \false;
            }
            // is a mock call
            if ($this->nodeNameResolver->isName($node->name, 'expects')) {
                return \true;
            }
            $classMethod = $this->resolveClassMethodFromCall($node);
            // skip circular self calls
            if ($currentClassMethod === $classMethod) {
                return \false;
            }
            if ($classMethod instanceof ClassMethod) {
                return $this->containsAssertCall($classMethod);
            }
            return \false;
        });
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $call
     */
    private function resolveClassMethodFromCall($call) : ?ClassMethod
    {
        if ($call instanceof MethodCall) {
            $objectType = $this->nodeTypeResolver->getType($call->var);
        } else {
            // StaticCall
            $objectType = $this->nodeTypeResolver->getType($call->class);
        }
        if (!$objectType instanceof TypeWithClassName) {
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
        if (!$call->name instanceof Identifier) {
            return \false;
        }
        $callName = $this->nodeNameResolver->getName($call->name);
        if (!\is_string($callName)) {
            return \false;
        }
        foreach (self::ASSERT_METHOD_NAME_PREFIXES as $assertMethodNamePrefix) {
            if (\strncmp($callName, $assertMethodNamePrefix, \strlen($assertMethodNamePrefix)) === 0) {
                return \true;
            }
        }
        return \false;
    }
}

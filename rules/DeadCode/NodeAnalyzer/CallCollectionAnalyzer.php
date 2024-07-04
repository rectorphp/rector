<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeWithClassName;
use Rector\Enum\ObjectReference;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class CallCollectionAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param StaticCall[]|MethodCall[]|NullsafeMethodCall[] $calls
     */
    public function isExists(array $calls, string $classMethodName, string $className) : bool
    {
        foreach ($calls as $call) {
            $callerRoot = $call instanceof StaticCall ? $call->class : $call->var;
            $callerType = $this->nodeTypeResolver->getType($callerRoot);
            if (!$callerType instanceof TypeWithClassName) {
                // handle fluent by $this->bar()->baz()->qux()
                // that methods don't have return type
                if ($callerType instanceof MixedType && !$callerType->isExplicitMixed()) {
                    $cloneCallerRoot = clone $callerRoot;
                    $isFluent = \false;
                    // init
                    $methodCallNames = [];
                    // first append
                    $methodCallNames[] = (string) $this->nodeNameResolver->getName($call->name);
                    while ($cloneCallerRoot instanceof MethodCall) {
                        $methodCallNames[] = (string) $this->nodeNameResolver->getName($cloneCallerRoot->name);
                        if ($cloneCallerRoot->var instanceof Variable && $cloneCallerRoot->var->name === 'this') {
                            $isFluent = \true;
                            break;
                        }
                        $cloneCallerRoot = $cloneCallerRoot->var;
                    }
                    if ($isFluent && \in_array($classMethodName, $methodCallNames, \true)) {
                        return \true;
                    }
                }
                continue;
            }
            if ($this->isSelfStatic($call) && $this->shouldSkip($call, $classMethodName)) {
                return \true;
            }
            if ($callerType->getClassName() !== $className) {
                continue;
            }
            if ($this->shouldSkip($call, $classMethodName)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\NullsafeMethodCall $call
     */
    private function isSelfStatic($call) : bool
    {
        return $call instanceof StaticCall && $call->class instanceof Name && \in_array($call->class->toString(), [ObjectReference::SELF, ObjectReference::STATIC], \true);
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\NullsafeMethodCall $call
     */
    private function shouldSkip($call, string $classMethodName) : bool
    {
        if (!$call->name instanceof Identifier) {
            return \true;
        }
        // the method is used
        return $this->nodeNameResolver->isName($call->name, $classMethodName);
    }
}

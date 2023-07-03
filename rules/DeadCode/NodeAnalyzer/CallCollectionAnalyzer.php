<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Enum\ObjectReference;
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
     * @param StaticCall[]|MethodCall[] $calls
     */
    public function isExists(array $calls, string $classMethodName, string $className) : bool
    {
        foreach ($calls as $call) {
            $callerRoot = $call instanceof StaticCall ? $call->class : $call->var;
            $callerType = $this->nodeTypeResolver->getType($callerRoot);
            if (!$callerType instanceof TypeWithClassName) {
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
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    private function isSelfStatic($call) : bool
    {
        return $call instanceof StaticCall && $call->class instanceof Name && \in_array($call->class->toString(), [ObjectReference::SELF, ObjectReference::STATIC], \true);
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $call
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

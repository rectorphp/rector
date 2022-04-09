<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\TypeWithClassName;
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
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param StaticCall[]|MethodCall[] $calls
     */
    public function isExists(array $calls, string $classMethodName, ?string $className) : bool
    {
        foreach ($calls as $call) {
            $callerRoot = $call instanceof \PhpParser\Node\Expr\StaticCall ? $call->class : $call->var;
            $callerType = $this->nodeTypeResolver->getType($callerRoot);
            if (!$callerType instanceof \PHPStan\Type\TypeWithClassName) {
                continue;
            }
            if ($callerType->getClassName() !== $className) {
                continue;
            }
            if (!$call->name instanceof \PhpParser\Node\Identifier) {
                return \true;
            }
            // the method is used
            if ($this->nodeNameResolver->isName($call->name, $classMethodName)) {
                return \true;
            }
        }
        return \false;
    }
}

<?php

declare (strict_types=1);
namespace Rector\Unambiguous\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class FluentMethodCallsCollector
{
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @return MethodCall[]
     */
    public function resolve(MethodCall $firstMethodCall): array
    {
        // must be nested method call, so we avoid only single one
        if (!$firstMethodCall->var instanceof MethodCall) {
            return [];
        }
        /** @var MethodCall[] $methodCalls */
        $methodCalls = [];
        $currentMethodCall = $firstMethodCall;
        $classNameObjectType = null;
        while ($currentMethodCall instanceof MethodCall) {
            if ($currentMethodCall->isFirstClassCallable()) {
                return [];
            }
            // must be exactly one argument
            if (count($currentMethodCall->getArgs()) !== 1) {
                return [];
            }
            $objectType = $this->nodeTypeResolver->getType($currentMethodCall->var);
            if (!$objectType instanceof ObjectType) {
                return [];
            }
            if ($classNameObjectType === null) {
                $classNameObjectType = $objectType->getClassName();
            } elseif ($classNameObjectType !== $objectType->getClassName()) {
                return [];
            }
            $methodCalls[] = $currentMethodCall;
            $currentMethodCall = $currentMethodCall->var;
        }
        return $methodCalls;
    }
}

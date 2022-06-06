<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DogFood\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\Value\ValueResolver;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
final class ContainerConfiguratorCallAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(ValueResolver $valueResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->valueResolver = $valueResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isMethodCallWithServicesSetConfiguredRectorRule(MethodCall $methodCall) : bool
    {
        return $this->nodeNameResolver->isName($methodCall->name, 'configure');
    }
    public function isMethodCallWithServicesSetRectorRule(MethodCall $methodCall) : bool
    {
        if (!$this->isMethodCallNamed($methodCall, 'services', 'set')) {
            return \false;
        }
        $firstArg = $methodCall->getArgs()[0];
        $serviceClass = $this->valueResolver->getValue($firstArg->value);
        if (!\is_string($serviceClass)) {
            return \false;
        }
        return \is_a($serviceClass, RectorInterface::class, \true);
    }
    public function isMethodCallNamed(Expr $expr, string $variableName, string $methodName) : bool
    {
        if (!$expr instanceof MethodCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($expr->var, $variableName)) {
            return \false;
        }
        return $this->nodeNameResolver->isName($expr->name, $methodName);
    }
}

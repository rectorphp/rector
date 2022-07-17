<?php

declare (strict_types=1);
namespace Rector\DogFood\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
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
    public function isMethodCallNamed(MethodCall $methodCall, string $variableName, string $methodName) : bool
    {
        if (!$this->nodeNameResolver->isName($methodCall->var, $variableName)) {
            return \false;
        }
        return $this->nodeNameResolver->isName($methodCall->name, $methodName);
    }
}

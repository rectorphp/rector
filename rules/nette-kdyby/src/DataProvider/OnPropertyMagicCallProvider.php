<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\DataProvider;

use Nette\Application\UI\Control;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Rector\NodeCollector\NodeCollector\ParsedFunctionLikeNodeCollector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class OnPropertyMagicCallProvider
{
    /**
     * @var ParsedFunctionLikeNodeCollector
     */
    private $parsedFunctionLikeNodeCollector;

    /**
     * @var MethodCall[]
     */
    private $onPropertyMagicCalls = [];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        ParsedFunctionLikeNodeCollector $parsedFunctionLikeNodeCollector,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->parsedFunctionLikeNodeCollector = $parsedFunctionLikeNodeCollector;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @return MethodCall[]
     */
    public function provide(): array
    {
        if ($this->onPropertyMagicCalls !== [] && ! StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return $this->onPropertyMagicCalls;
        }

        foreach ($this->parsedFunctionLikeNodeCollector->getMethodsCalls() as $methodCall) {
            if (! $this->isLocalOnPropertyCall($methodCall)) {
                continue;
            }

            $this->onPropertyMagicCalls[] = $methodCall;
        }

        return $this->onPropertyMagicCalls;
    }

    /**
     * Detects method call on, e.g:
     * public $onSomeProperty;
     */
    private function isLocalOnPropertyCall(MethodCall $methodCall): bool
    {
        if ($methodCall->var instanceof StaticCall) {
            return false;
        }

        if ($methodCall->var instanceof MethodCall) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($methodCall->var, 'this')) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($methodCall->name, 'on*')) {
            return false;
        }

        $methodName = $this->nodeNameResolver->getName($methodCall->name);
        if ($methodName === null) {
            return false;
        }

        $className = $methodCall->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return false;
        }

        // control event, inner only
        if (is_a($className, Control::class, true)) {
            return false;
        }

        if (method_exists($className, $methodName)) {
            return false;
        }

        return property_exists($className, $methodName);
    }
}

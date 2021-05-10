<?php

declare(strict_types=1);

namespace Rector\Defluent\ValueObject;

use PhpParser\Node\Expr\MethodCall;

final class FluentMethodCalls
{
    /**
     * @param MethodCall[] $fluentMethodCalls
     */
    public function __construct(
        private MethodCall $rootMethodCall,
        private array $fluentMethodCalls,
        private MethodCall $lastMethodCall
    ) {
    }

    public function getRootMethodCall(): MethodCall
    {
        return $this->rootMethodCall;
    }

    /**
     * @return MethodCall[]
     */
    public function getFluentMethodCalls(): array
    {
        return $this->fluentMethodCalls;
    }

    public function getLastMethodCall(): MethodCall
    {
        return $this->lastMethodCall;
    }
}

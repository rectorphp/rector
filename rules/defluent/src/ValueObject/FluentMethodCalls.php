<?php

declare(strict_types=1);

namespace Rector\Defluent\ValueObject;

use PhpParser\Node\Expr\MethodCall;

final class FluentMethodCalls
{
    /**
     * @var MethodCall[]
     */
    private $fluentMethodCalls = [];

    /**
     * @var MethodCall
     */
    private $rootMethodCall;

    /**
     * @var MethodCall
     */
    private $lastMethodCall;

    /**
     * @param MethodCall[] $fluentMethodCalls
     */
    public function __construct(MethodCall $rootMethodCall, array $fluentMethodCalls, MethodCall $lastMethodCall)
    {
        $this->rootMethodCall = $rootMethodCall;
        $this->fluentMethodCalls = $fluentMethodCalls;
        $this->lastMethodCall = $lastMethodCall;
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

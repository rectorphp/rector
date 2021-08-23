<?php

declare (strict_types=1);
namespace Rector\Defluent\ValueObject;

use PhpParser\Node\Expr\MethodCall;
final class FluentMethodCalls
{
    /**
     * @var \PhpParser\Node\Expr\MethodCall
     */
    private $rootMethodCall;
    /**
     * @var \PhpParser\Node\Expr\MethodCall[]
     */
    private $fluentMethodCalls;
    /**
     * @var \PhpParser\Node\Expr\MethodCall
     */
    private $lastMethodCall;
    /**
     * @param MethodCall[] $fluentMethodCalls
     */
    public function __construct(\PhpParser\Node\Expr\MethodCall $rootMethodCall, array $fluentMethodCalls, \PhpParser\Node\Expr\MethodCall $lastMethodCall)
    {
        $this->rootMethodCall = $rootMethodCall;
        $this->fluentMethodCalls = $fluentMethodCalls;
        $this->lastMethodCall = $lastMethodCall;
    }
    public function getRootMethodCall() : \PhpParser\Node\Expr\MethodCall
    {
        return $this->rootMethodCall;
    }
    /**
     * @return MethodCall[]
     */
    public function getFluentMethodCalls() : array
    {
        return $this->fluentMethodCalls;
    }
    public function getLastMethodCall() : \PhpParser\Node\Expr\MethodCall
    {
        return $this->lastMethodCall;
    }
}

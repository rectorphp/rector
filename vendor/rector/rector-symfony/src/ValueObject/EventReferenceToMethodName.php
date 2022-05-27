<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject;

use PhpParser\Node\Expr\ClassConstFetch;
use Rector\Symfony\Contract\EventReferenceToMethodNameInterface;
final class EventReferenceToMethodName implements \Rector\Symfony\Contract\EventReferenceToMethodNameInterface
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\ClassConstFetch
     */
    private $classConstFetch;
    /**
     * @readonly
     * @var string
     */
    private $methodName;
    public function __construct(\PhpParser\Node\Expr\ClassConstFetch $classConstFetch, string $methodName)
    {
        $this->classConstFetch = $classConstFetch;
        $this->methodName = $methodName;
    }
    public function getClassConstFetch() : \PhpParser\Node\Expr\ClassConstFetch
    {
        return $this->classConstFetch;
    }
    public function getMethodName() : string
    {
        return $this->methodName;
    }
}

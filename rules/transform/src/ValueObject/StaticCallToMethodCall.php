<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

final class StaticCallToMethodCall
{
    /**
     * @var string
     */
    private $staticClass;

    /**
     * @var string
     */
    private $staticMethod;

    /**
     * @var string
     */
    private $classType;

    /**
     * @var string
     */
    private $methodName;

    public function __construct(string $staticClass, string $staticMethod, string $classType, string $methodName)
    {
        $this->staticClass = $staticClass;
        $this->staticMethod = $staticMethod;
        $this->classType = $classType;
        $this->methodName = $methodName;
    }

    public function getClassType(): string
    {
        return $this->classType;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function isStaticCallMatch(StaticCall $staticCall): bool
    {
        if (! $staticCall->class instanceof Name) {
            return false;
        }

        $staticCallClassName = $staticCall->class->toString();
        if ($staticCallClassName !== $this->staticClass) {
            return false;
        }

        if (! $staticCall->name instanceof Identifier) {
            return false;
        }

        // all methods
        if ($this->staticMethod === '*') {
            return true;
        }

        $staticCallMethodName = $staticCall->name->toString();
        return $staticCallMethodName === $this->staticMethod;
    }
}

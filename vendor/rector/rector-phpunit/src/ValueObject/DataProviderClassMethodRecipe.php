<?php

declare (strict_types=1);
namespace Rector\PHPUnit\ValueObject;

use PhpParser\Node\Arg;
final class DataProviderClassMethodRecipe
{
    /**
     * @var string
     */
    private $methodName;
    /**
     * @var \PhpParser\Node\Arg[]
     */
    private $args;
    /**
     * @param Arg[] $args
     */
    public function __construct(string $methodName, array $args)
    {
        $this->methodName = $methodName;
        $this->args = $args;
    }
    public function getMethodName() : string
    {
        return $this->methodName;
    }
    /**
     * @return Arg[]
     */
    public function getArgs() : array
    {
        return $this->args;
    }
}

<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPUnit\ValueObject;

use RectorPrefix20220606\PhpParser\Node\Arg;
final class DataProviderClassMethodRecipe
{
    /**
     * @readonly
     * @var string
     */
    private $methodName;
    /**
     * @var Arg[]
     * @readonly
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

<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\ValueObject;

use PhpParser\Node\Arg;
use PhpParser\Node\Stmt\ClassMethod;
final class AsTwigAttributeConversion
{
    /**
     * @readonly
     */
    private int $itemKey;
    /**
     * @readonly
     */
    private ClassMethod $classMethod;
    /**
     * @readonly
     */
    private Arg $nameArg;
    /**
     * @var Arg[]
     * @readonly
     */
    private array $optionArgs;
    /**
     * @param Arg[] $optionArgs
     */
    public function __construct(int $itemKey, ClassMethod $classMethod, Arg $nameArg, array $optionArgs)
    {
        $this->itemKey = $itemKey;
        $this->classMethod = $classMethod;
        $this->nameArg = $nameArg;
        $this->optionArgs = $optionArgs;
    }
    public function getItemKey(): int
    {
        return $this->itemKey;
    }
    public function getClassMethod(): ClassMethod
    {
        return $this->classMethod;
    }
    public function getNameArg(): Arg
    {
        return $this->nameArg;
    }
    /**
     * @return Arg[]
     */
    public function getOptionArgs(): array
    {
        return $this->optionArgs;
    }
}

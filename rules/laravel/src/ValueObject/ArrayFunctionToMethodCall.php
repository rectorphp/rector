<?php

declare(strict_types=1);

namespace Rector\Laravel\ValueObject;

final class ArrayFunctionToMethodCall
{
    /**
     * @var string
     */
    private $function;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $arrayMethod;

    /**
     * @var string
     */
    private $nonArrayMethod;

    public function __construct(
        string $function,
        string $class,
        string $property,
        string $arrayMethod,
        string $nonArrayMethod
    ) {
        $this->function = $function;
        $this->class = $class;
        $this->property = $property;
        $this->arrayMethod = $arrayMethod;
        $this->nonArrayMethod = $nonArrayMethod;
    }

    public function getFunction(): string
    {
        return $this->function;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getArrayMethod(): string
    {
        return $this->arrayMethod;
    }

    public function getNonArrayMethod(): string
    {
        return $this->nonArrayMethod;
    }
}

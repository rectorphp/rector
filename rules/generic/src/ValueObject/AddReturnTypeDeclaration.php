<?php

declare(strict_types=1);

namespace Rector\Generic\ValueObject;

final class AddReturnTypeDeclaration
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $returnType;

    public function __construct(string $class, string $method, string $returnType)
    {
        $this->class = $class;
        $this->method = $method;
        $this->returnType = $returnType;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getReturnType(): string
    {
        return $this->returnType;
    }
}

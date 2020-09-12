<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\ValueObject;

final class AddParamTypeDeclaration
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @var int
     */
    private $position;

    /**
     * @var string
     */
    private $typehint;

    public function __construct(string $className, string $methodName, int $position, string $typehint)
    {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->position = $position;
        $this->typehint = $typehint;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getTypehint(): string
    {
        return $this->typehint;
    }
}

<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\ValueObject;

use PHPStan\Type\Type;

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
     * @var Type
     */
    private $paramType;

    public function __construct(string $className, string $methodName, int $position, Type $paramType)
    {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->position = $position;
        $this->paramType = $paramType;
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

    public function getParamType(): Type
    {
        return $this->paramType;
    }
}

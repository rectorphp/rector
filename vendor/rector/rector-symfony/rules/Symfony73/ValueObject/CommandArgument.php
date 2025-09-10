<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\ValueObject;

use PhpParser\Node\Expr;
use PHPStan\Type\Type;
final class CommandArgument
{
    /**
     * @readonly
     */
    private string $nameValue;
    /**
     * @readonly
     */
    private Expr $name;
    /**
     * @readonly
     */
    private ?Expr $mode;
    /**
     * @readonly
     */
    private ?Expr $description;
    /**
     * @readonly
     */
    private ?Expr $default;
    /**
     * @readonly
     */
    private bool $isArray;
    /**
     * @readonly
     */
    private ?Type $defaultType;
    public function __construct(string $nameValue, Expr $name, ?Expr $mode, ?Expr $description, ?Expr $default, bool $isArray, ?Type $defaultType)
    {
        $this->nameValue = $nameValue;
        $this->name = $name;
        $this->mode = $mode;
        $this->description = $description;
        $this->default = $default;
        $this->isArray = $isArray;
        $this->defaultType = $defaultType;
    }
    public function getNameValue(): string
    {
        return $this->nameValue;
    }
    public function getName(): Expr
    {
        return $this->name;
    }
    public function getMode(): ?Expr
    {
        return $this->mode;
    }
    public function getDescription(): ?Expr
    {
        return $this->description;
    }
    public function getDefault(): ?Expr
    {
        return $this->default;
    }
    public function isArray(): bool
    {
        return $this->isArray;
    }
    public function getDefaultType(): ?Type
    {
        return $this->defaultType;
    }
}

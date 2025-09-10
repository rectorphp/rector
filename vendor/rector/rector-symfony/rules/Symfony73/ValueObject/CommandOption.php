<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\ValueObject;

use PhpParser\Node\Expr;
final class CommandOption
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
    private ?Expr $shortcut;
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
    public function __construct(string $nameValue, Expr $name, ?Expr $shortcut, ?Expr $mode, ?Expr $description, ?Expr $default)
    {
        $this->nameValue = $nameValue;
        $this->name = $name;
        $this->shortcut = $shortcut;
        $this->mode = $mode;
        $this->description = $description;
        $this->default = $default;
    }
    public function getName(): Expr
    {
        return $this->name;
    }
    public function getShortcut(): ?Expr
    {
        return $this->shortcut;
    }
    public function getMode(): ?Expr
    {
        return $this->mode;
    }
    public function getDescription(): ?Expr
    {
        return $this->description;
    }
    public function getNameValue(): string
    {
        return $this->nameValue;
    }
    public function getDefault(): ?Expr
    {
        return $this->default;
    }
}

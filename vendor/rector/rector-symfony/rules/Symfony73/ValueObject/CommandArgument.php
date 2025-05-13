<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\ValueObject;

use PhpParser\Node\Expr;
final class CommandArgument
{
    /**
     * @readonly
     */
    private Expr $name;
    /**
     * @readonly
     */
    private Expr $mode;
    /**
     * @readonly
     */
    private Expr $description;
    public function __construct(Expr $name, Expr $mode, Expr $description)
    {
        $this->name = $name;
        $this->mode = $mode;
        $this->description = $description;
    }
    public function getName() : Expr
    {
        return $this->name;
    }
    public function getMode() : Expr
    {
        return $this->mode;
    }
    public function getDescription() : Expr
    {
        return $this->description;
    }
}

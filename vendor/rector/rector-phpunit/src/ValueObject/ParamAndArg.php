<?php

declare (strict_types=1);
namespace Rector\PHPUnit\ValueObject;

use PhpParser\Node\Expr\Variable;
use PHPStan\Type\Type;
final class ParamAndArg
{
    /**
     * @var \PhpParser\Node\Expr\Variable
     */
    private $variable;
    /**
     * @var \PHPStan\Type\Type|null
     */
    private $type;
    public function __construct(\PhpParser\Node\Expr\Variable $variable, ?\PHPStan\Type\Type $type)
    {
        $this->variable = $variable;
        $this->type = $type;
    }
    public function getVariable() : \PhpParser\Node\Expr\Variable
    {
        return $this->variable;
    }
    public function getType() : ?\PHPStan\Type\Type
    {
        return $this->type;
    }
}

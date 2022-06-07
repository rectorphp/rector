<?php

declare (strict_types=1);
namespace Rector\PHPUnit\ValueObject;

use PhpParser\Node\Expr\Variable;
use PHPStan\Type\Type;
final class ParamAndArg
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\Variable
     */
    private $variable;
    /**
     * @readonly
     * @var \PHPStan\Type\Type|null
     */
    private $type;
    public function __construct(Variable $variable, ?Type $type)
    {
        $this->variable = $variable;
        $this->type = $type;
    }
    public function getVariable() : Variable
    {
        return $this->variable;
    }
    public function getType() : ?Type
    {
        return $this->type;
    }
}

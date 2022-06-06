<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPUnit\ValueObject;

use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PHPStan\Type\Type;
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

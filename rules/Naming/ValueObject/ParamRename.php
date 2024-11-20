<?php

declare (strict_types=1);
namespace Rector\Naming\ValueObject;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
final class ParamRename
{
    /**
     * @readonly
     */
    private string $currentName;
    /**
     * @readonly
     */
    private string $expectedName;
    /**
     * @readonly
     */
    private Variable $variable;
    /**
     * @readonly
     */
    private FunctionLike $functionLike;
    public function __construct(string $currentName, string $expectedName, Variable $variable, FunctionLike $functionLike)
    {
        $this->currentName = $currentName;
        $this->expectedName = $expectedName;
        $this->variable = $variable;
        $this->functionLike = $functionLike;
    }
    public function getCurrentName() : string
    {
        return $this->currentName;
    }
    public function getExpectedName() : string
    {
        return $this->expectedName;
    }
    public function getFunctionLike() : FunctionLike
    {
        return $this->functionLike;
    }
    public function getVariable() : Variable
    {
        return $this->variable;
    }
}

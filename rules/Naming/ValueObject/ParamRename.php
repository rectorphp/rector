<?php

declare (strict_types=1);
namespace Rector\Naming\ValueObject;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use Rector\Naming\Contract\RenameParamValueObjectInterface;
final class ParamRename implements RenameParamValueObjectInterface
{
    /**
     * @readonly
     * @var string
     */
    private $currentName;
    /**
     * @readonly
     * @var string
     */
    private $expectedName;
    /**
     * @readonly
     * @var \PhpParser\Node\Param
     */
    private $param;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\Variable
     */
    private $variable;
    /**
     * @readonly
     * @var \PhpParser\Node\FunctionLike
     */
    private $functionLike;
    public function __construct(string $currentName, string $expectedName, Param $param, Variable $variable, FunctionLike $functionLike)
    {
        $this->currentName = $currentName;
        $this->expectedName = $expectedName;
        $this->param = $param;
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
    public function getParam() : Param
    {
        return $this->param;
    }
    public function getVariable() : Variable
    {
        return $this->variable;
    }
}

<?php

declare (strict_types=1);
namespace Rector\Naming\ValueObject;

use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Naming\Contract\RenameParamValueObjectInterface;
final class ParamRename implements RenameParamValueObjectInterface
{
    /**
     * @var string
     */
    private $currentName;
    /**
     * @var string
     */
    private $expectedName;
    /**
     * @var \PhpParser\Node\Param
     */
    private $param;
    /**
     * @var \PhpParser\Node\Expr\Variable
     */
    private $variable;
    private $functionLike;
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    public function __construct(string $currentName, string $expectedName, Param $param, Variable $variable, $functionLike)
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
    /**
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction
     */
    public function getFunctionLike()
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

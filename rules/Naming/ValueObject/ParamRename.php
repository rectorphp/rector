<?php

declare (strict_types=1);
namespace Rector\Naming\ValueObject;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Naming\Contract\RenameParamValueObjectInterface;
final class ParamRename implements \Rector\Naming\Contract\RenameParamValueObjectInterface
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
     * @var \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_
     */
    private $functionLike;
    /**
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function __construct(string $currentName, string $expectedName, \PhpParser\Node\Param $param, \PhpParser\Node\Expr\Variable $variable, $functionLike)
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
     * @return \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_
     */
    public function getFunctionLike()
    {
        return $this->functionLike;
    }
    public function getParam() : \PhpParser\Node\Param
    {
        return $this->param;
    }
    public function getVariable() : \PhpParser\Node\Expr\Variable
    {
        return $this->variable;
    }
}

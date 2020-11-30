<?php

declare(strict_types=1);

namespace Rector\Naming\ValueObject;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Naming\Contract\RenameParamValueObjectInterface;

final class ParamRename implements RenameParamValueObjectInterface
{
    /**
     * @var string
     */
    private $expectedName;

    /**
     * @var string
     */
    private $currentName;

    /**
     * @var Param
     */
    private $param;

    /**
     * @var Variable
     */
    private $variable;

    /**
     * @var ClassMethod|Function_|Closure
     */
    private $functionLike;

    /**
     * @param ClassMethod|Function_|Closure $functionLike
     */
    public function __construct(
        string $currentName,
        string $expectedName,
        Param $param,
        Variable $variable,
        FunctionLike $functionLike
    ) {
        $this->param = $param;
        $this->variable = $variable;
        $this->expectedName = $expectedName;
        $this->currentName = $currentName;
        $this->functionLike = $functionLike;
    }

    public function getCurrentName(): string
    {
        return $this->currentName;
    }

    public function getExpectedName(): string
    {
        return $this->expectedName;
    }

    /**
     * @return ClassMethod|Function_|Closure
     */
    public function getFunctionLike(): FunctionLike
    {
        return $this->functionLike;
    }

    public function getParam(): Param
    {
        return $this->param;
    }

    public function getVariable(): Variable
    {
        return $this->variable;
    }
}

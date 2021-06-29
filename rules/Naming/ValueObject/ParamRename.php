<?php

declare(strict_types=1);

namespace Rector\Naming\ValueObject;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Naming\Contract\RenameParamValueObjectInterface;

final class ParamRename implements RenameParamValueObjectInterface
{
    public function __construct(
        private string $currentName,
        private string $expectedName,
        private Param $param,
        private Variable $variable,
        private ClassMethod | Function_ | Closure $functionLike
    ) {
    }

    public function getCurrentName(): string
    {
        return $this->currentName;
    }

    public function getExpectedName(): string
    {
        return $this->expectedName;
    }

    public function getFunctionLike(): ClassMethod | Function_ | Closure
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

<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Source\Nette\SomeControl;

interface SkipCallableAsConstructorControl
{
    /**
     * @param callable $callable
     */
    public function create($callable): SomeControl;
}

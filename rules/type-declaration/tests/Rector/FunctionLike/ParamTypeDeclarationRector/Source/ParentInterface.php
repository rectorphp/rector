<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Source;

interface ParentInterface
{
    /**
     * @param mixed $value
     */
    public function __construct($value);
}

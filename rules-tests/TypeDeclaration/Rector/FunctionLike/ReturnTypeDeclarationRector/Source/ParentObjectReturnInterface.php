<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source;

interface ParentObjectReturnInterface
{
    /**
     * @return object
     */
    public function hydrate(): object;
}

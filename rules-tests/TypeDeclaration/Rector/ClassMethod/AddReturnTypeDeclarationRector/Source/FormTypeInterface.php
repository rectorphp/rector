<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source;

interface FormTypeInterface
{
    /**
     * @return string|null
     */
    public function getParent();
}

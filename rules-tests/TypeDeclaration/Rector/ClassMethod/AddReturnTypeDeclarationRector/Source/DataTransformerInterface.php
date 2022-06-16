<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source;

interface DataTransformerInterface
{
    /**
     * @return mixed
     */
    public function transform();
}

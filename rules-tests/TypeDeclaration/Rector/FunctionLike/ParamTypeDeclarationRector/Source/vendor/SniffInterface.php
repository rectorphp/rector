<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Source\vendor;

interface SniffInterface
{
    /**
     * @param int $position
     */
    public function process(string $file, $position);
}

<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;

interface ReturnTypeInterface
{
    /**
     * @return String_|null
     */
    public function getNode(): ?Node;
}

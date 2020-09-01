<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;

final class SkipParentInterfaceReturnType implements ReturnTypeInterface
{
    public function getNode(): ?Node
    {
        $string = new String_('value');
        $string->value = 'hey';

        return $string;
    }
}

interface ReturnTypeInterface
{
    /**
     * @return String_|LNumber|null
     */
    public function getNode(): ?Node;
}

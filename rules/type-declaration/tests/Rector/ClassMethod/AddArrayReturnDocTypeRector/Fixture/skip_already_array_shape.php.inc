<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node\Scalar\String_;

final class SkipAlreadyArrayShape
{
    /**
     * @return array<string, \PhpParser\Node\Expr>
     */
    public function getFluentCalls(): array
    {
        $values = [];
        $values['name'] = new String_('name');

        return $values;
    }
}

<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String_;

final class SkipAlreadyArrayShapeImported
{
    /**
     * @return array<string, Expr>
     */
    public function getFluentCalls(): array
    {
        $values = [];
        $values['name'] = new String_('name');

        return $values;
    }
}

<?php

namespace Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\Fixture;

use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;

class SkipOverlyGeneric
{
    public function run()
    {
        $stringOrScalar = $this->resolveStringOrScalar();
    }

    private function resolveStringOrScalar(): Expr
    {
        if (mt_rand(0, 100)) {
            return new String_('hey');
        }

        return new LNumber(1000);
    }
}

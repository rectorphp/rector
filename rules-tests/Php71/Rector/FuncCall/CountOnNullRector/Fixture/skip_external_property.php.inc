<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

use PhpParser\Node\Expr\Isset_;

class SkipExternalProperty
{
    public function run()
    {
        $issetNode = new Isset_([]);

        if (! isset($issetNode->vars[0]) || count($issetNode->vars) > 1) {
        }
    }
}

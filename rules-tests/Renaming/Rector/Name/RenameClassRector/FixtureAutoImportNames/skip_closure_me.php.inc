<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use PhpParser\Node\Expr\Closure;

final class SkipClosureMe
{
    protected function pExpr_Closure(): Closure
    {
    }
}

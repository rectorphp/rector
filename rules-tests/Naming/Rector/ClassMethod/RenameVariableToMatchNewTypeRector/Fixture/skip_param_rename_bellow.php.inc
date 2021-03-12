<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use PhpParser\Node\Expr\Assign;

final class SkipParamRenameBellow
{
    public function run($assign)
    {
        $jsonDataAssign = new Assign(1, 2);
        $this->addNodeBeforeNode($jsonDataAssign, $assign);
    }
}

<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use PhpParser\Node\Expr\Assign;

final class SkipMethodCallRenameBellow
{
    public function run()
    {
        $assign = $this->getAssign();

        $jsonDataAssign = new Assign(1, 2);
        $this->addNodeBeforeNode($jsonDataAssign, $assign);
    }

    private function getAssign()
    {
        return new Assign();
    }
}

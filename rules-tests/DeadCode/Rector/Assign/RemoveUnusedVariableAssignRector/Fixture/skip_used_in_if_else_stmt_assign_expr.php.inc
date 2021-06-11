<?php
namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipUsedInIfElseStmtAssignExpr {
    public function bar(bool $flag): void {
        $text = 'a';
        if ($flag) {
            $text = $text . "b";
        }
        else {
            $text = $text . "c";
        }
    }
}

?>

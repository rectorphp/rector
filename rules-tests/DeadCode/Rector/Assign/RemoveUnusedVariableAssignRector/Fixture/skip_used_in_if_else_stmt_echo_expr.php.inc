<?php
namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipUsedInIfElseStmtEchoExpr {
    public function bar(bool $flag): void {
        $text = 'a';
        if ($flag) {
            echo $text;
            $text = 'a';
        }
        else {
            echo $text;
            $text = 'a';
        }
    }
}

?>

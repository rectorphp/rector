<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class SkipHasNextStmtAfterIf
{
    public function run()
    {
        $firstStatement  = isFirstStatementTrue();
        $secondStatement = isSecondStatementTrue();
        $foo             = bar();

        if ($firstStatement) {
            if ($secondStatement && (null !== $foo)) {
                // some logic w/o returns here
            }
        }

        $thirdStatement = isThirdStatementTrue();
        if ($thirdStatement) {
            // some logic here
        }
    }
}

?>

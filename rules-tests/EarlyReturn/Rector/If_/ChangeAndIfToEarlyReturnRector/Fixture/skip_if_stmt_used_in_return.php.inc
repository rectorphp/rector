<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class SkipIfStmtUsedInReturn
{
    public function run()
    {
        $content = '';

        if (1 === 1 && 2 === 2) {
            $content = execute($content);
        }

        return $content . 'execute';
    }
}

?>

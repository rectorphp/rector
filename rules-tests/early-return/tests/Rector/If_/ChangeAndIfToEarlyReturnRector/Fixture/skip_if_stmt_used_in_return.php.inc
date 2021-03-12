<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

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

<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipIfNop {
    public function run(array $params)
    {
        $toDate = null;
        if (isset($params['todate'])) {
            // nop
        } else {
            $toDate = new \DateTime();
            $toDate->setTimestamp(strtotime('now - 3 month'));
        }

        return $toDate;
    }
}

?>

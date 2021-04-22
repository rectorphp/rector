<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipElseNop {
    public function run(array $params)
    {
        $toDate = null;
        if (isset($params['todate'])) {
            $toDate = \DateTime::createFromFormat('d.m.Y', $params['todate']);
        } else {
            // nop
        }

        return $toDate;
    }
}

?>

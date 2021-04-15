<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class ShadowedLocalVariable {
    public function run(array $params)
    {
        $toDate = null;
        if (isset($params['todate'])) {
            $toDate = \DateTime::createFromFormat('d.m.Y', $params['todate']);
        } else {
            $toDate = new \DateTime();
            $toDate->setTimestamp(strtotime('now - 3 month'));
        }

        return $toDate;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class ShadowedLocalVariable {
    public function run(array $params)
    {
        if (isset($params['todate'])) {
            $toDate = \DateTime::createFromFormat('d.m.Y', $params['todate']);
        } else {
            $toDate = new \DateTime();
            $toDate->setTimestamp(strtotime('now - 3 month'));
        }

        return $toDate;
    }
}

?>

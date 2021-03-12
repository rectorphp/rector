<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class AlwaysReturn
{
    public function run(&$queries, $sql)
    {
        if (!empty($sql) && preg_match('@[^[:space:]]+@', $sql)) {
            $queries[] = ['query' => $sql, 'empty' => 'empty'];
        }

        return true;
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class AlwaysReturn
{
    public function run(&$queries, $sql)
    {
        if (empty($sql)) {
            return true;
        }
        if (!preg_match('@[^[:space:]]+@', $sql)) {
            return true;
        }
        $queries[] = ['query' => $sql, 'empty' => 'empty'];
        return true;
    }
}

?>

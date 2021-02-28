<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class util {
    public function splitSqlFile($queries, $sql)
    {
        if (!empty($sql) && true) {
            $queries[] = 1;
        }

        return true;
    }
}
?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

// what is expected code?
// should remain the same? delete part bellow ----- (included)

class util {
    public function splitSqlFile($queries, $sql)
    {
        if (empty($sql)) {
            return true;
        }
        if (! true) {
            return true;
        }
        $queries[] = 1;

        return true;
    }
}
?>

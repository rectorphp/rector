<?php

namespace Rector\CodeQualityStrict\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

final class VariableHasSpace
{
    public $mysqli;
    public $databaseName;

    public function run()
    {
        $this->mysqli->select_db($this->databaseName);
        if (! $this->mysqli->query('SHOW TABLES')) {
            throw new DatabaseException('SHOW TABLES FAILED.');
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQualityStrict\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

final class VariableHasSpace
{
    public $mysqli;
    public $databaseName;

    public function run()
    {
        $this->mysqli->select_db($this->databaseName);
        $mysqliQuery = $this->mysqli->query('SHOW TABLES');
        if (! $mysqliQuery) {
            throw new DatabaseException('SHOW TABLES FAILED.');
        }
    }
}

?>
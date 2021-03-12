<?php

namespace Rector\CodeQualityStrict\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

final class SkipParentAssign
{
    public $mysqli;
    public $databaseName;

    public function run()
    {
        $this->mysqli->select_db($this->databaseName);
        if (!$result = $this->mysqli->query('SHOW TABLES')) {
            throw new DatabaseException('SHOW TABLES FAILED.');
        }
    }
}
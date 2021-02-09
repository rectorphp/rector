<?php

namespace Rector\CodeQuality\Tests\Rector\Return_\SimplifyUselessVariableRector\Fixture;

final class DemoFile
{
    public function run(bool $argument)
    {
        $sql = 'begin';
        if ($argument) {
            $sql .= ' add something';
        }
        $sql .= 'end';

        return $sql;
    }
}
?>

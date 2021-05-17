<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector\Fixture;

class SkipKeysUsed
{
    public function run($packageName, $values)
    {
        $keys = array_keys($values);

        foreach($keys as $field) {
            if ($field === 'foo') {
                return false;
            }
        }

        return in_array($packageName, $keys, true);
    }
}

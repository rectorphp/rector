<?php

namespace Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

final class SkipVersionCompare
{
    /**
     * @param string|null $value
     * @return bool|int
     */
    public function run($value)
    {
        if ($value === null) {
            return version_compare('one', 'two');
        }

        return version_compare('one', 'two', $value);
    }
}

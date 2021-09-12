<?php

namespace Rector\Tests\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector\Fixture;

class SkipExistsDifferentValue
{
    private const REQUIRES = 'exists_different_value';

    public function run($key, $items)
    {
        if ($key === 'requires') {
            return $items['requires'];
        }

        return $items['requires'];
    }
}

?>

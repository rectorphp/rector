<?php

namespace Rector\CodingStyle\Tests\Rector\Variable\UnderscoreToPascalCaseVariableAndPropertyNameRector\Fixture;

final class SkipFirstUnderscore
{
    private $_value;

    public function run($_a)
    {
        $_some_value = 5;

        return $this->_value;
    }
}

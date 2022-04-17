<?php

namespace Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\Fixture;

final class SkipMultipleAssign
{
    private string $name;

    public function __construct(string $name, bool $flag = true)
    {
        $this->name = $name;

        if ($flag) {
            $this->name = 'change';
        }

        if ($flag) {
            $this->name = 'change again';
        }
    }
}

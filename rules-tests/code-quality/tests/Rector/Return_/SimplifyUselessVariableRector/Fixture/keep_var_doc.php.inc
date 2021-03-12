<?php

namespace Rector\CodeQuality\Tests\Rector\Return_\SimplifyUselessVariableRector\Fixture;

class KeepVarDoc
{
    public function run()
    {
        $name = $this->getValue();

        /** @var string $name */
        return $name;
    }

    private function getValue()
    {
        return 'name';
    }
}

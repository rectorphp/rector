<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfElseToTernaryRector\Fixture;

class KeepNestedTernary
{
    public function run()
    {
        if (is_string($type)) {
            $type = $this->typeAnalyzer->isPhpReservedType($type) ? $type : '\\' . $type;
        } else {
            $type = (string) $type;
        }

        if ($type ? true : false) {
            $type = 'Hou';
        } else {
            $type = (string) $type;
        }
    }
}

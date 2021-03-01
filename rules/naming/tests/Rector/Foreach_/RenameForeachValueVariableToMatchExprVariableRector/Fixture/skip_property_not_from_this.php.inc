<?php

namespace Rector\Naming\Tests\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class AClass
{
    public $vars = [];
}

class SkipPropertyNotFromThis
{
    public function run(AClass $aClass)
    {
        $array = [];
        foreach ($aClass->vars as $property) {
            $array[] = $property;
        }
    }
}

?>

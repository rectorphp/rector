<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SkipArrayAssign
{
    public function run()
    {
        # e.g. |trans https://symfony.com/doc/current/translation/templates.html#using-twig-filters
        $labelFilters = [];
        if ($label instanceof FuncCall && $this->isName($label, '__')) {
            $labelFilters[] = 'trans';
            $label = $label->args[0]->value;
        }
    }
}

<?php

namespace Rector\Tests\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipReferenceAssign
{
    public function run(array $nodes)
    {
        $collectionArray = 'nameOfProperty';

        $containerClass = 'blabla';
        $collection = &$this->$collectionArray;
    }
}

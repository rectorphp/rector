<?php

namespace Rector\Php\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipRefernceAssign
{
    public function run(array $nodes)
    {
        $collectionArray = 'nameOfProperty';

        $containerClass = 'blabla';
        $collection = &$this->$collectionArray;
    }
}

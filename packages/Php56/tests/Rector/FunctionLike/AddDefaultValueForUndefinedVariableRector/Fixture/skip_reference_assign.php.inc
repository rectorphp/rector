<?php

namespace Rector\Php56\Tests\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector\Fixture;

class SkipRefernceAssign
{
    public function run(array $nodes)
    {
        $collectionArray = 'nameOfProperty';

        $containerClass = 'blabla';
        $collection = &$this->$collectionArray;
    }
}

<?php

namespace Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Source\CollectionInterface;
use Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Source\CollectionImpl;

final class SkipInterfaceType
{
    /** @var CollectionInterface*/
    private $collection;

    public function __construct()
    {
        $this->collection = new CollectionImpl();
    }

    public function run()
    {
        $this->collection->getData('value');
    }
}

<?php

declare(strict_types=1);

namespace Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Source;

class CollectionImpl implements CollectionInterface
{
    public function getData(string $var = null)
    {
        echo $var ?? 'fallback';
    }
}

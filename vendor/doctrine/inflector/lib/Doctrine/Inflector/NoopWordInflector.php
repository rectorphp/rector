<?php

declare (strict_types=1);
namespace RectorPrefix20220601\Doctrine\Inflector;

class NoopWordInflector implements \RectorPrefix20220601\Doctrine\Inflector\WordInflector
{
    public function inflect(string $word) : string
    {
        return $word;
    }
}

<?php

declare (strict_types=1);
namespace RectorPrefix20220205\Doctrine\Inflector;

class NoopWordInflector implements \RectorPrefix20220205\Doctrine\Inflector\WordInflector
{
    public function inflect(string $word) : string
    {
        return $word;
    }
}

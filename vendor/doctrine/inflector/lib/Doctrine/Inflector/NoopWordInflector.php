<?php

declare (strict_types=1);
namespace RectorPrefix20211215\Doctrine\Inflector;

class NoopWordInflector implements \RectorPrefix20211215\Doctrine\Inflector\WordInflector
{
    public function inflect(string $word) : string
    {
        return $word;
    }
}
